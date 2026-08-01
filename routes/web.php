<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DeductionTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffKeuanganController;
use App\Http\Controllers\StaffPsdmController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $role = auth()->user()->role ?? 'user';
    if ($role === 'admin') return redirect()->route('admin.dashboard');
    if ($role === 'staff_psdm') return redirect()->route('staff.psdm.dashboard');
    if ($role === 'staff_keuangan') return redirect()->route('staff.keuangan.dashboard');
    return app(UserController::class)->home(request());
})->middleware(['auth', 'verified'])->name('dashboard');

// Theme Toggle Route
Route::post('/theme/toggle', function () {
    session(['theme' => request('theme', 'light')]);
    return response()->json(['success' => true]);
})->name('theme.toggle');

// Notification Routes
Route::get('/notifications/{notification}/read', function (\App\Models\Notification $notification) {
    if ($notification->user_id === auth()->id()) {
        $notification->markAsRead();
    }
    return redirect($notification->url ?? '/dashboard');
})->middleware('auth')->name('notifications.read');

Route::post('/notifications/read-all', function () {
    \App\Models\Notification::where('user_id', auth()->id())
        ->unread()
        ->update(['read_at' => now()]);
    return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
})->middleware('auth')->name('notifications.read-all');



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.photo.delete');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Attendance Routes
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->middleware('throttle:5,1')->name('attendance.checkIn');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->middleware('throttle:5,1')->name('attendance.checkOut');

    // Admin Routes
    Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
        Route::post('/manual-checkin', [AdminController::class, 'manualCheckIn'])->name('admin.manualCheckIn');
        Route::put('/shift/{shift}', [AdminController::class, 'updateShift'])->name('admin.shift.update');
        
        // Staff CRUD (staff_psdm, staff_keuangan)
        Route::get('/staffs', [AdminController::class, 'staffs'])->name('admin.staffs');
        Route::get('/staffs/create', [AdminController::class, 'createStaff'])->name('admin.staffs.create');
        Route::post('/staffs', [AdminController::class, 'storeStaff'])->name('admin.staffs.store');
        Route::get('/staffs/{user}/edit', [AdminController::class, 'editStaff'])->name('admin.staffs.edit');
        Route::put('/staffs/{user}', [AdminController::class, 'updateStaff'])->name('admin.staffs.update');
        Route::delete('/staffs/{user}', [AdminController::class, 'deleteStaff'])->name('admin.staffs.delete');
        
        // Admin CRUD
        Route::get('/admins', [AdminController::class, 'admins'])->name('admin.admins');
        Route::get('/admins/create', [AdminController::class, 'createAdmin'])->name('admin.admins.create');
        Route::post('/admins', [AdminController::class, 'storeAdmin'])->name('admin.admins.store');
        Route::get('/admins/{user}/edit', [AdminController::class, 'editAdmin'])->name('admin.admins.edit');
        Route::put('/admins/{user}', [AdminController::class, 'updateAdmin'])->name('admin.admins.update');
        Route::delete('/admins/{user}', [AdminController::class, 'deleteAdmin'])->name('admin.admins.delete');
        
        
        // Activity Logs
        Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('admin.activity-logs');
    });

    // Staff PSDM Routes
    Route::prefix('staff/psdm')->middleware('staff.psdm')->group(function () {
        Route::get('/', [StaffPsdmController::class, 'index'])->name('staff.psdm.dashboard');
        
        // Users CRUD
        Route::get('/users', [StaffPsdmController::class, 'users'])->name('staff.psdm.users');
        Route::get('/users/create', [StaffPsdmController::class, 'createUser'])->name('staff.psdm.users.create');
        Route::post('/users', [StaffPsdmController::class, 'storeUser'])->name('staff.psdm.users.store');
        Route::get('/users/{user}/edit', [StaffPsdmController::class, 'editUser'])->name('staff.psdm.users.edit');
        Route::put('/users/{user}', [StaffPsdmController::class, 'updateUser'])->name('staff.psdm.users.update');
        Route::delete('/users/{user}', [StaffPsdmController::class, 'deleteUser'])->name('staff.psdm.users.delete');
        Route::post('/users/bulk-delete', [StaffPsdmController::class, 'bulkDeleteUsers'])->name('staff.psdm.users.bulk-delete');
        Route::get('/users/import', [StaffPsdmController::class, 'importUsersForm'])->name('staff.psdm.users.import');
        Route::post('/users/import', [StaffPsdmController::class, 'importUsers'])->name('staff.psdm.users.import.process');
        Route::get('/users/template', [StaffPsdmController::class, 'downloadTemplate'])->name('staff.psdm.users.template');
        
        // Announcements
        Route::get('/announcements', [StaffPsdmController::class, 'announcements'])->name('staff.psdm.announcements');
        Route::get('/announcements/create', [StaffPsdmController::class, 'createAnnouncement'])->name('staff.psdm.announcements.create');
        Route::post('/announcements', [StaffPsdmController::class, 'storeAnnouncement'])->name('staff.psdm.announcements.store');
        Route::get('/announcements/{announcement}/edit', [StaffPsdmController::class, 'editAnnouncement'])->name('staff.psdm.announcements.edit');
        Route::put('/announcements/{announcement}', [StaffPsdmController::class, 'updateAnnouncement'])->name('staff.psdm.announcements.update');
        Route::patch('/announcements/{announcement}/toggle', [StaffPsdmController::class, 'toggleAnnouncement'])->name('staff.psdm.announcements.toggle');
        Route::delete('/announcements/{announcement}', [StaffPsdmController::class, 'deleteAnnouncement'])->name('staff.psdm.announcements.delete');
        
        // Monitor Presensi
        Route::get('/monitor', [StaffPsdmController::class, 'monitor'])->name('staff.psdm.monitor');
        Route::delete('/monitor/{attendance}', [StaffPsdmController::class, 'deleteAttendance'])->name('staff.psdm.attendance.delete');
        Route::get('/export/attendance', [StaffPsdmController::class, 'exportAttendance'])->name('staff.psdm.export.attendance');
        
        // Master Data PSDM
        Route::get('/master-data', [StaffPsdmController::class, 'masterData'])->name('staff.psdm.master-data');
        Route::get('/master-data/{type}', [StaffPsdmController::class, 'showMasterDataType'])->name('staff.psdm.master-data.show');
        Route::post('/master-data/{type}/values', [StaffPsdmController::class, 'storeMasterDataValue'])->name('staff.psdm.master-data.values.store');
        Route::delete('/master-data/values/{value}', [StaffPsdmController::class, 'destroyMasterDataValue'])->name('staff.psdm.master-data.values.destroy');

        // Leave Management (Manajemen Cuti)
        Route::get('/leaves', [StaffPsdmController::class, 'leaves'])->name('staff.psdm.leaves');
        Route::patch('/leaves/{leave}/approve', [StaffPsdmController::class, 'approveLeave'])->name('staff.psdm.leaves.approve');
        Route::patch('/leaves/{leave}/reject', [StaffPsdmController::class, 'rejectLeave'])->name('staff.psdm.leaves.reject');
        Route::delete('/leaves/{leave}', [StaffPsdmController::class, 'deleteLeave'])->name('staff.psdm.leaves.delete');

        // Dinas Luar Management
        Route::get('/business-trips', [StaffPsdmController::class, 'businessTrips'])->name('staff.psdm.business-trips');
        Route::patch('/business-trips/{businessTrip}/approve', [StaffPsdmController::class, 'approveBusinessTrip'])->name('staff.psdm.business-trips.approve');
        Route::patch('/business-trips/{businessTrip}/reject', [StaffPsdmController::class, 'rejectBusinessTrip'])->name('staff.psdm.business-trips.reject');
        Route::delete('/business-trips/{businessTrip}', [StaffPsdmController::class, 'deleteBusinessTrip'])->name('staff.psdm.business-trips.delete');

        // Absen Manual
        Route::get('/manual-attendance', [StaffPsdmController::class, 'manualAttendanceForm'])->name('staff.psdm.manual-attendance');
        Route::post('/manual-attendance', [StaffPsdmController::class, 'storeManualAttendance'])->name('staff.psdm.manual-attendance.store');
    });

    // Staff Keuangan Routes
    Route::prefix('staff/keuangan')->middleware('staff.keuangan')->group(function () {
        Route::get('/', [StaffKeuanganController::class, 'index'])->name('staff.keuangan.dashboard');
        
        // Salaries
        Route::get('/salaries', [StaffKeuanganController::class, 'salaries'])->name('staff.keuangan.salaries');
        Route::get('/salaries/input', [StaffKeuanganController::class, 'inputForm'])->name('staff.keuangan.salaries.input');
        Route::post('/salaries/input', [StaffKeuanganController::class, 'storeManual'])->name('staff.keuangan.salaries.store.manual');
        Route::get('/salaries/import', [StaffKeuanganController::class, 'importForm'])->name('staff.keuangan.salaries.import.form');
        Route::post('/salaries/import', [StaffKeuanganController::class, 'import'])->name('staff.keuangan.salaries.import');
        Route::get('/salaries/template', [StaffKeuanganController::class, 'downloadTemplate'])->name('staff.keuangan.salaries.template');
        Route::get('/salaries/calculate', [StaffKeuanganController::class, 'calculateForm'])->name('staff.keuangan.calculate');
        Route::post('/salaries/calculate', [StaffKeuanganController::class, 'calculate'])->name('staff.keuangan.calculate.process');
        Route::post('/salaries', [StaffKeuanganController::class, 'storeSalary'])->name('staff.keuangan.salaries.store');
        Route::get('/salaries/{salary}', [StaffKeuanganController::class, 'showSalary'])->name('staff.keuangan.salaries.show');
        Route::patch('/salaries/{salary}/status', [StaffKeuanganController::class, 'updateStatus'])->name('staff.keuangan.salaries.status');
        Route::delete('/salaries/{salary}', [StaffKeuanganController::class, 'deleteSalary'])->name('staff.keuangan.salaries.delete');
        Route::post('/salaries/bulk-delete', [StaffKeuanganController::class, 'bulkDeleteSalaries'])->name('staff.keuangan.salaries.bulk-delete');

        // Tanda tangan
        Route::get('/signature', [StaffKeuanganController::class, 'signaturePage'])->name('staff.keuangan.signature');
        Route::post('/salaries/{salary}/sign', [StaffKeuanganController::class, 'signSalary'])->name('staff.keuangan.salaries.sign');
        Route::post('/salaries/bulk-sign', [StaffKeuanganController::class, 'bulkSignSalaries'])->name('staff.keuangan.salaries.bulk-sign');
        Route::post('/signature/upload', [StaffKeuanganController::class, 'uploadSignature'])->name('staff.keuangan.signature.upload');
        
        // Single user salary input
        Route::get('/salaries/input/{user}', [StaffKeuanganController::class, 'inputFormSingle'])->name('staff.keuangan.salaries.input.single');
        
        // Edit salary
        Route::get('/salaries/{salary}/edit', [StaffKeuanganController::class, 'editSalary'])->name('staff.keuangan.salaries.edit');
        Route::put('/salaries/{salary}', [StaffKeuanganController::class, 'updateSalary'])->name('staff.keuangan.salaries.update');
        
        // Bulk input
        Route::get('/salaries/bulk', [StaffKeuanganController::class, 'bulkInputForm'])->name('staff.keuangan.salaries.bulk');
        Route::post('/salaries/bulk', [StaffKeuanganController::class, 'storeBulk'])->name('staff.keuangan.salaries.store.bulk');
        
        // User Profile Keuangan
        Route::get('/users', [StaffKeuanganController::class, 'users'])->name('staff.keuangan.users');
        Route::get('/users/{user}', [StaffKeuanganController::class, 'showUser'])->name('staff.keuangan.users.show');
        
        // Export Salary
        Route::get('/export/salaries', [StaffKeuanganController::class, 'exportSalaries'])->name('staff.keuangan.export.salaries');
        

        // Deduction Types Management
        Route::get('/deductions', [DeductionTypeController::class, 'index'])->name('staff.keuangan.deductions.index');
        Route::post('/deductions', [DeductionTypeController::class, 'store'])->name('staff.keuangan.deductions.store');
        Route::put('/deductions/{deductionType}', [DeductionTypeController::class, 'update'])->name('staff.keuangan.deductions.update');
        Route::delete('/deductions/{deductionType}', [DeductionTypeController::class, 'destroy'])->name('staff.keuangan.deductions.destroy');
    });

    // User Routes (for employees only — protected by user.only middleware)
    Route::middleware('user.only')->group(function () {
        Route::get('/home', [UserController::class, 'home'])->name('user.home');
        Route::get('/rekap', [UserController::class, 'rekap'])->name('user.rekap');
        Route::get('/rekap/export', [UserController::class, 'exportRekap'])->name('user.rekap.export');
        Route::get('/salary', [UserController::class, 'salary'])->name('user.salary');
        Route::get('/salary/{salary}/pdf', [UserController::class, 'salaryPdf'])->name('user.salary.pdf');
        Route::get('/cuti', [UserController::class, 'leaves'])->name('user.leaves');
        Route::get('/cuti/create', [UserController::class, 'createLeave'])->name('user.leaves.create');
        Route::post('/cuti', [UserController::class, 'storeLeave'])->name('user.leaves.store');
        Route::delete('/cuti/{leave}', [UserController::class, 'cancelLeave'])->name('user.leaves.cancel');

        // Dinas Luar
        Route::get('/dinas-luar', [UserController::class, 'businessTrips'])->name('user.business-trips');
        Route::get('/dinas-luar/create', [UserController::class, 'createBusinessTrip'])->name('user.business-trips.create');
        Route::post('/dinas-luar', [UserController::class, 'storeBusinessTrip'])->name('user.business-trips.store');
        Route::delete('/dinas-luar/{businessTrip}', [UserController::class, 'cancelBusinessTrip'])->name('user.business-trips.cancel');
    });
});

require __DIR__.'/auth.php';            
