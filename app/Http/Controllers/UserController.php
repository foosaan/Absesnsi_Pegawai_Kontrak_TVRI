<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\BusinessTrip;
use App\Models\Salary;
use App\Models\Leave;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class UserController extends Controller
{
    /**
     * Home page dengan pengumuman
     */
    public function home()
    {
        $user = auth()->user();
        
        // Pengumuman aktif
        $announcements = Announcement::active()
            ->latest()
            ->take(5)
            ->get();
        
        // Status absensi hari ini
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', Carbon::today())
            ->first();
        
        // Riwayat absensi terakhir (10 data)
        $recentAttendances = Attendance::where('user_id', $user->id)
            ->orderBy('check_in_time', 'desc')
            ->take(10)
            ->get();
        
        // Statistik bulan ini
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $monthlyAttendances = Attendance::where('user_id', $user->id)
            ->whereBetween('check_in_time', [$startOfMonth, $endOfMonth])
            ->get();
        
        $stats = [
            'total_hadir' => $monthlyAttendances->count(),
            'total_terlambat' => $monthlyAttendances->where('status', 'late')->count(),
            'total_tepat_waktu' => $monthlyAttendances->where('status', '!=', 'late')->count(),
        ];
        
        // Shift info
        $currentShift = null;
        $allShifts = null;
        
        if ($user->isShiftAttendance()) {
            $allShifts = \App\Models\Shift::getShiftSchedules();
            $currentShift = \App\Models\Shift::getCurrentShiftForTime(Carbon::now());
        } elseif ($user->isNormalAttendance()) {
            $currentShift = \App\Models\Shift::getNormalShift();
        }
        
        return view('dashboard', compact('announcements', 'todayAttendance', 'stats', 'recentAttendances', 'currentShift', 'allShifts'));
    }

    /**
     * Rekap absensi user
     */
    public function rekap(Request $request)
    {
        $user = auth()->user();
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $attendances = Attendance::with('shift')
            ->where('user_id', $user->id)
            ->whereBetween('check_in_time', [$startDate, $endDate])
            ->orderBy('check_in_time', 'desc')
            ->get();
        
        // Statistik
        $stats = [
            'total_hadir' => $attendances->count(),
            'total_terlambat' => $attendances->where('status', 'late')->count(),
            'total_tepat_waktu' => $attendances->where('status', '!=', 'late')->count(),
        ];
        
        return view('user.rekap', compact('attendances', 'stats', 'month', 'year'));
    }

    /**
     * Export rekap absensi pribadi ke Excel
     */
    public function exportRekap(Request $request)
    {
        $user = auth()->user();
        $filterType = $request->get('filter_type', 'month');
        $userName = str_replace(' ', '_', $user->name);
        $params = ['user_id' => $user->id];

        switch ($filterType) {
            case 'day':
                $date = $request->get('date', Carbon::today()->toDateString());
                $params['date'] = $date;
                $filename = 'rekap_absensi_' . $userName . '_' . $date . '.xlsx';
                break;
            case 'all':
                $filename = 'rekap_absensi_' . $userName . '_semua_data.xlsx';
                break;
            default: // month
                $filterType = 'month';
                $month = $request->get('month', Carbon::now()->month);
                $year = $request->get('year', Carbon::now()->year);
                $params['month'] = $month;
                $params['year'] = $year;
                $monthName = Carbon::create($year, $month, 1)->translatedFormat('F');
                $filename = 'rekap_absensi_' . $userName . '_' . $monthName . '_' . $year . '.xlsx';
                break;
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AttendanceExport($filterType, $params),
            $filename
        );
    }

    /**
     * Halaman gaji user
     */
    public function salary(Request $request)
    {
        $user = auth()->user();
        
        $salaries = Salary::where('user_id', $user->id)
            ->with('salaryDeductions.type')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(12);
        
        $deductionTypes = \App\Models\DeductionType::where('is_active', true)->orderBy('name')->get();
        
        return view('user.salary', compact('salaries', 'deductionTypes'));
    }

    /**
     * List cuti user
     */
    public function leaves()
    {
        $user = auth()->user();
        
        $leaves = Leave::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('user.leaves.index', compact('leaves'));
    }

    /**
     * Form ajukan cuti
     */
    public function createLeave()
    {
        return view('user.leaves.create');
    }

    /**
     * Simpan cuti
     */
    public function storeLeave(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'type' => 'required|in:cuti_tahunan,sakit,alasan_penting,lainnya',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        $userId = auth()->id();

        // Bug #8 fix: Check for overlapping leaves (pending or approved)
        $overlapping = Leave::where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })
            ->exists();

        if ($overlapping) {
            return back()->withInput()
                ->with('error', 'Sudah ada pengajuan cuti di tanggal tersebut (pending atau disetujui).');
        }

        // Bug #3 fix: Check leave balance for cuti_tahunan
        if ($request->type === 'cuti_tahunan') {
            $year = \Carbon\Carbon::parse($request->start_date)->year;
            $balance = \App\Models\LeaveBalance::getOrCreate($userId, $year);
            
            // Count working days requested
            $workingDays = 0;
            $date = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            while ($date->lte($endDate)) {
                if ($date->isWeekday()) {
                    $workingDays++;
                }
                $date->addDay();
            }
            
            if ($workingDays > $balance->remaining) {
                return back()->withInput()
                    ->with('error', "Saldo cuti tahunan tidak cukup. Sisa: {$balance->remaining} hari, dibutuhkan: {$workingDays} hari kerja.");
            }
        }

        // Handle file upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
        }

        $leave = Leave::create([
            'user_id' => $userId,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'type' => $request->type,
            'status' => 'pending',
        ]);

        \App\Services\NotificationService::newLeaveRequest($leave);

        return redirect()->route('user.leaves')
            ->with('success', 'Pengajuan cuti berhasil dikirim!');
    }

    /**
     * Batalkan cuti (jika masih pending)
     */
    public function cancelLeave(Leave $leave)
    {
        if ($leave->user_id !== auth()->id()) {
            abort(403);
        }
        
        if ($leave->status !== 'pending') {
            return back()->with('error', 'Cuti tidak bisa dibatalkan karena sudah diproses.');
        }
        
        $leave->delete();
        
        return back()->with('success', 'Pengajuan cuti berhasil dibatalkan.');
    }

    /**
     * Export slip gaji ke PDF
     */
    public function salaryPdf(Salary $salary)
    {
        // Pastikan salary milik user yang login
        if ($salary->user_id !== auth()->id()) {
            abort(403);
        }

        // Hanya slip yang sudah ditandatangani yang bisa didownload
        if (!$salary->isSigned()) {
            return back()->with('error', 'Slip gaji belum ditandatangani oleh Staff Keuangan.');
        }

        $salary->load('signer');
        $deductionTypes = \App\Models\DeductionType::where('is_active', true)->orderBy('name')->get();
    
        $pdf = Pdf::loadView('user.salary-pdf', compact('salary', 'deductionTypes'));
        
        $filename = 'slip_gaji_' . $salary->user->name . '_' . $salary->period . '.pdf';
        $filename = str_replace(' ', '_', $filename);
        
        return $pdf->download($filename);
    }

    /**
     * List dinas luar user
     */
    public function businessTrips()
    {
        $user = auth()->user();
        
        $trips = BusinessTrip::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('user.business-trips.index', compact('trips'));
    }

    /**
     * Form ajukan dinas luar
     */
    public function createBusinessTrip()
    {
        return view('user.business-trips.create');
    }

    /**
     * Simpan dinas luar
     */
    public function storeBusinessTrip(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'destination' => 'required|string|max:255',
            'purpose' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        $userId = auth()->id();

        // Check overlap with existing business trips
        $overlapping = BusinessTrip::where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })
            ->exists();

        if ($overlapping) {
            return back()->withInput()
                ->with('error', 'Sudah ada pengajuan dinas luar di tanggal tersebut.');
        }

        // Check overlap with leaves
        $leaveOverlap = Leave::where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })
            ->exists();

        if ($leaveOverlap) {
            return back()->withInput()
                ->with('error', 'Sudah ada pengajuan cuti di tanggal tersebut.');
        }

        $data = [
            'user_id' => $userId,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'destination' => $request->destination,
            'purpose' => $request->purpose,
            'status' => 'pending',
        ];

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('business-trip-attachments', 'public');
        }

        $trip = BusinessTrip::create($data);

        \App\Services\NotificationService::newBusinessTripRequest($trip);

        return redirect()->route('user.business-trips')
            ->with('success', 'Pengajuan dinas luar berhasil dikirim!');
    }

    /**
     * Batalkan dinas luar (jika masih pending)
     */
    public function cancelBusinessTrip(BusinessTrip $businessTrip)
    {
        if ($businessTrip->user_id !== auth()->id()) {
            abort(403);
        }
        
        if ($businessTrip->status !== 'pending') {
            return back()->with('error', 'Dinas luar tidak bisa dibatalkan karena sudah diproses.');
        }
        
        $businessTrip->delete();
        
        return back()->with('success', 'Pengajuan dinas luar berhasil dibatalkan.');
    }
}
