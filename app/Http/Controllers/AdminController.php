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
        
        // Check if user already has attendance today
        $existing = \App\Models\Attendance::where('user_id', $request->user_id)
            ->whereDate('check_in_time', $today)
            ->first();
        
        if ($existing) {
            return back()->with('error', 'User sudah memiliki absensi hari ini!');
        }

        \App\Models\Attendance::create([
            'user_id' => $request->user_id,
            'photo_path' => 'manual/admin_input.png', // placeholder
            'check_in_time' => $today->format('Y-m-d') . ' ' . $request->check_in_time,
            'latitude' => 0,
            'longitude' => 0,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Absensi Manual Berhasil Ditambahkan!');
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
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,staff_psdm,staff_keuangan',
        ]);

        \App\Models\User::create([
            'name' => $request->name,
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
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,staff_psdm,staff_keuangan',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name' => $request->name,
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
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        \App\Models\User::create([
            'name' => $request->name,
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
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name' => $request->name,
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
}

