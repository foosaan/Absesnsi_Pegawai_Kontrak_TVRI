<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\EmployeeProfile;
use App\Models\Attendance;
use App\Models\Announcement;
use App\Models\MasterData;
use Carbon\Carbon;

class StaffPsdmController extends Controller
{
    /**
     * Helper: Ambil semua Master Data PSDM untuk form
     */
    private function getMasterDataPsdm()
    {
        $masterData = MasterData::where('is_active', true)
            ->orderBy('value')
            ->get()
            ->groupBy('type');
            
        $masterDataTypes = collect([
            'jabatan' => (object)[
                'name' => 'Jabatan',
                'values' => $masterData->get('jabatan', collect())
            ],
            'bagian' => (object)[
                'name' => 'Bagian',
                'values' => $masterData->get('bagian', collect())
            ],
            'status-pegawai' => (object)[
                'name' => 'Status Pegawai',
                'values' => $masterData->get('status_pegawai', collect())
            ],
            'status_operasional' => (object)[
                'name' => 'Status Operasional',
                'values' => $masterData->get('status_operasional', collect())
            ],
        ]);
        
        return ['masterDataTypes' => $masterDataTypes];
    }

    /**
     * Dashboard Staff PSDM - Statistik presensi
     */
    public function index()
    {
        $today = Carbon::today();
        
        // Statistik hari ini
        $totalUsers = User::where('role', 'user')->count();
        $todayAttendances = Attendance::where('work_date', $today->toDateString())->where('status', '!=', 'cuti')->count();
        $todayLate = Attendance::where('work_date', $today->toDateString())->where('status', 'late')->count();
        $todayOnTime = $todayAttendances - $todayLate;
        
        // Presensi terbaru hari ini
        $recentAttendances = Attendance::with(['user', 'shift'])
            ->where('work_date', $today->toDateString())
            ->latest()
            ->take(10)
            ->get();
        
        // Pengumuman aktif
        $announcements = Announcement::active()->latest()->take(5)->get();
        
        // Data grafik: Statistik kehadiran mingguan (7 hari terakhir) — kueri tunggal
        $chartLabels = [];
        $chartHadir = [];
        $chartTerlambat = [];
        $chartCuti = [];

        $weekStart = now()->subDays(6)->startOfDay();
        $weekEnd = now()->endOfDay();
        $weekAttendances = Attendance::whereBetween('work_date', [$weekStart->toDateString(), $weekEnd->toDateString()])->get();
        $groupedByDate = $weekAttendances->groupBy(function ($attendance) {
            return $attendance->work_date instanceof \Carbon\Carbon 
                ? $attendance->work_date->format('Y-m-d') 
                : $attendance->work_date;
        });

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->toDateString();
            $chartLabels[] = $date->translatedFormat('D, d M');

            $dayAttendances = $groupedByDate->get($dateStr, collect());
            $chartHadir[] = $dayAttendances->where('status', 'present')->count();
            $chartTerlambat[] = $dayAttendances->where('status', 'late')->count();
            $chartCuti[] = $dayAttendances->where('status', 'cuti')->count();
        }

