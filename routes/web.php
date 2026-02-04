<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
    Route::group(['prefix' => 'admin'], function () {
        Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.dashboard');
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
        
        // Announcements
        Route::get('/announcements', [App\Http\Controllers\StaffPsdmController::class, 'announcements'])->name('staff.psdm.announcements');
        Route::get('/announcements/create', [App\Http\Controllers\StaffPsdmController::class, 'createAnnouncement'])->name('staff.psdm.announcements.create');
        Route::post('/announcements', [App\Http\Controllers\StaffPsdmController::class, 'storeAnnouncement'])->name('staff.psdm.announcements.store');
        Route::patch('/announcements/{announcement}/toggle', [App\Http\Controllers\StaffPsdmController::class, 'toggleAnnouncement'])->name('staff.psdm.announcements.toggle');
        Route::delete('/announcements/{announcement}', [App\Http\Controllers\StaffPsdmController::class, 'deleteAnnouncement'])->name('staff.psdm.announcements.delete');
        
        // Monitor Absensi
        Route::get('/monitor', [App\Http\Controllers\StaffPsdmController::class, 'monitor'])->name('staff.psdm.monitor');
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
        
        // User Profile Keuangan
        Route::get('/users', [App\Http\Controllers\StaffKeuanganController::class, 'users'])->name('staff.keuangan.users');
        Route::get('/users/{user}/edit', [App\Http\Controllers\StaffKeuanganController::class, 'editUser'])->name('staff.keuangan.users.edit');
        Route::put('/users/{user}', [App\Http\Controllers\StaffKeuanganController::class, 'updateUser'])->name('staff.keuangan.users.update');
    });

    // User Routes (for employees)
    Route::get('/home', [App\Http\Controllers\UserController::class, 'home'])->name('user.home');
    Route::get('/rekap', [App\Http\Controllers\UserController::class, 'rekap'])->name('user.rekap');
    Route::get('/salary', [App\Http\Controllers\UserController::class, 'salary'])->name('user.salary');
    Route::get('/salary/{salary}/pdf', [App\Http\Controllers\UserController::class, 'salaryPdf'])->name('user.salary.pdf');
    Route::get('/cuti', [App\Http\Controllers\UserController::class, 'leaves'])->name('user.leaves');
    Route::get('/cuti/create', [App\Http\Controllers\UserController::class, 'createLeave'])->name('user.leaves.create');
    Route::post('/cuti', [App\Http\Controllers\UserController::class, 'storeLeave'])->name('user.leaves.store');
    Route::delete('/cuti/{leave}', [App\Http\Controllers\UserController::class, 'cancelLeave'])->name('user.leaves.cancel');
});

require __DIR__.'/auth.php';

