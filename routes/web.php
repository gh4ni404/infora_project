<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
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
});
