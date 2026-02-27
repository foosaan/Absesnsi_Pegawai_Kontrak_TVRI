<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $role = auth()->user()->role ?? 'user';
    if ($role === 'admin') return redirect()->route('admin.dashboard');
    if ($role === 'staff_psdm') return redirect()->route('staff.psdm.dashboard');
    if ($role === 'staff_keuangan') return redirect()->route('staff.keuangan.dashboard');
    return app(App\Http\Controllers\UserController::class)->home(request());
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
    Route::get('/attendance', [App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [App\Http\Controllers\AttendanceController::class, 'checkIn'])->name('attendance.checkIn');
    Route::post('/attendance/check-out', [App\Http\Controllers\AttendanceController::class, 'checkOut'])->name('attendance.checkOut');

    // Admin Routes
    Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
        Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/settings', [App\Http\Controllers\AdminController::class, 'settings'])->name('admin.settings');
        Route::post('/settings', [App\Http\Controllers\AdminController::class, 'updateSettings'])->name('admin.settings.update');
        Route::post('/manual-checkin', [App\Http\Controllers\AdminController::class, 'manualCheckIn'])->name('admin.manualCheckIn');
        Route::put('/shift/{shift}', [App\Http\Controllers\AdminController::class, 'updateShift'])->name('admin.shift.update');
        
        // Staff CRUD (staff_psdm, staff_keuangan)
        Route::get('/staffs', [App\Http\Controllers\AdminController::class, 'staffs'])->name('admin.staffs');
        Route::get('/staffs/create', [App\Http\Controllers\AdminController::class, 'createStaff'])->name('admin.staffs.create');
        Route::post('/staffs', [App\Http\Controllers\AdminController::class, 'storeStaff'])->name('admin.staffs.store');
        Route::get('/staffs/{user}/edit', [App\Http\Controllers\AdminController::class, 'editStaff'])->name('admin.staffs.edit');
        Route::put('/staffs/{user}', [App\Http\Controllers\AdminController::class, 'updateStaff'])->name('admin.staffs.update');
        Route::delete('/staffs/{user}', [App\Http\Controllers\AdminController::class, 'deleteStaff'])->name('admin.staffs.delete');
        
        // Admin CRUD
        Route::get('/admins', [App\Http\Controllers\AdminController::class, 'admins'])->name('admin.admins');
        Route::get('/admins/create', [App\Http\Controllers\AdminController::class, 'createAdmin'])->name('admin.admins.create');
        Route::post('/admins', [App\Http\Controllers\AdminController::class, 'storeAdmin'])->name('admin.admins.store');
        Route::get('/admins/{user}/edit', [App\Http\Controllers\AdminController::class, 'editAdmin'])->name('admin.admins.edit');
        Route::put('/admins/{user}', [App\Http\Controllers\AdminController::class, 'updateAdmin'])->name('admin.admins.update');
        Route::delete('/admins/{user}', [App\Http\Controllers\AdminController::class, 'deleteAdmin'])->name('admin.admins.delete');
        
        // Export
        Route::get('/export/attendance', [App\Http\Controllers\AdminController::class, 'exportAttendance'])->name('admin.export.attendance');
        
        // Activity Logs
        Route::get('/activity-logs', [App\Http\Controllers\AdminController::class, 'activityLogs'])->name('admin.activity-logs');
        
        
        // Monitor Absensi (read-only)
        Route::get('/monitor', [App\Http\Controllers\AdminController::class, 'monitor'])->name('admin.monitor');
        
        // Lihat Cuti (read-only)
        Route::get('/leaves', [App\Http\Controllers\AdminController::class, 'leaves'])->name('admin.leaves');
    });

    // Staff PSDM Routes
    Route::prefix('staff/psdm')->middleware('staff.psdm')->group(function () {
        Route::get('/', [App\Http\Controllers\StaffPsdmController::class, 'index'])->name('staff.psdm.dashboard');
        
        // Users CRUD
        Route::get('/users', [App\Http\Controllers\StaffPsdmController::class, 'users'])->name('staff.psdm.users');
        Route::get('/users/create', [App\Http\Controllers\StaffPsdmController::class, 'createUser'])->name('staff.psdm.users.create');
        Route::post('/users', [App\Http\Controllers\StaffPsdmController::class, 'storeUser'])->name('staff.psdm.users.store');
        Route::get('/users/{user}/edit', [App\Http\Controllers\StaffPsdmController::class, 'editUser'])->name('staff.psdm.users.edit');
        Route::put('/users/{user}', [App\Http\Controllers\StaffPsdmController::class, 'updateUser'])->name('staff.psdm.users.update');
        Route::delete('/users/{user}', [App\Http\Controllers\StaffPsdmController::class, 'deleteUser'])->name('staff.psdm.users.delete');
        Route::post('/users/bulk-delete', [App\Http\Controllers\StaffPsdmController::class, 'bulkDeleteUsers'])->name('staff.psdm.users.bulk-delete');
        Route::get('/users/import', [App\Http\Controllers\StaffPsdmController::class, 'importUsersForm'])->name('staff.psdm.users.import');
        Route::post('/users/import', [App\Http\Controllers\StaffPsdmController::class, 'importUsers'])->name('staff.psdm.users.import.process');
        
        // Announcements
        Route::get('/announcements', [App\Http\Controllers\StaffPsdmController::class, 'announcements'])->name('staff.psdm.announcements');
        Route::get('/announcements/create', [App\Http\Controllers\StaffPsdmController::class, 'createAnnouncement'])->name('staff.psdm.announcements.create');
        Route::post('/announcements', [App\Http\Controllers\StaffPsdmController::class, 'storeAnnouncement'])->name('staff.psdm.announcements.store');
        Route::get('/announcements/{announcement}/edit', [App\Http\Controllers\StaffPsdmController::class, 'editAnnouncement'])->name('staff.psdm.announcements.edit');
        Route::put('/announcements/{announcement}', [App\Http\Controllers\StaffPsdmController::class, 'updateAnnouncement'])->name('staff.psdm.announcements.update');
        Route::patch('/announcements/{announcement}/toggle', [App\Http\Controllers\StaffPsdmController::class, 'toggleAnnouncement'])->name('staff.psdm.announcements.toggle');
        Route::delete('/announcements/{announcement}', [App\Http\Controllers\StaffPsdmController::class, 'deleteAnnouncement'])->name('staff.psdm.announcements.delete');
        
        // Monitor Absensi
        Route::get('/monitor', [App\Http\Controllers\StaffPsdmController::class, 'monitor'])->name('staff.psdm.monitor');
        Route::delete('/monitor/{attendance}', [App\Http\Controllers\StaffPsdmController::class, 'deleteAttendance'])->name('staff.psdm.attendance.delete');
        Route::get('/export/attendance', [App\Http\Controllers\StaffPsdmController::class, 'exportAttendance'])->name('staff.psdm.export.attendance');
        
        // Master Data PSDM
        Route::get('/master-data', [App\Http\Controllers\StaffPsdmController::class, 'masterData'])->name('staff.psdm.master-data');
        Route::post('/master-data', [App\Http\Controllers\StaffPsdmController::class, 'storeMasterDataType'])->name('staff.psdm.master-data.store');
        Route::delete('/master-data/{type}', [App\Http\Controllers\StaffPsdmController::class, 'destroyMasterDataType'])->name('staff.psdm.master-data.destroy');
        Route::get('/master-data/{type}', [App\Http\Controllers\StaffPsdmController::class, 'showMasterDataType'])->name('staff.psdm.master-data.show');
        Route::put('/master-data/{type}', [App\Http\Controllers\StaffPsdmController::class, 'updateMasterDataType'])->name('staff.psdm.master-data.update');
        Route::post('/master-data/{type}/values', [App\Http\Controllers\StaffPsdmController::class, 'storeMasterDataValue'])->name('staff.psdm.master-data.values.store');
        Route::delete('/master-data/values/{value}', [App\Http\Controllers\StaffPsdmController::class, 'destroyMasterDataValue'])->name('staff.psdm.master-data.values.destroy');

        // Leave Management (Manajemen Cuti)
        Route::get('/leaves', [App\Http\Controllers\StaffPsdmController::class, 'leaves'])->name('staff.psdm.leaves');
        Route::patch('/leaves/{leave}/approve', [App\Http\Controllers\StaffPsdmController::class, 'approveLeave'])->name('staff.psdm.leaves.approve');
        Route::patch('/leaves/{leave}/reject', [App\Http\Controllers\StaffPsdmController::class, 'rejectLeave'])->name('staff.psdm.leaves.reject');

        // Dinas Luar Management
        Route::get('/business-trips', [App\Http\Controllers\StaffPsdmController::class, 'businessTrips'])->name('staff.psdm.business-trips');
        Route::patch('/business-trips/{businessTrip}/approve', [App\Http\Controllers\StaffPsdmController::class, 'approveBusinessTrip'])->name('staff.psdm.business-trips.approve');
        Route::patch('/business-trips/{businessTrip}/reject', [App\Http\Controllers\StaffPsdmController::class, 'rejectBusinessTrip'])->name('staff.psdm.business-trips.reject');
    });

    // Staff Keuangan Routes
    Route::prefix('staff/keuangan')->middleware('staff.keuangan')->group(function () {
        Route::get('/', [App\Http\Controllers\StaffKeuanganController::class, 'index'])->name('staff.keuangan.dashboard');
        
        // Salaries
        Route::get('/salaries', [App\Http\Controllers\StaffKeuanganController::class, 'salaries'])->name('staff.keuangan.salaries');
        Route::get('/salaries/input', [App\Http\Controllers\StaffKeuanganController::class, 'inputForm'])->name('staff.keuangan.salaries.input');
        Route::post('/salaries/input', [App\Http\Controllers\StaffKeuanganController::class, 'storeManual'])->name('staff.keuangan.salaries.store.manual');
        Route::get('/salaries/import', [App\Http\Controllers\StaffKeuanganController::class, 'importForm'])->name('staff.keuangan.salaries.import.form');
        Route::post('/salaries/import', [App\Http\Controllers\StaffKeuanganController::class, 'import'])->name('staff.keuangan.salaries.import');
        Route::get('/salaries/template', [App\Http\Controllers\StaffKeuanganController::class, 'downloadTemplate'])->name('staff.keuangan.salaries.template');
        Route::get('/salaries/calculate', [App\Http\Controllers\StaffKeuanganController::class, 'calculateForm'])->name('staff.keuangan.calculate');
        Route::post('/salaries/calculate', [App\Http\Controllers\StaffKeuanganController::class, 'calculate'])->name('staff.keuangan.calculate.process');
        Route::post('/salaries', [App\Http\Controllers\StaffKeuanganController::class, 'storeSalary'])->name('staff.keuangan.salaries.store');
        Route::get('/salaries/{salary}', [App\Http\Controllers\StaffKeuanganController::class, 'showSalary'])->name('staff.keuangan.salaries.show');
        Route::patch('/salaries/{salary}/status', [App\Http\Controllers\StaffKeuanganController::class, 'updateStatus'])->name('staff.keuangan.salaries.status');
        Route::delete('/salaries/{salary}', [App\Http\Controllers\StaffKeuanganController::class, 'deleteSalary'])->name('staff.keuangan.salaries.delete');
        Route::post('/salaries/bulk-delete', [App\Http\Controllers\StaffKeuanganController::class, 'bulkDeleteSalaries'])->name('staff.keuangan.salaries.bulk-delete');

        // Tanda tangan
        Route::post('/salaries/{salary}/sign', [App\Http\Controllers\StaffKeuanganController::class, 'signSalary'])->name('staff.keuangan.salaries.sign');
        Route::post('/salaries/bulk-sign', [App\Http\Controllers\StaffKeuanganController::class, 'bulkSignSalaries'])->name('staff.keuangan.salaries.bulk-sign');
        Route::post('/signature/upload', [App\Http\Controllers\StaffKeuanganController::class, 'uploadSignature'])->name('staff.keuangan.signature.upload');
        
        // Single user salary input
        Route::get('/salaries/input/{user}', [App\Http\Controllers\StaffKeuanganController::class, 'inputFormSingle'])->name('staff.keuangan.salaries.input.single');
        
        // Edit salary
        Route::get('/salaries/{salary}/edit', [App\Http\Controllers\StaffKeuanganController::class, 'editSalary'])->name('staff.keuangan.salaries.edit');
        Route::put('/salaries/{salary}', [App\Http\Controllers\StaffKeuanganController::class, 'updateSalary'])->name('staff.keuangan.salaries.update');
        
        // Bulk input
        Route::get('/salaries/bulk', [App\Http\Controllers\StaffKeuanganController::class, 'bulkInputForm'])->name('staff.keuangan.salaries.bulk');
        Route::post('/salaries/bulk', [App\Http\Controllers\StaffKeuanganController::class, 'storeBulk'])->name('staff.keuangan.salaries.store.bulk');
        
        // User Profile Keuangan
        Route::get('/users', [App\Http\Controllers\StaffKeuanganController::class, 'users'])->name('staff.keuangan.users');
        Route::get('/users/{user}', [App\Http\Controllers\StaffKeuanganController::class, 'showUser'])->name('staff.keuangan.users.show');
        
        // Export Salary
        Route::get('/export/salaries', [App\Http\Controllers\StaffKeuanganController::class, 'exportSalaries'])->name('staff.keuangan.export.salaries');
        

        // Deduction Types Management
        Route::get('/deductions', [App\Http\Controllers\DeductionTypeController::class, 'index'])->name('staff.keuangan.deductions.index');
        Route::post('/deductions', [App\Http\Controllers\DeductionTypeController::class, 'store'])->name('staff.keuangan.deductions.store');
        Route::put('/deductions/{deductionType}', [App\Http\Controllers\DeductionTypeController::class, 'update'])->name('staff.keuangan.deductions.update');
        Route::delete('/deductions/{deductionType}', [App\Http\Controllers\DeductionTypeController::class, 'destroy'])->name('staff.keuangan.deductions.destroy');
    });

    // User Routes (for employees)
    Route::get('/home', [App\Http\Controllers\UserController::class, 'home'])->name('user.home');
    Route::get('/rekap', [App\Http\Controllers\UserController::class, 'rekap'])->name('user.rekap');
    Route::get('/rekap/export', [App\Http\Controllers\UserController::class, 'exportRekap'])->name('user.rekap.export');
    Route::get('/salary', [App\Http\Controllers\UserController::class, 'salary'])->name('user.salary');
    Route::get('/salary/{salary}/pdf', [App\Http\Controllers\UserController::class, 'salaryPdf'])->name('user.salary.pdf');
    Route::get('/cuti', [App\Http\Controllers\UserController::class, 'leaves'])->name('user.leaves');
    Route::get('/cuti/create', [App\Http\Controllers\UserController::class, 'createLeave'])->name('user.leaves.create');
    Route::post('/cuti', [App\Http\Controllers\UserController::class, 'storeLeave'])->name('user.leaves.store');
    Route::delete('/cuti/{leave}', [App\Http\Controllers\UserController::class, 'cancelLeave'])->name('user.leaves.cancel');

    // Dinas Luar
    Route::get('/dinas-luar', [App\Http\Controllers\UserController::class, 'businessTrips'])->name('user.business-trips');
    Route::get('/dinas-luar/create', [App\Http\Controllers\UserController::class, 'createBusinessTrip'])->name('user.business-trips.create');
    Route::post('/dinas-luar', [App\Http\Controllers\UserController::class, 'storeBusinessTrip'])->name('user.business-trips.store');
    Route::delete('/dinas-luar/{businessTrip}', [App\Http\Controllers\UserController::class, 'cancelBusinessTrip'])->name('user.business-trips.cancel');
});

require __DIR__.'/auth.php';

