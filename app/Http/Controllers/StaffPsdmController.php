<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Announcement;
use App\Models\MasterDataType;
use Carbon\Carbon;

class StaffPsdmController extends Controller
{
    /**
     * Helper: Ambil semua Master Data PSDM untuk form
     */
    private function getMasterDataPsdm()
    {
        $masterDataTypes = MasterDataType::where('scope', 'psdm')
            ->with(['values' => function($q) {
                $q->where('is_active', true)->orderBy('value');
            }])
            ->get()
            ->keyBy('slug');
        
        return ['masterDataTypes' => $masterDataTypes];
    }

    /**
     * Dashboard Staff PSDM - Statistik absensi
     */
    public function index()
    {
        $today = Carbon::today();
        
        // Statistik hari ini
        $totalUsers = User::where('role', 'user')->count();
        $todayAttendances = Attendance::whereDate('check_in_time', $today)->where('status', '!=', 'cuti')->count();
        $todayLate = Attendance::whereDate('check_in_time', $today)->where('status', 'late')->count();
        $todayOnTime = $todayAttendances - $todayLate;
        
        // Absensi terbaru hari ini
        $recentAttendances = Attendance::with(['user', 'shift'])
            ->whereDate('check_in_time', $today)
            ->latest()
            ->take(10)
            ->get();
        
        // Pengumuman aktif
        $announcements = Announcement::active()->latest()->take(5)->get();
        
        return view('staff.psdm.dashboard', compact(
            'totalUsers',
            'todayAttendances',
            'todayLate',
            'todayOnTime',
            'recentAttendances',
            'announcements'
        ));
    }

    /**
     * List semua user
     */
    public function users(Request $request)
    {
        $query = User::where('role', 'user');
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        $users = $query->orderBy('name')->paginate(15);
        
        return view('staff.psdm.users.index', compact('users'));
    }

    /**
 * Form create user
 */
public function createUser()
{
    // Ambil data dari Master Data PSDM
    $masterData = $this->getMasterDataPsdm();
    
    return view('staff.psdm.users.create', $masterData);
}

    /**
 * Store new user
 */
public function storeUser(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'nip' => 'required|string|max:50|unique:users,nip',
        'nik' => 'required|string|max:20',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'attendance_type' => 'required|in:normal,shift',
        'jenis_kelamin' => 'nullable|in:L,P',
    ]);

    // Map master_data fields ke kolom user
    $masterData = $request->input('master_data', []);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'role' => 'user',
        'attendance_type' => $request->attendance_type ?? 'normal',
        // Biodata fields
        'nip' => $request->nip,
        'nik' => $request->nik,
        'alamat' => $request->alamat,
        'jenis_kelamin' => $request->jenis_kelamin,
        'jabatan' => $masterData['jabatan'] ?? null,
        'bagian' => $masterData['bagian'] ?? null,
        'status_pegawai' => $masterData['status-pegawai'] ?? null,
    ]);

    return redirect()->route('staff.psdm.users')
        ->with('success', 'User berhasil ditambahkan!');
}

    /**
 * Form edit user
 */
public function editUser(User $user)
{
    // Ambil data dari Master Data PSDM
    $masterData = $this->getMasterDataPsdm();
    $masterData['user'] = $user;
    
    return view('staff.psdm.users.edit', $masterData);
}

    /**
 * Update user (data dasar saja - data keuangan dikelola Staff Keuangan)
 */
