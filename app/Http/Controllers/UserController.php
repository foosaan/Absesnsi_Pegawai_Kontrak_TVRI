<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Attendance;
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
        
        return view('user.home', compact('announcements', 'todayAttendance', 'stats'));
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
     * Halaman gaji user
     */
    public function salary(Request $request)
    {
        $user = auth()->user();
        
        $salaries = Salary::where('user_id', $user->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(12);
        
        return view('user.salary', compact('salaries'));
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
            'type' => 'required|in:cuti_tahunan,sakit,izin,lainnya',
        ]);

        Leave::create([
            'user_id' => auth()->id(),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'type' => $request->type,
            'status' => 'pending',
        ]);

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

        $pdf = Pdf::loadView('user.salary-pdf', compact('salary'));
        
        $filename = 'slip_gaji_' . $salary->user->name . '_' . $salary->period . '.pdf';
        $filename = str_replace(' ', '_', $filename);
        
        return $pdf->download($filename);
    }
}