        // Diagram lingkaran: Rincian status bulan ini
        $monthAttendances = Attendance::whereBetween('work_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->get();
        $pieData = [
            'hadir' => $monthAttendances->where('status', 'present')->count(),
            'terlambat' => $monthAttendances->where('status', 'late')->count(),
            'cuti' => $monthAttendances->where('status', 'cuti')->count(),
            'tidak_hadir' => $monthAttendances->where('status', 'absent')->count(),
        ];

        return view('staff.psdm.dashboard', compact(
            'totalUsers',
            'todayAttendances',
            'todayLate',
            'todayOnTime',
            'recentAttendances',
            'announcements',
            'chartLabels', 'chartHadir', 'chartTerlambat', 'chartCuti', 'pieData'
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
        
        if ($request->filled('jabatan')) {
            $query->whereHas('jabatanValue', function($q) use ($request) {
                $q->where('value', $request->jabatan);
            });
        }
        
        if ($request->filled('attendance_type')) {
            $query->whereHas('profile', function($q) use ($request) {
                $q->where('attendance_type', $request->attendance_type);
            });
        }
        
        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        
        $jabatanList = DB::table('master_data')
            ->where('type', 'jabatan')
            ->where('is_active', true)
            ->pluck('value')
            ->sort();
        
        return view('staff.psdm.users.index', compact('users', 'jabatanList'));
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
        'nip' => 'required|string|regex:/^[0-9]{18}$/|unique:users,nip',
        'nik' => 'required|string|regex:/^[0-9]{16}$/|unique:employee_profiles,nik',
        'email' => 'required|email|unique:users,email',
        'password' => ['required', 'string', 'max:20', Password::min(8)->mixedCase()->numbers()->symbols()],
        'attendance_type' => 'required|in:normal,shift,umum',
        'jenis_kelamin' => 'nullable|in:L,P',
        'no_telepon' => 'required|string|regex:/^[0-9]{10,13}$/',
    ], [
        'nip.regex' => 'NIP harus berupa 18 digit angka.',
        'nik.regex' => 'NIK harus berupa 16 digit angka.',
        'no_telepon.required' => 'No. Telepon/HP belum diisi atau tidak valid (max 15 angka, tanpa spasi, tanpa karakter selain angka)',
        'no_telepon.regex' => 'No. Telepon/HP belum diisi atau tidak valid (max 15 angka, tanpa spasi, tanpa karakter selain angka)',
    ]);

    // Map master_data fields ke kolom user
    $masterData = $request->input('master_data', []);

    // Buat akun user (data autentikasi + NIP)
    $employee = User::create([
        'name' => $request->name,
        'nip' => $request->nip,
        'email' => $request->email,
        'password' => $request->password,
        'role' => 'user',
    ]);

    // Buat profil kepegawaian (tabel terpisah — biodata saja, tanpa NIP)
    $employee->profile()->create([
        'nik' => $request->nik,
        'alamat' => $request->alamat,
        'no_telepon' => $request->no_telepon,
        'jenis_kelamin' => $request->jenis_kelamin,
        'attendance_type' => $request->attendance_type ?? 'normal',
        'jabatan_id' => $masterData['jabatan'] ?? null,
        'bagian_id' => $masterData['bagian'] ?? null,
        'status_pegawai_id' => $masterData['status-pegawai'] ?? null,
        'status_operasional_id' => $masterData['status_operasional'] ?? null,
    ]);

    // Catat log ke ActivityLog
    \App\Models\ActivityLog::create([
        'user_id' => auth()->id(),
        'action' => 'create',
        'model_type' => get_class($employee),
        'model_id' => $employee->id,
        'description' => "Menambahkan pegawai baru: '{$employee->name}'",
        'old_values' => null,
        'new_values' => [
            'name' => $employee->name,
            'nip' => $employee->nip,
            'email' => $employee->email,
            'attendance_type' => $employee->attendance_type,
            'jabatan' => $employee->jabatan
        ],
        'ip_address' => request()->ip(),
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
            'nip' => 'required|string|regex:/^[0-9]{18}$/|unique:users,nip,' . $user->id,
            'nik' => 'required|string|regex:/^[0-9]{16}$/|unique:employee_profiles,nik,' . ($user->profile->id ?? ''),
            'email' => 'required|email|unique:users,email,' . $user->id,
            'attendance_type' => 'required|in:normal,shift,umum',
            'jenis_kelamin' => 'nullable|in:L,P',
            'no_telepon' => 'required|string|regex:/^[0-9]{10,13}$/',
        ], [
            'nip.regex' => 'NIP harus berupa 18 digit angka.',
            'nik.regex' => 'NIK harus berupa 16 digit angka.',
            'no_telepon.required' => 'No. Telepon/HP belum diisi atau tidak valid (max 15 angka, tanpa spasi, tanpa karakter selain angka)',
            'no_telepon.regex' => 'No. Telepon/HP belum diisi atau tidak valid (max 15 angka, tanpa spasi, tanpa karakter selain angka)',
        ]);

        $oldData = [
            'name' => $user->name,
            'nip' => $user->nip,
            'email' => $user->email,
            'attendance_type' => $user->attendance_type,
            'jabatan' => $user->jabatan,
            'bagian' => $user->bagian,
            'status_pegawai' => $user->status_pegawai,
            'status_operasional' => $user->status_operasional,
        ];

        // Map master_data fields ke kolom user
        $masterData = $request->input('master_data', []);

        // Update data autentikasi + NIP di tabel users
        $userData = [
            'name' => $request->name,
            'nip' => $request->nip,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => ['string', 'max:20', Password::min(8)->mixedCase()->numbers()->symbols()]]);
            $userData['password'] = $request->password;
        }

        $user->update($userData);

        // Update data kepegawaian di tabel employee_profiles (biodata saja)
        $profileData = [
            'nik' => $request->nik,
            'alamat' => $request->alamat,
            'no_telepon' => $request->no_telepon,
            'jenis_kelamin' => $request->jenis_kelamin,
            'attendance_type' => $request->attendance_type,
            'jabatan_id' => $masterData['jabatan'] ?? $user->jabatan_id,
            'bagian_id' => $masterData['bagian'] ?? $user->bagian_id,
            'status_pegawai_id' => $masterData['status-pegawai'] ?? $user->status_pegawai_id,
            'status_operasional_id' => $masterData['status_operasional'] ?? $user->status_operasional_id,
        ];

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        // Refresh relasi setelah update
        $user->load('profile');

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => get_class($user),
            'model_id' => $user->id,
            'description' => "Memperbarui data pegawai: '{$user->name}'",
            'old_values' => $oldData,
            'new_values' => [
                'name' => $user->name,
                'nip' => $user->nip,
                'email' => $user->email,
                'attendance_type' => $user->attendance_type,
                'jabatan' => $user->jabatan,
                'bagian' => $user->bagian,
                'status_pegawai' => $user->status_pegawai,
                'status_operasional' => $user->status_operasional,
            ],
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('staff.psdm.users')
            ->with('success', 'User berhasil diupdate!');
    }

    /**
     * Delete user (with cascade cleanup)
     */
    public function deleteUser(User $user)
    {
        // Cegah menghapus diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        $oldData = [
            'name' => $user->name,
            'nip' => $user->nip,
            'email' => $user->email,
            'attendance_type' => $user->attendance_type,
            'jabatan' => $user->jabatan,
        ];

        // Bersihkan data terkait untuk mencegah data yatim (orphan data)
        // Pakai each()->delete() agar model events (pembersihan file) terpicu
        $user->attendances()->get()->each->delete();
        $user->salaries()->delete();
        \App\Models\Leave::where('user_id', $user->id)->get()->each->delete();
        \App\Models\BusinessTrip::where('user_id', $user->id)->get()->each->delete();
        \App\Models\Notification::where('user_id', $user->id)->delete();
        
        // Hapus profile photo
        if ($user->profile_photo) {
            \Storage::disk('public')->delete($user->profile_photo);
        }
        if ($user->signature) {
            \Storage::disk('public')->delete($user->signature);
        }

        $user->delete();

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => get_class($user),
            'model_id' => $user->id,
            'description' => "Menghapus pegawai: '{$user->name}'",
            'old_values' => $oldData,
            'new_values' => null,
            'ip_address' => request()->ip(),
        ]);

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

        // Dapatkan informasi pengguna yang akan dihapus untuk pencatatan log
        $deletedUsersInfo = User::whereIn('id', $ids)->get(['id', 'name', 'nip', 'email'])->toArray();

        // Bersihkan data terkait — pakai each()->delete() agar file ikut terhapus
        \App\Models\Attendance::whereIn('user_id', $ids)->get()->each->delete();
        \App\Models\Salary::whereIn('user_id', $ids)->delete();
        \App\Models\Leave::whereIn('user_id', $ids)->get()->each->delete();
        \App\Models\BusinessTrip::whereIn('user_id', $ids)->get()->each->delete();
        \App\Models\Notification::whereIn('user_id', $ids)->delete();

        // Hapus profile photo & signature user
        $users = User::whereIn('id', $ids)->get();
        foreach ($users as $u) {
            if ($u->profile_photo) \Storage::disk('public')->delete($u->profile_photo);
            if ($u->signature) \Storage::disk('public')->delete($u->signature);
        }

        $count = User::whereIn('id', $ids)->delete();

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => User::class,
            'model_id' => null,
            'description' => "Menghapus {$count} pegawai secara massal",
            'old_values' => $deletedUsersInfo,
            'new_values' => null,
            'ip_address' => request()->ip(),
        ]);

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
     * Download template Excel untuk import pegawai
     */
    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\UserTemplateExport,
            'template_import_pegawai.xlsx'
        );
    }

    /**
     * Proses import users dari Excel (All-or-Nothing)
     * Jika ada satu saja baris yang tidak valid, seluruh proses dibatalkan.
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

        // Jika ada error, kembalikan ke halaman import dengan daftar error
        if (count($errors) > 0) {
            return redirect()->route('staff.psdm.users.import')
                ->with('error', 'Import dibatalkan! Ditemukan ' . count($errors) . ' kesalahan pada file Excel. Tidak ada data yang disimpan. Silakan perbaiki lalu upload ulang.')
                ->with('import_errors', $errors);
        }

        // Berhasil — catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => User::class,
            'model_id' => null,
            'description' => "Melakukan import data pegawai dari Excel (" . count($results) . " pegawai berhasil diimport)",
            'old_values' => null,
            'new_values' => [
                'success_count' => count($results),
            ],
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('staff.psdm.users')
            ->with('success', count($results) . ' pegawai berhasil diimport.');
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
            'content' => $request->input('content'),
            'created_by' => auth()->id(),
            'is_active' => $request->has('is_active'),
        ]);

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => get_class($announcement),
            'model_id' => $announcement->id,
            'description' => "Membuat pengumuman baru: '{$announcement->title}'",
            'old_values' => null,
            'new_values' => [
                'title' => $announcement->title,
                'content' => $announcement->content,
                'is_active' => $announcement->is_active
            ],
            'ip_address' => request()->ip(),
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

        $oldValues = [
            'title' => $announcement->title,
            'content' => $announcement->content,
            'is_active' => $announcement->is_active
        ];

        $announcement->update([
            'title' => $request->title,
            'content' => $request->input('content'),
            'is_active' => $request->has('is_active'),
        ]);

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => get_class($announcement),
            'model_id' => $announcement->id,
            'description' => "Memperbarui pengumuman: '{$announcement->title}'",
            'old_values' => $oldValues,
            'new_values' => [
                'title' => $announcement->title,
                'content' => $announcement->content,
                'is_active' => $announcement->is_active
            ],
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('staff.psdm.announcements')
            ->with('success', 'Pengumuman berhasil diupdate!');
    }

    /**
     * Toggle pengumuman active status
     */
    public function toggleAnnouncement(Announcement $announcement)
    {
        $oldActive = $announcement->is_active;
        $announcement->update(['is_active' => !$announcement->is_active]);

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => get_class($announcement),
            'model_id' => $announcement->id,
            'description' => "Mengubah status aktif pengumuman '{$announcement->title}' menjadi " . ($announcement->is_active ? 'Aktif' : 'Non-Aktif'),
            'old_values' => ['is_active' => $oldActive],
            'new_values' => ['is_active' => $announcement->is_active],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Status pengumuman berhasil diubah!');
    }

    /**
     * Delete pengumuman
     */
    public function deleteAnnouncement(Announcement $announcement)
    {
        $oldValues = [
            'title' => $announcement->title,
            'content' => $announcement->content,
            'is_active' => $announcement->is_active
        ];

        // Hapus notifikasi terkait pengumuman ini
        \App\Models\Notification::where('type', 'announcement')
            ->where('message', $announcement->title)
            ->delete();

        $announcement->delete();

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => get_class($announcement),
            'model_id' => $announcement->id,
            'description' => "Menghapus pengumuman: '{$announcement->title}'",
            'old_values' => $oldValues,
            'new_values' => null,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('staff.psdm.announcements')
            ->with('success', 'Pengumuman berhasil dihapus!');
    }

    /**
     * Monitor presensi semua user
     */
    public function monitor(Request $request)
    {
        $query = Attendance::with(['user', 'shift', 'createdByUser'])->latest('check_in_time');
        
        // Pemfilteran tanggal - mendukung: tanggal tertentu, bulan saja, atau semua
        if ($request->filled('date')) {
            $query->where('work_date', $request->date);
        } elseif ($request->filled('month') || $request->filled('year')) {
            $month = $request->input('month', now()->month);
            $year = $request->input('year', now()->year);
            $query->whereMonth('work_date', $month)
                  ->whereYear('work_date', $year);
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
        
        // Hitung stats hasil pencarian secara dynamic on-the-fly
        $stats = null;
        if ($request->anyFilled(['search', 'date', 'month', 'year', 'status'])) {
            $statsQuery = Attendance::query();
            
            if ($request->filled('date')) {
                $statsQuery->where('work_date', $request->date);
            } elseif ($request->filled('month') || $request->filled('year')) {
                $month = $request->input('month', now()->month);
                $year = $request->input('year', now()->year);
                $statsQuery->whereMonth('work_date', $month)
                           ->whereYear('work_date', $year);
            }
            
            if ($request->filled('status')) {
                $statsQuery->where('status', $request->status);
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $statsQuery->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%");
                });
            }
            
            $statsData = $statsQuery->get();
            $stats = [
                'hadir' => $statsData->where('status', 'present')->count(),
                'terlambat' => $statsData->where('status', 'late')->count(),
                'cuti' => $statsData->where('status', 'cuti')->count(),
                'dinas_luar' => $statsData->where('status', 'dinas_luar')->count(),
                'tidak_hadir' => $statsData->where('status', 'absent')->count(),
                'total' => $statsData->count(),
            ];
        }
        
        $attendances = $query->paginate(20)->withQueryString();
        $users = User::where('role', 'user')->orderBy('name')->get();
        
        return view('staff.psdm.monitor', compact('attendances', 'users', 'stats'));
    }

    /**
     * Export rekap presensi ke Excel
     */
    public function exportAttendance(Request $request)
    {
        $filterType = $request->get('filter_type', 'month');
        $params = [];

        switch ($filterType) {
            case 'day':
                $date = $request->get('date', Carbon::today()->toDateString());
                $params['date'] = $date;
                $filename = 'rekap_presensi_' . $date . '.xlsx';
                break;
            case 'all':
                $filename = 'rekap_presensi_semua_data.xlsx';
                break;
            default: // month
                $filterType = 'month';
                $month = $request->get('month', Carbon::now()->month);
                $year = $request->get('year', Carbon::now()->year);
                $params['month'] = $month;
                $params['year'] = $year;
                $monthName = Carbon::create($year, $month, 1)->translatedFormat('F');
                $filename = 'rekap_presensi_' . $monthName . '_' . $year . '.xlsx';
                break;
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AttendanceExport($filterType, $params),
            $filename
        );
    }



    /**
     * Master Data PSDM - List
     */
    /**
     * Master Data PSDM - List
     */
    public function masterData()
    {
        $types = collect([
            (object)[
                'id' => 'jabatan',
                'name' => 'Jabatan',
                'description' => 'Daftar jabatan pegawai kontrak',
                'values_count' => \App\Models\MasterData::where('type', 'jabatan')->count(),
            ],
            (object)[
                'id' => 'bagian',
                'name' => 'Bagian',
                'description' => 'Daftar bagian/departemen kepegawaian',
                'values_count' => \App\Models\MasterData::where('type', 'bagian')->count(),
            ],
            (object)[
                'id' => 'status_pegawai',
                'name' => 'Status Pegawai',
                'description' => 'Daftar tipe/status kepegawaian',
                'values_count' => \App\Models\MasterData::where('type', 'status_pegawai')->count(),
            ],
            (object)[
                'id' => 'status_operasional',
                'name' => 'Status Operasional',
                'description' => 'Status tipe jam kerja operasional',
                'values_count' => \App\Models\MasterData::where('type', 'status_operasional')->count(),
            ],
        ]);
        return view('staff.psdm.master-data.index', compact('types'));
    }

    public function showMasterDataType(string $type)
    {
        $allowedTypes = ['jabatan', 'bagian', 'status_pegawai', 'status_operasional'];
        if (!in_array($type, $allowedTypes)) {
            abort(404);
        }

        $values = \App\Models\MasterData::where('type', $type)->orderBy('value')->get();
        
        $typeNames = [
            'jabatan' => 'Jabatan',
            'bagian' => 'Bagian',
            'status_pegawai' => 'Status Pegawai',
            'status_operasional' => 'Status Operasional',
        ];
        
        $typeObj = (object)[
            'id' => $type,
            'name' => $typeNames[$type],
            'description' => 'Kelola pilihan nilai ' . strtolower($typeNames[$type]),
            'values' => $values,
        ];

        return view('staff.psdm.master-data.show', compact('typeObj'))->with('type', $typeObj);
    }

    public function storeMasterDataValue(Request $request, string $type)
    {
        $allowedTypes = ['jabatan', 'bagian', 'status_pegawai', 'status_operasional'];
        if (!in_array($type, $allowedTypes)) {
            abort(404);
        }

        $request->validate([
            'value' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        \App\Models\MasterData::create([
            'type' => $type,
            'value' => $request->value,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return back()->with('success', 'Nilai berhasil ditambahkan!');
    }

    public function destroyMasterDataValue(\App\Models\MasterData $value)
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

        // Statistik keseluruhan
        $leaveStats = [
            'total' => \App\Models\Leave::count(),
            'approved' => \App\Models\Leave::where('status', 'approved')->count(),
            'rejected' => \App\Models\Leave::where('status', 'rejected')->count(),
            'pending' => $pendingCount,
            'total_days' => \App\Models\Leave::where('status', 'approved')->get()->sum('total_days'),
        ];

        return view('staff.psdm.leaves.index', compact('leaves', 'pendingCount', 'leaveStats'));
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

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => get_class($leave),
            'model_id' => $leave->id,
            'description' => "Menyetujui pengajuan cuti pegawai '{$leave->user->name}' ({$leave->type_label})",
            'old_values' => ['status' => 'pending'],
            'new_values' => ['status' => 'approved', 'approved_by' => auth()->id()],
            'ip_address' => request()->ip(),
        ]);

        \App\Services\NotificationService::leaveProcessed($leave);

        // Kirim notifikasi email ke pengguna
        if ($leave->user->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($leave->user->email)
                    ->send(new \App\Mail\LeaveStatusMail($leave));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send leave email', ['error' => $e->getMessage()]);
            }
        }

        return back()->with('success', "Cuti {$leave->user->name} disetujui! {$count} record presensi cuti dibuat.");
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

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => get_class($leave),
            'model_id' => $leave->id,
            'description' => "Menolak pengajuan cuti pegawai '{$leave->user->name}' ({$leave->type_label})",
            'old_values' => ['status' => 'pending'],
            'new_values' => [
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'rejection_reason' => $request->rejection_reason
            ],
            'ip_address' => request()->ip(),
        ]);

        \App\Services\NotificationService::leaveProcessed($leave);

        // Kirim notifikasi email ke pengguna
        if ($leave->user->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($leave->user->email)
                    ->send(new \App\Mail\LeaveStatusMail($leave));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send leave rejection email', ['error' => $e->getMessage()]);
            }
        }

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

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => get_class($businessTrip),
            'model_id' => $businessTrip->id,
            'description' => "Menyetujui pengajuan dinas luar pegawai '{$businessTrip->user->name}' ke {$businessTrip->destination}",
            'old_values' => ['status' => 'pending'],
            'new_values' => ['status' => 'approved', 'approved_by' => auth()->id()],
            'ip_address' => request()->ip(),
        ]);

        \App\Services\NotificationService::businessTripProcessed($businessTrip);

        return back()->with('success', "Dinas luar {$businessTrip->user->name} disetujui! {$count} record presensi dinas luar dibuat.");
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

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => get_class($businessTrip),
            'model_id' => $businessTrip->id,
            'description' => "Menolak pengajuan dinas luar pegawai '{$businessTrip->user->name}' ke {$businessTrip->destination}",
            'old_values' => ['status' => 'pending'],
            'new_values' => [
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'rejection_reason' => $request->rejection_reason
            ],
            'ip_address' => request()->ip(),
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

        $oldValues = [
            'user_name' => $userName,
            'check_in_time' => $attendance->check_in_time,
            'check_out_time' => $attendance->check_out_time,
            'status' => $attendance->status
        ];

        // Delete photo files if they exist
        if ($attendance->photo_path && !\Illuminate\Support\Str::contains($attendance->photo_path, ['cuti', 'dinas_luar'])) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($attendance->photo_path);
        }
        if ($attendance->check_out_photo_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($attendance->check_out_photo_path);
        }

        $attendance->delete();

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => get_class($attendance),
            'model_id' => $attendance->id,
            'description' => "Menghapus data presensi pegawai '{$userName}' tanggal {$date}",
            'old_values' => $oldValues,
            'new_values' => null,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', "Presensi {$userName} tanggal {$date} berhasil dihapus. Karyawan dapat melakukan presensi ulang untuk tanggal tersebut.");
    }

    /**
     * Delete leave record
     */
    public function deleteLeave(\App\Models\Leave $leave)
    {
        $userName = $leave->user->name ?? 'Unknown';

        $oldValues = [
            'user_name' => $userName,
            'type' => $leave->type,
            'start_date' => $leave->start_date,
            'end_date' => $leave->end_date,
            'status' => $leave->status
        ];

        // Hapus file attachment jika ada
        if ($leave->attachment) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($leave->attachment);
        }

        // Hapus attendance record terkait cuti (jika sudah approved)
        Attendance::where('leave_id', $leave->id)->delete();

        // Hapus notifikasi terkait cuti ini
        \App\Models\Notification::where('type', 'pending_leave')
            ->where('message', 'like', '%' . $userName . '%')
            ->delete();

        $leave->delete();

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => get_class($leave),
            'model_id' => $leave->id,
            'description' => "Menghapus data cuti pegawai '{$userName}' ({$leave->type_label})",
            'old_values' => $oldValues,
            'new_values' => null,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', "Data cuti {$userName} berhasil dihapus.");
    }

    /**
     * Delete business trip record
     */
    public function deleteBusinessTrip(\App\Models\BusinessTrip $businessTrip)
    {
        $userName = $businessTrip->user->name ?? 'Unknown';

        $oldValues = [
            'user_name' => $userName,
            'destination' => $businessTrip->destination,
            'start_date' => $businessTrip->start_date,
            'end_date' => $businessTrip->end_date,
            'status' => $businessTrip->status
        ];

        // Hapus file attachment jika ada
        if ($businessTrip->attachment) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($businessTrip->attachment);
        }

        // Hapus attendance record terkait dinas luar
        Attendance::where('business_trip_id', $businessTrip->id)->delete();

        // Hapus notifikasi terkait dinas luar ini
        \App\Models\Notification::where('type', 'pending_trip')
            ->where('message', 'like', '%' . $userName . '%')
            ->delete();

        $businessTrip->delete();

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => get_class($businessTrip),
            'model_id' => $businessTrip->id,
            'description' => "Menghapus data dinas luar pegawai '{$userName}' ke {$businessTrip->destination}",
            'old_values' => $oldValues,
            'new_values' => null,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', "Data dinas luar {$userName} berhasil dihapus.");
    }

    /**
     * Form absen manual
     */
    public function manualAttendanceForm()
    {
        $users = User::where('role', 'user')->orderBy('name')->get();
        return view('staff.psdm.manual-attendance', compact('users'));
    }

    /**
     * Simpan absen manual
     */
    public function storeManualAttendance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'check_in_time' => 'required|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'manual_reason' => 'required|string|max:500',
        ]);

        $user = User::findOrFail($request->user_id);
        $date = \Carbon\Carbon::parse($request->date);
        $checkInTime = $date->copy()->setTimeFromTimeString($request->check_in_time . ':00');

        // Cek duplikat
        $existing = Attendance::where('user_id', $user->id)
            ->where('work_date', $date->toDateString())
            ->first();

        if ($existing) {
            return back()->withInput()->with('error', "Karyawan {$user->name} sudah punya record presensi pada tanggal tersebut.");
        }

        // Deteksi shift — untuk tipe umum tidak perlu shift
        $service = new \App\Services\AttendanceService();

        if ($user->isUmumAttendance()) {
            // Tipe Umum: tidak ada shift, tidak ada batas waktu masuk, tidak pernah late
            $minCheckOutTime = $checkInTime->copy()->addHours(8);
            $data = [
                'user_id'           => $user->id,
                'shift_id'          => null,
                'attendance_type'   => 'umum',
                'photo_path'        => 'manual',
                'check_in_time'     => $checkInTime,
                'work_date'         => $date->toDateString(),
                'min_check_out_time'=> $minCheckOutTime,
                'max_check_out_time'=> null,
                'latitude'          => 0,
                'longitude'         => 0,
                'status'            => 'present', // Umum tidak pernah late
                'manual_reason'     => $request->manual_reason,
                'created_by'        => auth()->id(),
            ];

            if ($request->filled('check_out_time')) {
                $checkOutTime = $date->copy()->setTimeFromTimeString($request->check_out_time . ':00');
                if ($checkOutTime->lte($checkInTime)) {
                    $checkOutTime->addDay();
                }
                $data['check_out_time'] = $checkOutTime;
                $data['check_out_photo_path'] = 'manual';
                $data['check_out_latitude'] = 0;
                $data['check_out_longitude'] = 0;
            }

            $attendance = Attendance::create($data);

            // Catat log ke ActivityLog
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => get_class($attendance),
                'model_id' => $attendance->id,
                'description' => "Menginput presensi manual (Umum) pegawai '{$user->name}' tanggal {$date->format('d M Y')}",
                'old_values' => null,
                'new_values' => [
                    'user_name' => $user->name,
                    'work_date' => $attendance->work_date,
                    'check_in_time' => $attendance->check_in_time,
                    'check_out_time' => $attendance->check_out_time ?? null,
                    'reason' => $attendance->manual_reason
                ],
                'ip_address' => request()->ip(),
            ]);

            return redirect()->route('staff.psdm.manual-attendance')
                ->with('success', "Absen manual (Umum) {$user->name} berhasil dibuat.");
        }

        $shift = $service->getApplicableShift($user, $checkInTime);

        $isLate = $service->isLate($shift, $checkInTime);
        $minCheckOutTime = $service->calculateMinCheckOutTime($checkInTime, $shift, $isLate);
        $maxCheckOutTime = $service->calculateMaxCheckOutTime($checkInTime, $shift);

        $data = [
            'user_id' => $user->id,
            'shift_id' => $shift->id,
            'attendance_type' => $user->isUmumAttendance() ? 'umum' : ($user->isNormalAttendance() ? 'normal' : 'shift'),
            'photo_path' => 'manual',
            'check_in_time' => $checkInTime,
            'work_date' => $date->toDateString(),
            'min_check_out_time' => $minCheckOutTime,
            'max_check_out_time' => $maxCheckOutTime,
            'latitude' => 0,
            'longitude' => 0,
            'status' => $isLate ? 'late' : 'present',
            'manual_reason' => $request->manual_reason,
            'created_by' => auth()->id(),
        ];

        // Jika jam pulang diisi
        if ($request->filled('check_out_time')) {
            $checkOutTime = $date->copy()->setTimeFromTimeString($request->check_out_time . ':00');
            // Handle midnight crossing
            if ($checkOutTime->lte($checkInTime)) {
                $checkOutTime->addDay();
            }
            $data['check_out_time'] = $checkOutTime;
            $data['check_out_photo_path'] = 'manual';
            $data['check_out_latitude'] = 0;
            $data['check_out_longitude'] = 0;
        }

        $attendance = Attendance::create($data);

        // Catat log ke ActivityLog
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => get_class($attendance),
            'model_id' => $attendance->id,
            'description' => "Menginput presensi manual pegawai '{$user->name}' tanggal {$date->format('d M Y')}",
            'old_values' => null,
            'new_values' => [
                'user_name' => $user->name,
                'work_date' => $attendance->work_date,
                'check_in_time' => $attendance->check_in_time,
                'check_out_time' => $attendance->check_out_time ?? null,
                'reason' => $attendance->manual_reason
            ],
            'ip_address' => request()->ip(),
        ]);

        $message = "Absen manual {$user->name} berhasil dibuat.";
        if ($isLate) {
            $message .= ' (Status: Terlambat)';
        }

        return redirect()->route('staff.psdm.manual-attendance')
            ->with('success', $message);
    }
}
