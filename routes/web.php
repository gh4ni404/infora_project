<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\System\BackupRestoreController;
use App\Http\Controllers\System\MenuAccessController;
use App\Http\Controllers\System\MenuController;
use App\Http\Controllers\System\ModuleController;
use App\Http\Controllers\System\SubMenuController;
use App\Http\Controllers\System\UnderDevelopmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::middleware(['auth', 'super_admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Pengaturan Sistem (System Governance)
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/under-development', [UnderDevelopmentController::class, 'show'])->name('under-development');
        Route::resource('modules', ModuleController::class)->except(['show']);
        Route::resource('menus', MenuController::class)->except(['show']);
        Route::resource('sub-menus', SubMenuController::class)->except(['show']);
    });

    // Tata Kelola Menu Akses & Template Peran
    Route::prefix('sistem')->name('sistem.')->group(function () {
        Route::get('/menu-akses', [MenuAccessController::class, 'index'])->name('menu-akses');
        Route::get('/menu-akses/user/{user}', [MenuAccessController::class, 'editUser'])->name('menu-akses.user');
        Route::put('/menu-akses/user/{user}', [MenuAccessController::class, 'updateUser'])->name('menu-akses.user.update');
        Route::post('/menu-akses/user/{user}/apply-template', [MenuAccessController::class, 'applyTemplateToUser'])->name('menu-akses.user.apply-template');
        Route::get('/menu-akses/template/{roleKey}', [MenuAccessController::class, 'editTemplate'])->name('menu-akses.template');
        Route::put('/menu-akses/template/{roleKey}', [MenuAccessController::class, 'updateTemplate'])->name('menu-akses.template.update');
    });

    // Cadangan & Pemulihan Basis Data (Backup & Restore)
    Route::get('/backup-restore', [BackupRestoreController::class, 'index'])->name('backup-restore');
    Route::post('/backup-restore', [BackupRestoreController::class, 'store'])->name('backup-restore.create');
    Route::get('/backup-restore/download/{filename}', [BackupRestoreController::class, 'download'])->name('backup-restore.download');
    Route::post('/backup-restore/restore', [BackupRestoreController::class, 'restore'])->name('backup-restore.restore');
    Route::delete('/backup-restore/{filename}', [BackupRestoreController::class, 'destroy'])->name('backup-restore.destroy');
});
