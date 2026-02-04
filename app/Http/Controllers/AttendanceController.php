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
        
        // Get all shifts for display (for Satpam)
        $allShifts = $user->isSatpam() ? Shift::getSatpamShifts() : null;
        
        return view('attendance.index', compact(
            'todayAttendance', 
            'canCheckIn', 
            'canCheckOut', 
            'statusMessage',
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
            'photo' => 'required|image|max:5120',
        ]);

        $user = auth()->user();
        
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

        // Process check-in using service
        $attendance = $this->attendanceService->processCheckIn($user, [
            'photo_path' => $path,
            'latitude' => $userLat,
            'longitude' => $userLon,
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
            'photo' => 'required|image|max:5120',
        ]);

        $user = auth()->user();
        
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

        // Process check-out using service
        $this->attendanceService->processCheckOut($attendance, [
            'photo_path' => $path,
            'latitude' => $userLat,
            'longitude' => $userLon,
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
