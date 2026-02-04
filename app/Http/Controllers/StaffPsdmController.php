<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Announcement;
use Carbon\Carbon;

class StaffPsdmController extends Controller
{
    /**
     * Dashboard Staff PSDM - Statistik absensi
     */
    public function index()
    {
        $today = Carbon::today();
        
        // Statistik hari ini
        $totalUsers = User::where('role', 'user')->count();
        $todayAttendances = Attendance::whereDate('check_in_time', $today)->count();
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
        
        if ($request->filled('employee_type')) {
            $query->where('employee_type', $request->employee_type);
        }
        
        $users = $query->orderBy('name')->paginate(15);
        
        return view('staff.psdm.users.index', compact('users'));
    }

    /**
     * Form create user
     */
    public function createUser()
    {
        return view('staff.psdm.users.create');
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'employee_type' => 'required|in:ob,satpam',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'user',
            'employee_type' => $request->employee_type,
        ]);

        return redirect()->route('staff.psdm.users')
            ->with('success', 'User berhasil ditambahkan!');
    }

    /**
     * Form edit user
     */
    public function editUser(User $user)
    {
        return view('staff.psdm.users.edit', compact('user'));
    }

    /**
     * Update user (data dasar saja - data keuangan dikelola Staff Keuangan)
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'employee_type' => 'nullable|in:ob,satpam',
            'attendance_type' => 'required|in:normal,shift',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'employee_type' => $request->employee_type,
            'attendance_type' => $request->attendance_type,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->route('staff.psdm.users')
            ->with('success', 'User berhasil diupdate!');
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        $user->delete();

        return redirect()->route('staff.psdm.users')
            ->with('success', 'User berhasil dihapus!');
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

        Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'created_by' => auth()->id(),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('staff.psdm.announcements')
            ->with('success', 'Pengumuman berhasil ditambahkan!');
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
        $query = Attendance::with(['user', 'shift'])->latest();
        
        if ($request->filled('date')) {
            $query->whereDate('check_in_time', $request->date);
        } else {
            $query->whereDate('check_in_time', Carbon::today());
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        $attendances = $query->paginate(20);
        $users = User::where('role', 'user')->orderBy('name')->get();
        
        return view('staff.psdm.monitor', compact('attendances', 'users'));
    }
}
