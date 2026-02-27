<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\ShiftLog;

class AdminController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\Attendance::with(['user', 'shift'])->latest();
        
        // Filter by date
        if ($request->filled('filter_date')) {
            $query->whereDate('check_in_time', $request->filter_date);
        }
        
        // Filter by user
        if ($request->filled('filter_user')) {
            $query->where('user_id', $request->filter_user);
        }
        
        $attendances = $query->get();
        $settings = \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key');
        $users = \App\Models\User::where('role', 'user')->get();
        $shifts = Shift::all();
        $shiftLogs = ShiftLog::with(['shift', 'changedByUser'])
            ->latest()
            ->take(20)
            ->get();
        
        return view('admin.dashboard', compact('attendances', 'settings', 'users', 'shifts', 'shiftLogs'));
    }

    public function manualCheckIn(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'check_in_time' => 'required',
            'status' => 'required|in:present,late',
        ]);

        $today = \Carbon\Carbon::today();
        $user = User::findOrFail($request->user_id);
        
        // Check if user already has attendance today
        $existing = \App\Models\Attendance::where('user_id', $request->user_id)
            ->whereDate('check_in_time', $today)
            ->first();
        
        if ($existing) {
            return back()->with('error', 'User sudah memiliki absensi hari ini!');
        }

        // Use AttendanceService to get shift and calculate times
        $attendanceService = app(\App\Services\AttendanceService::class);
        $checkInTime = \Carbon\Carbon::parse($today->format('Y-m-d') . ' ' . $request->check_in_time);
        $shift = $attendanceService->getApplicableShift($user, $checkInTime);
        $isLate = $request->status === 'late';
        
        $minCheckOutTime = null;
        $maxCheckOutTime = null;
        if ($shift) {
            $minCheckOutTime = $attendanceService->calculateMinCheckOutTime($checkInTime, $shift, $isLate);
            $maxCheckOutTime = $attendanceService->calculateMaxCheckOutTime($checkInTime, $shift);
        }

        \App\Models\Attendance::create([
            'user_id' => $request->user_id,
            'shift_id' => $shift?->id,
            'attendance_type' => $user->isNormalAttendance() ? 'normal' : 'shift',
            'photo_path' => 'manual/admin_input.png',
            'check_in_time' => $checkInTime,
            'min_check_out_time' => $minCheckOutTime,
            'max_check_out_time' => $maxCheckOutTime,
            'latitude' => 0,
            'longitude' => 0,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Absensi Manual Berhasil Ditambahkan!');
    }

    public function settings()
    {
        $settings = \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key');
        $shifts = Shift::all();
        $shiftLogs = ShiftLog::with(['shift', 'changedByUser'])
            ->latest()
            ->take(20)
            ->get();

        return view('admin.settings', compact('settings', 'shifts', 'shiftLogs'));
    }

    public function updateSettings(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'office_latitude' => 'required|numeric',
            'office_longitude' => 'required|numeric',
            'allowed_radius_meters' => 'required|numeric',
        ]);

        $keys = [
            'office_latitude', 
            'office_longitude', 
            'allowed_radius_meters',
        ];

        foreach ($request->only($keys) as $key => $value) {
            \Illuminate\Support\Facades\DB::table('settings')->where('key', $key)->update(['value' => $value]);
        }

        return back()->with('success', 'Pengaturan Lokasi Berhasil Diupdate!');
    }

    public function updateShift(\Illuminate\Http\Request $request, Shift $shift)
    {
        $request->validate([
            'start_time' => 'required',
            'end_time' => 'required',
            'tolerance_minutes' => 'required|integer|min:0|max:120',
        ]);

        // Get old values for logging
        $oldStartTime = $shift->start_time instanceof \Carbon\Carbon 
            ? $shift->start_time->format('H:i') 
            : substr($shift->start_time, 0, 5);
        $oldEndTime = $shift->end_time instanceof \Carbon\Carbon 
            ? $shift->end_time->format('H:i') 
            : substr($shift->end_time, 0, 5);
        $oldTolerance = $shift->tolerance_minutes;

        // Log changes for each field that changed
        $fieldsToCheck = [
            'start_time' => ['old' => $oldStartTime, 'new' => $request->start_time],
            'end_time' => ['old' => $oldEndTime, 'new' => $request->end_time],
            'tolerance_minutes' => ['old' => (string) $oldTolerance, 'new' => (string) $request->tolerance_minutes],
        ];

        foreach ($fieldsToCheck as $fieldName => $values) {
            if ($values['old'] !== $values['new']) {
                ShiftLog::create([
                    'shift_id' => $shift->id,
                    'changed_by' => auth()->id(),
                    'field_name' => $fieldName,
                    'old_value' => $values['old'],
                    'new_value' => $values['new'],
                ]);
            }
        }

        // Update the shift
        $shift->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'tolerance_minutes' => $request->tolerance_minutes,
        ]);

        return back()->with('success', "Shift '{$shift->name}' berhasil diupdate!");
    }

    /**
     * List semua staff (staff_psdm, staff_keuangan only)
     */
    public function staffs(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\User::whereIn('role', ['staff_psdm', 'staff_keuangan'])
            ->orderBy('role')
            ->orderBy('name');
        
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }
        
        $staffs = $query->paginate(15);
        
        return view('admin.staffs.index', compact('staffs'));
    }

    /**
     * Form tambah staff
     */
    public function createStaff()
    {
        return view('admin.staffs.create');
    }

    /**
     * Simpan staff baru
     */
    public function storeStaff(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:30|unique:users,nip',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,staff_psdm,staff_keuangan',
        ]);

        \App\Models\User::create([
            'name' => $request->name,
            'nip' => $request->nip,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.staffs')
            ->with('success', 'Staff berhasil ditambahkan!');
    }

    /**
     * Form edit staff
     */
    public function editStaff(\App\Models\User $user)
    {
        if (!in_array($user->role, ['admin', 'staff_psdm', 'staff_keuangan'])) {
            abort(404, 'User bukan staff');
        }
        return view('admin.staffs.edit', compact('user'));
    }

    /**
     * Update staff
     */
    public function updateStaff(\Illuminate\Http\Request $request, \App\Models\User $user)
    {
        if (!in_array($user->role, ['admin', 'staff_psdm', 'staff_keuangan'])) {
            abort(404, 'User bukan staff');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:30|unique:users,nip,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,staff_psdm,staff_keuangan',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'nip' => $request->nip,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.staffs')
            ->with('success', 'Data staff berhasil diupdate!');
    }

    /**
     * Hapus staff
     */
    public function deleteStaff(\App\Models\User $user)
    {
        if (!in_array($user->role, ['admin', 'staff_psdm', 'staff_keuangan'])) {
            abort(404, 'User bukan staff');
        }

        // Jangan hapus diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun Anda sendiri!');
        }

        $user->delete();

        return redirect()->route('admin.staffs')
            ->with('success', 'Staff berhasil dihapus!');
    }

    // ==================== ADMIN CRUD ====================

    /**
     * List semua admin
     */
    public function admins(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\User::where('role', 'admin')
            ->orderBy('name');
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }
        
        $admins = $query->paginate(15);
        
        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Form tambah admin
     */
    public function createAdmin()
    {
        return view('admin.admins.create');
    }

    /**
     * Simpan admin baru
     */
    public function storeAdmin(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:30|unique:users,nip',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        \App\Models\User::create([
            'name' => $request->name,
            'nip' => $request->nip,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'admin',
        ]);

        return redirect()->route('admin.admins')
            ->with('success', 'Admin berhasil ditambahkan!');
    }

    /**
     * Form edit admin
     */
    public function editAdmin(\App\Models\User $user)
    {
        if ($user->role !== 'admin') {
            abort(404, 'User bukan admin');
        }
        return view('admin.admins.edit', compact('user'));
    }

    /**
     * Update admin
     */
    public function updateAdmin(\Illuminate\Http\Request $request, \App\Models\User $user)
    {
        if ($user->role !== 'admin') {
            abort(404, 'User bukan admin');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:30|unique:users,nip,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'nip' => $request->nip,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.admins')
            ->with('success', 'Data admin berhasil diupdate!');
    }

    /**
     * Hapus admin
     */
    public function deleteAdmin(\App\Models\User $user)
    {
        if ($user->role !== 'admin') {
            abort(404, 'User bukan admin');
        }

        // Jangan hapus diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun Anda sendiri!');
        }

        $user->delete();

        return redirect()->route('admin.admins')
            ->with('success', 'Admin berhasil dihapus!');
    }

    /**
     * Monitor absensi semua user (read-only overview)
     */
    public function monitor(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\Attendance::with(['user', 'shift'])->latest('check_in_time');
        
        if ($request->filled('date')) {
            $query->whereDate('check_in_time', $request->date);
        } elseif ($request->filled('month') || $request->filled('year')) {
            $month = $request->input('month', now()->month);
            $year = $request->input('year', now()->year);
            $query->whereMonth('check_in_time', $month)
                  ->whereYear('check_in_time', $year);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }
        
        $attendances = $query->paginate(20)->withQueryString();
        
        return view('admin.monitor', compact('attendances'));
    }

    /**
     * Lihat semua pengajuan cuti (read-only)
     */
    public function leaves(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\Leave::with('user', 'approver')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves = $query->paginate(15);
        $pendingCount = \App\Models\Leave::where('status', 'pending')->count();

        return view('admin.leaves.index', compact('leaves', 'pendingCount'));
    }

    /**
     * Export rekap absensi ke Excel
     */
    public function exportAttendance(\Illuminate\Http\Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        $filename = 'rekap_absensi_' . $month . '_' . $year . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AttendanceExport('month', ['month' => $month, 'year' => $year]), 
            $filename
        );
    }

    /**
     * View activity logs
     */
    public function activityLogs(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\ActivityLog::with('user')->latest();
        
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        
        $logs = $query->paginate(20);
        
        return view('admin.activity-logs', compact('logs'));
    }

    /**
 * Master Data Management
 */