public function updateUser(Request $request, User $user)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'nip' => 'required|string|max:50|unique:users,nip,' . $user->id,
        'nik' => 'required|string|max:20',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'attendance_type' => 'required|in:normal,shift',
        'jenis_kelamin' => 'nullable|in:L,P',
    ]);

    // Map master_data fields ke kolom user
    $masterData = $request->input('master_data', []);

    $data = [
        'name' => $request->name,
        'email' => $request->email,
        'attendance_type' => $request->attendance_type,
        // Biodata fields
        'nip' => $request->nip,
        'nik' => $request->nik,
        'alamat' => $request->alamat,
        'jenis_kelamin' => $request->jenis_kelamin,
        'jabatan' => $masterData['jabatan'] ?? $user->jabatan,
        'bagian' => $masterData['bagian'] ?? $user->bagian,
        'status_pegawai' => $masterData['status-pegawai'] ?? $user->status_pegawai,
        'status_operasional' => $masterData['status_oprasional'] ?? $user->status_operasional,
    ];

    if ($request->filled('password')) {
        $request->validate(['password' => 'min:6']);
        $data['password'] = bcrypt($request->password);
    }

    $user->update($data);

    return redirect()->route('staff.psdm.users')
        ->with('success', 'User berhasil diupdate!');
}

    /**
     * Delete user (with cascade cleanup)
     */
    public function deleteUser(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        // Clean up related records to prevent orphan data
        $user->attendances()->delete();
        $user->salaries()->delete();
        \App\Models\Leave::where('user_id', $user->id)->delete();
        \App\Models\LeaveBalance::where('user_id', $user->id)->delete();
        \App\Models\BusinessTrip::where('user_id', $user->id)->delete();
        \App\Models\Notification::where('user_id', $user->id)->delete();
        
        $user->delete();

        return redirect()->route('staff.psdm.users')
            ->with('success', 'User dan semua data terkait berhasil dihapus!');
    }

    /**
     * Bulk delete users
     */
    public function bulkDeleteUsers(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $ids = collect($request->user_ids)->reject(fn($id) => (int) $id === auth()->id());

        if ($ids->isEmpty()) {
            return back()->with('error', 'Tidak ada user yang bisa dihapus.');
        }

        // Clean up related records
        \App\Models\Attendance::whereIn('user_id', $ids)->delete();
        \App\Models\Salary::whereIn('user_id', $ids)->delete();
        \App\Models\Leave::whereIn('user_id', $ids)->delete();
        \App\Models\LeaveBalance::whereIn('user_id', $ids)->delete();
        \App\Models\BusinessTrip::whereIn('user_id', $ids)->delete();
        \App\Models\Notification::whereIn('user_id', $ids)->delete();

        $count = User::whereIn('id', $ids)->delete();

        return redirect()->route('staff.psdm.users')
            ->with('success', "{$count} pegawai dan semua data terkait berhasil dihapus!");
    }

    /**
     * Form import users dari Excel
     */
    public function importUsersForm()
    {
        return view('staff.psdm.users.import');
    }

    /**
     * Proses import users dari Excel
     */
    public function importUsers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new \App\Imports\UserImport();
        \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

        $results = $import->getResults();
        $errors = $import->getErrors();
        $skipped = $import->getSkipped();

        $message = count($results) . ' pegawai berhasil diimport.';
        if (count($skipped) > 0) {
            $message .= ' ' . count($skipped) . ' dilewati (NIP sudah terdaftar).';
        }
        if (count($errors) > 0) {
            $message .= ' ' . count($errors) . ' error.';
        }

        return redirect()->route('staff.psdm.users')
            ->with('success', $message)
            ->with('import_skipped', $skipped)
            ->with('import_errors', $errors);
    }

    /**
     * List pengumuman
     */
    public function announcements()
    {
        $announcements = Announcement::with('creator')->latest()->paginate(10);
        
        return view('staff.psdm.announcements.index', compact('announcements'));
    }

    /**
     * Form create pengumuman
     */
    public function createAnnouncement()
    {
        return view('staff.psdm.announcements.create');
    }

    /**
     * Store pengumuman
     */
    public function storeAnnouncement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'created_by' => auth()->id(),
            'is_active' => $request->has('is_active'),
        ]);

        if ($announcement->is_active) {
            \App\Services\NotificationService::announcementCreated($announcement);
        }

        return redirect()->route('staff.psdm.announcements')
            ->with('success', 'Pengumuman berhasil ditambahkan!');
    }

    /**
     * Form edit pengumuman
     */
    public function editAnnouncement(Announcement $announcement)
    {
        return view('staff.psdm.announcements.edit', compact('announcement'));
    }

    /**
     * Update pengumuman
     */
    public function updateAnnouncement(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $announcement->update([
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('staff.psdm.announcements')
            ->with('success', 'Pengumuman berhasil diupdate!');
    }

    /**
     * Toggle pengumuman active status
     */
    public function toggleAnnouncement(Announcement $announcement)
    {
        $announcement->update(['is_active' => !$announcement->is_active]);

        return back()->with('success', 'Status pengumuman berhasil diubah!');
    }

    /**
     * Delete pengumuman
     */
    public function deleteAnnouncement(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('staff.psdm.announcements')
            ->with('success', 'Pengumuman berhasil dihapus!');
    }

    /**
     * Monitor absensi semua user
     */
    public function monitor(Request $request)
    {
        $query = Attendance::with(['user', 'shift'])->latest('check_in_time');
        
        // Date filtering - support: specific date, month only, or all
        if ($request->filled('date')) {
            $query->whereDate('check_in_time', $request->date);
        } elseif ($request->filled('month') || $request->filled('year')) {
            $month = $request->input('month', now()->month);
            $year = $request->input('year', now()->year);
            $query->whereMonth('check_in_time', $month)
                  ->whereYear('check_in_time', $year);
        }
        // If no date/month filter, show all records
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Search by name or NIP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }
        
        $attendances = $query->paginate(20)->withQueryString();
        $users = User::where('role', 'user')->orderBy('name')->get();
        
        return view('staff.psdm.monitor', compact('attendances', 'users'));
    }

    /**
     * Export rekap absensi ke Excel
     */
    public function exportAttendance(Request $request)
    {
        $filterType = $request->get('filter_type', 'month');
        $params = [];

        switch ($filterType) {
            case 'day':
                $date = $request->get('date', Carbon::today()->toDateString());
                $params['date'] = $date;
                $filename = 'rekap_absensi_' . $date . '.xlsx';
                break;
            case 'all':
                $filename = 'rekap_absensi_semua_data.xlsx';
                break;
            default: // month
                $filterType = 'month';
                $month = $request->get('month', Carbon::now()->month);
                $year = $request->get('year', Carbon::now()->year);
                $params['month'] = $month;
                $params['year'] = $year;
                $monthName = Carbon::create($year, $month, 1)->translatedFormat('F');
                $filename = 'rekap_absensi_' . $monthName . '_' . $year . '.xlsx';
                break;
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AttendanceExport($filterType, $params),
            $filename
        );
    }

    /**
     * List saldo cuti karyawan
     */
    public function leaveBalances(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        
        $users = User::where('role', 'user')
            ->orderBy('name')
            ->get()
            ->map(function ($user) use ($year) {
                $user->leave_balance = \App\Models\LeaveBalance::getOrCreate($user->id, $year);
                return $user;
            });
        
        return view('staff.psdm.leave-balances.index', compact('users', 'year'));
    }

    /**
     * Update saldo cuti
     */
    public function updateLeaveBalance(Request $request, User $user)
    {
        $request->validate([
            'year' => 'required|integer',
            'initial_balance' => 'required|integer|min:0',
            'used' => 'required|integer|min:0',
        ]);

        $balance = \App\Models\LeaveBalance::getOrCreate($user->id, $request->year);
        $balance->update([
            'initial_balance' => $request->initial_balance,
            'used' => $request->used,
            'remaining' => $request->initial_balance - $request->used,
            'notes' => $request->notes,
        ]);

        return back()->with('success', "Saldo cuti {$user->name} berhasil diupdate!");
    }

    /**
     * Initialize leave balances for all users
     */
    public function initializeLeaveBalances(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $initialBalance = $request->get('initial_balance', 12);
        
        $users = User::where('role', 'user')->get();
        $count = 0;
        
        foreach ($users as $user) {
            $exists = \App\Models\LeaveBalance::where('user_id', $user->id)
                ->where('year', $year)
                ->exists();
            
            if (!$exists) {
                \App\Models\LeaveBalance::create([
                    'user_id' => $user->id,
                    'year' => $year,
                    'initial_balance' => $initialBalance,
                    'used' => 0,
                    'remaining' => $initialBalance,
                ]);
                $count++;
            }
        }
        
        return back()->with('success', "{$count} saldo cuti berhasil diinisialisasi untuk tahun {$year}!");
    }

    /**
     * Master Data PSDM - List
     */
    public function masterData()
    {
        $types = \App\Models\MasterDataType::where('scope', 'psdm')->withCount('values')->get();
        return view('staff.psdm.master-data.index', compact('types'));
    }

    public function storeMasterDataType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        \App\Models\MasterDataType::create([
            'name' => $request->name,
            'scope' => 'psdm',
            'description' => $request->description,
        ]);

        return back()->with('success', 'Kategori master data berhasil ditambahkan!');
    }

    public function destroyMasterDataType(\App\Models\MasterDataType $type)
    {
        $type->delete();
        return back()->with('success', 'Kategori master data berhasil dihapus!');
    }

    public function updateMasterDataType(Request $request, \App\Models\MasterDataType $type)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $type->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Kategori "' . $type->name . '" berhasil diupdate!');
    }

    public function showMasterDataType(\App\Models\MasterDataType $type)
    {
        $type->load('values');
        return view('staff.psdm.master-data.show', compact('type'));
    }

    public function storeMasterDataValue(Request $request, \App\Models\MasterDataType $type)
    {
        $request->validate([
            'value' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $type->values()->create([
            'value' => $request->value,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Nilai berhasil ditambahkan!');
    }

    public function destroyMasterDataValue(\App\Models\MasterDataValue $value)
    {
        $value->delete();
        return back()->with('success', 'Nilai berhasil dihapus!');
    }

    /**
     * List semua pengajuan cuti
     */
    public function leaves(Request $request)
    {
        $query = \App\Models\Leave::with('user', 'approver')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves = $query->paginate(15);
        $pendingCount = \App\Models\Leave::where('status', 'pending')->count();

        return view('staff.psdm.leaves.index', compact('leaves', 'pendingCount'));
    }

    /**
     * Approve pengajuan cuti + auto-create attendance records + update saldo
     */
    public function approveLeave(\App\Models\Leave $leave)
    {
        if ($leave->status !== 'pending') {
            return back()->with('error', 'Cuti ini sudah diproses sebelumnya.');
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        // Auto-create attendance records for leave period
        $service = new \App\Services\AttendanceService();
        $count = $service->createLeaveAttendances($leave);

        // Decrement leave balance (only for cuti_tahunan)
        if ($leave->type === 'cuti_tahunan') {
            $year = $leave->start_date->year;
            $balance = \App\Models\LeaveBalance::getOrCreate($leave->user_id, $year);
            
            // Count working days (weekdays only) for accurate balance
            $workingDays = 0;
            $date = $leave->start_date->copy();
            while ($date->lte($leave->end_date)) {
                if ($date->isWeekday()) {
                    $workingDays++;
                }
                $date->addDay();
            }
            
            $balance->useLeave($workingDays);
        }

        \App\Services\NotificationService::leaveProcessed($leave);

        return back()->with('success', "Cuti {$leave->user->name} disetujui! {$count} record absensi cuti dibuat.");
    }

    /**
     * Reject pengajuan cuti
     */
    public function rejectLeave(Request $request, \App\Models\Leave $leave)
    {
        if ($leave->status !== 'pending') {
            return back()->with('error', 'Cuti ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $leave->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        \App\Services\NotificationService::leaveProcessed($leave);

        return back()->with('success', "Cuti {$leave->user->name} ditolak.");
    }

    /**
     * List semua pengajuan dinas luar
     */
    public function businessTrips(Request $request)
    {
        $query = \App\Models\BusinessTrip::with('user', 'approver')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $trips = $query->paginate(15);
        $pendingCount = \App\Models\BusinessTrip::where('status', 'pending')->count();

        return view('staff.psdm.business-trips.index', compact('trips', 'pendingCount'));
    }

    /**
     * Approve pengajuan dinas luar + auto-create attendance records
     */
    public function approveBusinessTrip(\App\Models\BusinessTrip $businessTrip)
    {
        if ($businessTrip->status !== 'pending') {
            return back()->with('error', 'Dinas luar ini sudah diproses sebelumnya.');
        }

        $businessTrip->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        // Auto-create attendance records for business trip period
        $service = new \App\Services\AttendanceService();
        $count = $service->createBusinessTripAttendances($businessTrip);

        \App\Services\NotificationService::businessTripProcessed($businessTrip);

        return back()->with('success', "Dinas luar {$businessTrip->user->name} disetujui! {$count} record absensi dinas luar dibuat.");
    }

    /**
     * Reject pengajuan dinas luar
     */
    public function rejectBusinessTrip(Request $request, \App\Models\BusinessTrip $businessTrip)
    {
        if ($businessTrip->status !== 'pending') {
            return back()->with('error', 'Dinas luar ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $businessTrip->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        \App\Services\NotificationService::businessTripProcessed($businessTrip);

        return back()->with('success', "Dinas luar {$businessTrip->user->name} ditolak.");
    }

    /**
     * Delete attendance record
     */
    public function deleteAttendance(Attendance $attendance)
    {
        $userName = $attendance->user->name ?? 'Unknown';
        $date = Carbon::parse($attendance->check_in_time)->format('d M Y');

        // Delete photo files if they exist
        if ($attendance->photo_path && !\Illuminate\Support\Str::contains($attendance->photo_path, ['cuti', 'dinas_luar'])) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($attendance->photo_path);
        }
        if ($attendance->check_out_photo_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($attendance->check_out_photo_path);
        }

        $attendance->delete();

        return back()->with('success', "Absensi {$userName} tanggal {$date} berhasil dihapus. Karyawan dapat melakukan absensi ulang untuk tanggal tersebut.");
    }
}
