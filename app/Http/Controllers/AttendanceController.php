<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\Shift;
use App\Services\AttendanceService;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        // Get today's attendance record
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $today)
            ->first();
        
        // Get settings for location validation
        $settings = DB::table('settings')->pluck('value', 'key');
        
        // Get applicable shift for user
        $currentShift = $this->attendanceService->getApplicableShift($user);
        
        // Check if user is on leave today
        $isOnLeave = $todayAttendance && $todayAttendance->status === 'cuti';
        
        if ($isOnLeave) {
            // User is on leave - disable check-in/out
            $canCheckIn = false;
            $canCheckOut = false;
            $statusMessage = 'Anda sedang cuti hari ini. Selamat beristirahat!';
        } else {
            // Determine current action state using service
            $checkInStatus = $this->attendanceService->canCheckIn($user);
            $checkOutStatus = $this->attendanceService->canCheckOut($user);
            
            $canCheckIn = $checkInStatus['can'];
            $canCheckOut = $checkOutStatus['can'];
            
            // Determine status message
            if (!$todayAttendance) {
                $statusMessage = $checkInStatus['message'];
            } elseif (!$todayAttendance->check_out_time) {
                $statusMessage = $checkOutStatus['message'];
            } else {
                $statusMessage = 'Anda sudah menyelesaikan absensi hari ini. Sampai jumpa besok!';
            }
        }
        
        // Get all shifts for display (for shift-based users)
        $allShifts = $user->isShiftAttendance() ? Shift::getShiftSchedules() : null;
        
        return view('attendance.index', compact(
            'todayAttendance', 
            'canCheckIn', 
            'canCheckOut', 
            'statusMessage',
            'isOnLeave',
            'settings',
            'currentShift',
            'allShifts'
        ));
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'required|numeric',
            'is_mock_location' => 'nullable|boolean',
            'photo' => 'required|image|max:5120',
        ]);

        $user = auth()->user();
        
        // Fake GPS Detection (Server-Side)
        $accuracy = (float) $request->accuracy;
        $isMock = (bool) $request->is_mock_location;
        
        // Reject if client detected mock location
        if ($isMock) {
            return back()->with('error', 'Terdeteksi penggunaan lokasi palsu (fake GPS). Absensi ditolak!');
        }
        
        // Reject if accuracy is exactly 0 (strong indicator of mock)
        if ($accuracy == 0) {
            return back()->with('error', 'Akurasi GPS tidak valid (0 meter). Pastikan GPS asli aktif.');
        }
        
        // Reject if accuracy is too poor (> 200 meters)
        if ($accuracy > 200) {
            return back()->with('error', 'Akurasi GPS terlalu rendah (' . round($accuracy) . 'm). Pastikan GPS aktif dan tunggu sinyal stabil.');
        }
        
        // Check if can check-in using service
        $checkInStatus = $this->attendanceService->canCheckIn($user);
        
        if (!$checkInStatus['can']) {
            return back()->with('error', $checkInStatus['message']);
        }
        
        // Get settings for location validation
        $settings = DB::table('settings')->pluck('value', 'key');
        
        // Validate location
        $userLat = $request->latitude;
        $userLon = $request->longitude;
        $officeLat = $settings['office_latitude'];
        $officeLon = $settings['office_longitude'];
        $allowedRadius = $settings['allowed_radius_meters'];

        $distance = $this->calculateDistance($userLat, $userLon, $officeLat, $officeLon);

        if ($distance > $allowedRadius) {
            return back()->with('error', 'Anda berada diluar jangkauan kantor! Jarak: ' . round($distance) . ' meter.');
        }

        // Handle File Upload
        $path = $request->file('photo')->store('attendance_photos', 'public');

        // Flag suspicious accuracy (very low like 1-2m can also be mock)
        $suspicious = $accuracy <= 2;

        // Process check-in using service
        $attendance = $this->attendanceService->processCheckIn($user, [
            'photo_path' => $path,
            'latitude' => $userLat,
            'longitude' => $userLon,
            'location_accuracy' => $accuracy,
            'is_mock_location' => $suspicious,
        ]);

        $message = 'Absen Masuk Berhasil!';
        if ($attendance->status === 'late') {
            $message .= ' (Terlambat)';
        }
        
        // Add info about minimum checkout time
        $minCheckOut = $attendance->min_check_out_time->format('H:i');
        $message .= " Anda bisa absen pulang mulai jam {$minCheckOut}.";

        return back()->with('success', $message);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'required|numeric',
            'is_mock_location' => 'nullable|boolean',
            'photo' => 'required|image|max:5120',
        ]);

        $user = auth()->user();
        
        // Fake GPS Detection (Server-Side)
        $accuracy = (float) $request->accuracy;
        $isMock = (bool) $request->is_mock_location;
        
        if ($isMock) {
            return back()->with('error', 'Terdeteksi penggunaan lokasi palsu (fake GPS). Absensi ditolak!');
        }
        
        if ($accuracy == 0) {
            return back()->with('error', 'Akurasi GPS tidak valid (0 meter). Pastikan GPS asli aktif.');
        }
        
        if ($accuracy > 200) {
            return back()->with('error', 'Akurasi GPS terlalu rendah (' . round($accuracy) . 'm). Pastikan GPS aktif dan tunggu sinyal stabil.');
        }
        
        // Check if can check-out using service
        $checkOutStatus = $this->attendanceService->canCheckOut($user);
        
        if (!$checkOutStatus['can']) {
            return back()->with('error', $checkOutStatus['message']);
        }
        
        $attendance = $checkOutStatus['attendance'];
        
        // Get settings for location validation
        $settings = DB::table('settings')->pluck('value', 'key');
        
        // Validate location
        $userLat = $request->latitude;
        $userLon = $request->longitude;
        $officeLat = $settings['office_latitude'];
        $officeLon = $settings['office_longitude'];
        $allowedRadius = $settings['allowed_radius_meters'];

        $distance = $this->calculateDistance($userLat, $userLon, $officeLat, $officeLon);

        if ($distance > $allowedRadius) {
            return back()->with('error', 'Anda berada diluar jangkauan kantor! Jarak: ' . round($distance) . ' meter.');
        }

        // Handle File Upload
        $path = $request->file('photo')->store('attendance_photos', 'public');

        // Flag suspicious accuracy
        $suspicious = $accuracy <= 2 || $attendance->is_mock_location;

        // Process check-out using service
        $this->attendanceService->processCheckOut($attendance, [
            'photo_path' => $path,
            'latitude' => $userLat,
            'longitude' => $userLon,
            'check_out_location_accuracy' => $accuracy,
            'is_mock_location' => $suspicious,
        ]);

        return back()->with('success', 'Absen Pulang Berhasil!');
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
