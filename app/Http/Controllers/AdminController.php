<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use App\Models\Shift;
use App\Models\ShiftLog;

class AdminController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $settings = \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key');
        $shiftsCount = Shift::count();
        
        $totalAdmins = \App\Models\User::where('role', 'admin')->count();
        $totalPsdm = \App\Models\User::where('role', 'staff_psdm')->count();
        $totalKeuangan = \App\Models\User::where('role', 'staff_keuangan')->count();
        $totalPegawai = \App\Models\User::where('role', 'user')->count();

        // Recent activity logs across the system
        $activityLogs = \App\Models\ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'settings', 
            'shiftsCount', 
            'totalAdmins', 
            'totalPsdm', 
            'totalKeuangan', 
            'totalPegawai',
            'activityLogs'
        ));
    }

    public function manualCheckIn(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'check_in_time' => 'required',
            'status' => 'required|in:present,late',
        ]);

        $today = \Carbon\Carbon::today();
        $user = \App\Models\User::findOrFail($request->user_id);
        
        // Check if user already has attendance today
        $existing = \App\Models\Attendance::where('user_id', $request->user_id)
            ->where('work_date', $today->toDateString())
            ->first();
        
        if ($existing) {
            return back()->with('error', 'User sudah memiliki presensi hari ini!');
        }

        $attendanceService = app(\App\Services\AttendanceService::class);
        $checkInTime = \Carbon\Carbon::parse($today->format('Y-m-d') . ' ' . $request->check_in_time);
        $isLate = $request->status === 'late';

        // Tipe Umum: tidak ada shift, tidak ada batas max, tidak pernah late
        if ($user->isUmumAttendance()) {
            \App\Models\Attendance::create([
                'user_id'           => $request->user_id,
                'shift_id'          => null,
                'attendance_type'   => 'umum',
                'photo_path'        => 'manual/admin_input.png',
                'check_in_time'     => $checkInTime,
                'work_date'         => $today->toDateString(),
                'min_check_out_time'=> $checkInTime->copy()->addHours(8),
                'max_check_out_time'=> null,
                'latitude'          => 0,
                'longitude'         => 0,
                'status'            => 'present',
            ]);
            return back()->with('success', 'Presensi Manual (Umum) Berhasil Ditambahkan!');
        }

        // Normal / Shift
        $shift = $attendanceService->getApplicableShift($user, $checkInTime);
        $minCheckOutTime = null;
        $maxCheckOutTime = null;
        if ($shift) {
            $minCheckOutTime = $attendanceService->calculateMinCheckOutTime($checkInTime, $shift, $isLate);
            $maxCheckOutTime = $attendanceService->calculateMaxCheckOutTime($checkInTime, $shift);
        }

        \App\Models\Attendance::create([
            'user_id'           => $request->user_id,
            'shift_id'          => $shift?->id,
            'attendance_type'   => $user->isNormalAttendance() ? 'normal' : 'shift',
            'photo_path'        => 'manual/admin_input.png',
            'check_in_time'     => $checkInTime,
            'work_date'         => $today->toDateString(),
            'min_check_out_time'=> $minCheckOutTime,
            'max_check_out_time'=> $maxCheckOutTime,
            'latitude'          => 0,
            'longitude'         => 0,
            'status'            => $request->status,
        ]);

        return back()->with('success', 'Presensi Manual Berhasil Ditambahkan!');
    }

    public function settings()
    {
        $settings = \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key');
        $shifts = Shift::all();
        $shiftLogs = ShiftLog::with(['shift', 'changedByUser'])
            ->latest()
            ->take(20)
            ->get();

        // Get the admin who last updated the location settings
        $latSetting = \Illuminate\Support\Facades\DB::table('settings')->where('key', 'office_latitude')->first();
        $updater = $latSetting && $latSetting->updated_by ? \App\Models\User::find($latSetting->updated_by) : null;
        $updatedAt = $latSetting ? $latSetting->updated_at : null;

        return view('admin.settings', compact('settings', 'shifts', 'shiftLogs', 'updater', 'updatedAt'));
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

        // Capture old values for audit logging
        $oldSettings = \Illuminate\Support\Facades\DB::table('settings')
            ->whereIn('key', $keys)
            ->pluck('value', 'key')
            ->toArray();

        foreach ($request->only($keys) as $key => $value) {
            \Illuminate\Support\Facades\DB::table('settings')->where('key', $key)->update([
                'value' => $value,
                'updated_by' => auth()->id(),
                'updated_at' => now()
            ]);
        }

        $newSettings = $request->only($keys);

        // Record log to ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'Setting',
            'model_id' => null,
            'description' => 'Mengubah koordinat lokasi kantor & radius presensi',
            'old_values' => $oldSettings,
            'new_values' => $newSettings,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Pengaturan Lokasi Berhasil Diupdate!');
    }

    public function updateShift(\Illuminate\Http\Request $request, Shift $shift)
    {
        $request->validate([
            'start_time' => 'required',
            'end_time' => 'required',
            'tolerance_minutes' => 'required|integer|min:0|max:120',
        ]);

        // Validasi durasi shift harus tepat 8 jam (480 menit)
        // Toleransi 479 menit untuk Shift 3 yang berakhir 23:59
        $start = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
        $end = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);

        // Jika jam selesai lebih kecil dari jam mulai, berarti melewati tengah malam
        if ($end->lte($start)) {
            $end->addDay();
        }

        $durasiMenit = $start->diffInMinutes($end);

        // Validasi batas bawah (Minimal 8 Jam)
        if ($durasiMenit < 479) {
            $jam = floor($durasiMenit / 60);
            $menit = $durasiMenit % 60;
            return back()->withErrors([
                'end_time' => "Durasi shift terlalu pendek ({$jam} jam {$menit} menit). Harus tepat 8 jam."
            ])->withInput();
        }

        // Validasi batas atas (Maksimal 8 Jam)
        if ($durasiMenit > 480) {
            $jam = floor($durasiMenit / 60);
            $menit = $durasiMenit % 60;
            return back()->withErrors([
                'end_time' => "Durasi shift terlalu panjang ({$jam} jam {$menit} menit). Harus tepat 8 jam."
            ])->withInput();
        }

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

        $hasChanges = false;
        foreach ($fieldsToCheck as $fieldName => $values) {
            if ($values['old'] !== $values['new']) {
                ShiftLog::create([
                    'shift_id' => $shift->id,
                    'changed_by' => auth()->id(),
                    'field_name' => $fieldName,
                    'old_value' => $values['old'],
                    'new_value' => $values['new'],
                ]);
                $hasChanges = true;
            }
        }

        if ($hasChanges) {
            // Record log to ActivityLog
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => get_class($shift),
                'model_id' => $shift->id,
                'description' => "Mengubah pengaturan Shift '{$shift->name}'",
                'old_values' => [
                    'start_time' => $oldStartTime,
                    'end_time' => $oldEndTime,
                    'tolerance_minutes' => $oldTolerance
                ],
                'new_values' => [
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'tolerance_minutes' => $request->tolerance_minutes
                ],
                'ip_address' => request()->ip(),
            ]);
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
            'nip' => 'nullable|string|regex:/^[0-9]{18}$/|unique:users,nip',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'string', 'max:20', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => 'required|in:admin,staff_psdm,staff_keuangan',
        ], [
            'nip.regex' => 'NIP harus berupa 18 digit angka.',
        ]);

        $staff = \App\Models\User::create([
            'name' => $request->name,
            'nip' => $request->nip,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
        ]);

        // Record log to ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => get_class($staff),
            'model_id' => $staff->id,
            'description' => "Menambahkan staff baru: '{$staff->name}' ({$staff->role})",
            'old_values' => null,
            'new_values' => [
                'name' => $staff->name,
                'nip' => $staff->nip,
                'email' => $staff->email,
                'role' => $staff->role
            ],
            'ip_address' => request()->ip(),
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
            'nip' => 'nullable|string|regex:/^[0-9]{18}$/|unique:users,nip,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,staff_psdm,staff_keuangan',
            'password' => ['nullable', 'string', 'max:20', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'nip.regex' => 'NIP harus berupa 18 digit angka.',
        ]);

        $oldData = [
            'name' => $user->name,
            'nip' => $user->nip,
            'email' => $user->email,
            'role' => $user->role,
        ];

        $data = [
            'name' => $request->name,
            'nip' => $request->nip,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        // Record log to ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => get_class($user),
            'model_id' => $user->id,
            'description' => "Memperbarui data staff: '{$user->name}'",
            'old_values' => $oldData,
            'new_values' => [
                'name' => $user->name,
                'nip' => $user->nip,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'ip_address' => request()->ip(),
        ]);

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

        $oldData = [
            'name' => $user->name,
            'nip' => $user->nip,
            'email' => $user->email,
            'role' => $user->role,
        ];

        $user->delete();

        // Record log to ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => get_class($user),
            'model_id' => $user->id,
            'description' => "Menghapus staff: '{$user->name}'",
            'old_values' => $oldData,
            'new_values' => null,
            'ip_address' => request()->ip(),
        ]);

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
            'nip' => 'nullable|string|regex:/^[0-9]{18}$/|unique:users,nip',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'string', 'max:20', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'nip.regex' => 'NIP harus berupa 18 digit angka.',
        ]);

        $admin = \App\Models\User::create([
            'name' => $request->name,
            'nip' => $request->nip,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'admin',
        ]);

        // Record log to ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => get_class($admin),
            'model_id' => $admin->id,
            'description' => "Menambahkan admin baru: '{$admin->name}'",
            'old_values' => null,
            'new_values' => [
                'name' => $admin->name,
                'nip' => $admin->nip,
                'email' => $admin->email,
                'role' => $admin->role
            ],
            'ip_address' => request()->ip(),
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
            'nip' => 'nullable|string|regex:/^[0-9]{18}$/|unique:users,nip,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => ['nullable', 'string', 'max:20', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'nip.regex' => 'NIP harus berupa 18 digit angka.',
        ]);

        $oldData = [
            'name' => $user->name,
            'nip' => $user->nip,
            'email' => $user->email,
            'role' => $user->role,
        ];

        $data = [
            'name' => $request->name,
            'nip' => $request->nip,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        // Record log to ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => get_class($user),
            'model_id' => $user->id,
            'description' => "Memperbarui data admin: '{$user->name}'",
            'old_values' => $oldData,
            'new_values' => [
                'name' => $user->name,
                'nip' => $user->nip,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'ip_address' => request()->ip(),
        ]);

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

        $oldData = [
            'name' => $user->name,
            'nip' => $user->nip,
            'email' => $user->email,
            'role' => $user->role,
        ];

        $user->delete();

        // Record log to ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => get_class($user),
            'model_id' => $user->id,
            'description' => "Menghapus admin: '{$user->name}'",
            'old_values' => $oldData,
            'new_values' => null,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('admin.admins')
            ->with('success', 'Admin berhasil dihapus!');
    }

    /**
     * Monitor presensi semua user (read-only overview)
     */
    public function monitor(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\Attendance::with(['user', 'shift'])->latest('check_in_time');
        
        if ($request->filled('date')) {
            $query->where('work_date', $request->date);
        } elseif ($request->filled('month') || $request->filled('year')) {
            $month = $request->input('month', now()->month);
            $year = $request->input('year', now()->year);
            $query->whereMonth('work_date', $month)
                  ->whereYear('work_date', $year);
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
     * Export rekap presensi ke Excel
     */
    public function exportAttendance(\Illuminate\Http\Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        $filename = 'rekap_presensi_' . $month . '_' . $year . '.xlsx';
        
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

}