public function masterData(\Illuminate\Http\Request $request)
{
    $currentScope = $request->get('scope', 'psdm');
    $types = \App\Models\MasterDataType::where('scope', $currentScope)->withCount('values')->get();
    return view('admin.master-data.index', compact('types', 'currentScope'));
}

    public function storeMasterDataType(\Illuminate\Http\Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'scope' => 'required|in:psdm,keuangan',
        'description' => 'nullable|string|max:255',
    ]);

    \App\Models\MasterDataType::create([
        'name' => $request->name,
        'scope' => $request->scope,
        'description' => $request->description,
    ]);

    return redirect()->route('admin.master-data', ['scope' => $request->scope])
        ->with('success', 'Kategori master data berhasil ditambahkan!');
}

    public function destroyMasterDataType(\App\Models\MasterDataType $type)
    {
        $scope = $type->scope;
        $type->delete();
        return redirect()->route('admin.master-data', ['scope' => $scope])->with('success', 'Kategori master data berhasil dihapus!');
    }

    public function updateMasterDataType(\Illuminate\Http\Request $request, \App\Models\MasterDataType $type)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $type->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.master-data', ['scope' => $type->scope])
            ->with('success', 'Kategori "' . $type->name . '" berhasil diupdate!');
    }

    public function showMasterDataType(\App\Models\MasterDataType $type)
    {
        $type->load('values');
        return view('admin.master-data.show', compact('type'));
    }

    public function storeMasterDataValue(\Illuminate\Http\Request $request, \App\Models\MasterDataType $type)
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
}

