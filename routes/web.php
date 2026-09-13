<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KonfigurasiSekolah\KalenderAkademikController;
use App\Http\Controllers\KonfigurasiSekolah\TahunAjaranController;
use App\Http\Controllers\Master\SchoolController;
use App\Http\Controllers\System\BackupRestoreController;
use App\Http\Controllers\System\MenuAccessController;
use App\Http\Controllers\System\MenuController;
use App\Http\Controllers\System\ModuleController;
use App\Http\Controllers\System\SubMenuController;
use App\Http\Controllers\System\UnderDevelopmentController;
use App\Http\Controllers\Wilayah\KabupatenController;
use App\Http\Controllers\Wilayah\KecamatanController;
use App\Http\Controllers\Wilayah\KelurahanController;
use App\Http\Controllers\Wilayah\ProvinsiController;
use App\Http\Controllers\Wilayah\WilayahDropdownController;
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
        Route::get('/user', [MenuAccessController::class, 'index'])->name('user');
        Route::get('/user/user/{user}', [MenuAccessController::class, 'editUser'])->name('user.user');
        Route::put('/user/user/{user}', [MenuAccessController::class, 'updateUser'])->name('user.user.update');
        Route::post('/user/user/{user}/apply-template', [MenuAccessController::class, 'applyTemplateToUser'])->name('user.user.apply-template');
        Route::get('/user/template/{roleKey}', [MenuAccessController::class, 'editTemplate'])->name('user.template');
        Route::put('/user/template/{roleKey}', [MenuAccessController::class, 'updateTemplate'])->name('user.template.update');
    });

    // Master Data Administrasi
    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('data-sekolah', SchoolController::class)->except(['show', 'create']);
    });

    // Konfigurasi Sekolah
    Route::prefix('konfigurasi-sekolah')->name('konfigurasi-sekolah.')->group(function () {
        // Tahun Ajaran & Semester
        Route::get('/tahun-ajaran', [TahunAjaranController::class, 'index'])->name('tahun-ajaran');
        Route::post('/tahun-ajaran', [TahunAjaranController::class, 'store'])->name('tahun-ajaran.store');
        Route::put('/tahun-ajaran/{tahunAjaran}', [TahunAjaranController::class, 'update'])->name('tahun-ajaran.update');
        Route::delete('/tahun-ajaran/{tahunAjaran}', [TahunAjaranController::class, 'destroy'])->name('tahun-ajaran.destroy');

        Route::post('/tahun-ajaran/{tahunAjaran}/semester', [TahunAjaranController::class, 'storeSemester'])->name('tahun-ajaran.semester.store');
        Route::put('/tahun-ajaran/{tahunAjaran}/semester/{semester}', [TahunAjaranController::class, 'updateSemester'])->name('tahun-ajaran.semester.update');
        Route::delete('/tahun-ajaran/{tahunAjaran}/semester/{semester}', [TahunAjaranController::class, 'destroySemester'])->name('tahun-ajaran.semester.destroy');
        Route::post('/tahun-ajaran/{tahunAjaran}/semester/{semester}/activate', [TahunAjaranController::class, 'activateSemester'])->name('tahun-ajaran.semester.activate');

        // Kalender Akademik
        Route::get('/kalender-akademik/events', [KalenderAkademikController::class, 'events'])->name('kalender-akademik.events');
        Route::resource('kalender-akademik', KalenderAkademikController::class)->names([
            'index' => 'kalender-akademik',
        ])->except(['show', 'create']);
        Route::get('/kalender-akademik/semua', [KalenderAkademikController::class, 'index'])->name('kalender-akademik.index');
    });

    // Master Wilayah Administratif
    Route::prefix('wilayah')->name('wilayah.')->group(function () {
        Route::resource('provinsi', ProvinsiController::class)->names([
            'index' => 'provinsi',
        ])->except(['show', 'create']);
        Route::get('/provinsi/semua', [ProvinsiController::class, 'index'])->name('provinsi.index');

        Route::resource('kabupaten', KabupatenController::class)->names([
            'index' => 'kabupaten',
        ])->except(['show', 'create']);
        Route::get('/kabupaten/semua', [KabupatenController::class, 'index'])->name('kabupaten.index');

        Route::resource('kecamatan', KecamatanController::class)->names([
            'index' => 'kecamatan',
        ])->except(['show', 'create']);
        Route::get('/kecamatan/semua', [KecamatanController::class, 'index'])->name('kecamatan.index');

        Route::resource('kelurahan', KelurahanController::class)->names([
            'index' => 'kelurahan',
        ])->except(['show', 'create']);
        Route::get('/kelurahan/semua', [KelurahanController::class, 'index'])->name('kelurahan.index');

        // Endpoint Data Dropdown Wilayah (JSON)
        Route::prefix('dropdown')->name('dropdown.')->group(function () {
            Route::get('provinsi', [WilayahDropdownController::class, 'provinsi'])->name('provinsi');
            Route::get('kabupaten', [WilayahDropdownController::class, 'kabupaten'])->name('kabupaten');
            Route::get('kecamatan', [WilayahDropdownController::class, 'kecamatan'])->name('kecamatan');
            Route::get('kelurahan', [WilayahDropdownController::class, 'kelurahan'])->name('kelurahan');
        });
    });

    // Cadangan & Pemulihan Basis Data (Backup & Restore)
    Route::get('/backup-restore', [BackupRestoreController::class, 'index'])->name('backup-restore');
    Route::post('/backup-restore', [BackupRestoreController::class, 'store'])->name('backup-restore.create');
    Route::get('/backup-restore/download/{filename}', [BackupRestoreController::class, 'download'])->name('backup-restore.download');
    Route::post('/backup-restore/restore', [BackupRestoreController::class, 'restore'])->name('backup-restore.restore');
    Route::delete('/backup-restore/all', [BackupRestoreController::class, 'destroyAll'])->name('backup-restore.destroy-all');
    Route::delete('/backup-restore/{filename}', [BackupRestoreController::class, 'destroy'])->name('backup-restore.destroy');
});
