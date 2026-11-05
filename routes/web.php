<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\QrController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Anggota\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'aktif'])->group(function () {
    Route::get('/ganti-password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/ganti-password', [PasswordController::class, 'update'])->name('password.update');
});

Route::middleware(['auth', 'aktif', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/', 'admin.dashboard')->name('dashboard');

        Route::get('/anggota', [MemberController::class, 'index'])->name('members.index');
        Route::get('/anggota/tambah', [MemberController::class, 'create'])->name('members.create');
        Route::post('/anggota', [MemberController::class, 'store'])->name('members.store');
        Route::get('/anggota/{member}/ubah', [MemberController::class, 'edit'])->name('members.edit');
        Route::put('/anggota/{member}', [MemberController::class, 'update'])->name('members.update');
        Route::patch('/anggota/{member}/status', [MemberController::class, 'toggleStatus'])->name('members.status');
        Route::patch('/anggota/{member}/password', [MemberController::class, 'resetPassword'])->name('members.password');

        Route::get('/riwayat-aktivitas', [ActivityLogController::class, 'index'])->name('logs.index');

        Route::get('/pengaturan-posko', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/pengaturan-posko', [SettingController::class, 'update'])->name('settings.update');

        Route::get('/qr', [QrController::class, 'show'])->name('qr.show');
        Route::post('/qr', [QrController::class, 'store'])->name('qr.store');
        Route::delete('/qr', [QrController::class, 'revoke'])->name('qr.revoke');
    });

Route::middleware(['auth', 'aktif', 'role:anggota'])
    ->prefix('anggota')
    ->name('anggota.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    });
