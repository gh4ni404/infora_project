<?php

use App\Http\Controllers\AbsensiKbm\AbsenMuridController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KonfigurasiSekolah\KalenderAkademikController;
use App\Http\Controllers\KonfigurasiSekolah\PendaftaranMukaController;
use App\Http\Controllers\KonfigurasiSekolah\TahunAjaranController;
use App\Http\Controllers\Master\GtkController;
use App\Http\Controllers\Master\JurusanController;
use App\Http\Controllers\Master\MapelController;
use App\Http\Controllers\Master\MuridController;
use App\Http\Controllers\Master\SchoolController;
use App\Http\Controllers\Master\TingkatanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Rombel\KelasController;
use App\Http\Controllers\System\BackupRestoreController;
use App\Http\Controllers\System\MenuAccessController;
use App\Http\Controllers\System\MenuController;
use App\Http\Controllers\System\ModuleController;
use App\Http\Controllers\System\ReferensiController;
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
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Pengaturan Profil Pengguna Mandiri
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Pengaturan Sistem (System Governance)
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/under-development', [UnderDevelopmentController::class, 'show'])->name('under-development');
        Route::get('/under-development/status', [UnderDevelopmentController::class, 'status'])->name('under-development.status');
        Route::patch('/under-development/checklist', [UnderDevelopmentController::class, 'updateChecklist'])->name('under-development.checklist');
        Route::post('modules/sync-order', [ModuleController::class, 'syncOrder'])->name('modules.sync-order');
        Route::resource('modules', ModuleController::class)->except(['show']);
        Route::resource('menus', MenuController::class)->except(['show']);
        Route::resource('sub-menus', SubMenuController::class)->except(['show']);
        Route::get('referensi', [ReferensiController::class, 'index'])->name('referensi.index');
        Route::post('referensi/kategori', [ReferensiController::class, 'storeKategori'])->name('referensi.kategori.store');
        Route::put('referensi/kategori/{referensi}', [ReferensiController::class, 'updateKategori'])->name('referensi.kategori.update');
        Route::delete('referensi/kategori/{referensi}', [ReferensiController::class, 'destroyKategori'])->name('referensi.kategori.destroy');
        Route::post('referensi/item', [ReferensiController::class, 'storeItem'])->name('referensi.item.store');
        Route::put('referensi/item/{referensi}', [ReferensiController::class, 'updateItem'])->name('referensi.item.update');
        Route::delete('referensi/item/{referensi}', [ReferensiController::class, 'destroyItem'])->name('referensi.item.destroy');
        Route::patch('referensi/{referensi}/toggle-status', [ReferensiController::class, 'toggleStatus'])->name('referensi.toggle-status');
    });

    // Tata Kelola Menu Akses dan Template Peran
    Route::prefix('sistem')->name('sistem.')->group(function () {
        Route::get('/user', [MenuAccessController::class, 'index'])->name('user.index');
        Route::get('/user/user/{user}', [MenuAccessController::class, 'editUser'])->name('user.user');
        Route::put('/user/user/{user}', [MenuAccessController::class, 'updateUser'])->name('user.user.update');
        Route::post('/user/user/{user}/apply-template', [MenuAccessController::class, 'applyTemplateToUser'])->name('user.user.apply-template');
        Route::patch('/user/user/{user}/activate', [MenuAccessController::class, 'activate'])->name('user.activate');
        Route::patch('/user/user/{user}/toggle-status', [MenuAccessController::class, 'toggleStatus'])->name('user.toggle-status');
        Route::delete('/user/user/{user}', [MenuAccessController::class, 'destroy'])->name('user.destroy');
        Route::get('/user/template/{roleKey}', [MenuAccessController::class, 'editTemplate'])->name('user.template');
        Route::put('/user/template/{roleKey}', [MenuAccessController::class, 'updateTemplate'])->name('user.template.update');
    });

    // Master Data Administrasi
    Route::prefix('master')->name('master.')->group(function () {
        Route::post('data-sekolah/{dataSekolah}/sync-admin', [SchoolController::class, 'syncSingleAdmin'])->name('data-sekolah.sync-single-admin');
        Route::resource('data-sekolah', SchoolController::class)->only(['index', 'edit', 'update', 'destroy']);
        Route::resource('data-tingkatan', TingkatanController::class)->parameters([
            'data-tingkatan' => 'tingkatan',
        ])->except(['show', 'create']);
        Route::post('data-tingkatan/{tingkatan}/toggle-status', [TingkatanController::class, 'toggleStatus'])->name('data-tingkatan.toggle-status');
        Route::resource('data-jurusan', JurusanController::class)->parameters([
            'data-jurusan' => 'jurusan',
        ])->except(['show', 'create']);

        Route::post('data-jurusan/{jurusan}/toggle-status', [JurusanController::class, 'toggleStatus'])->name('data-jurusan.toggle-status');
        Route::resource('data-mapel', MapelController::class)->parameters([
            'data-mapel' => 'mapel',
        ])->except(['show', 'create']);
        Route::post('data-mapel/{mapel}/toggle-status', [MapelController::class, 'toggleStatus'])->name('data-mapel.toggle-status');
        Route::get('data-gtk/template', [GtkController::class, 'template'])->name('data-gtk.template');
        Route::get('data-gtk/export', [GtkController::class, 'export'])->name('data-gtk.export');
        Route::post('data-gtk/import', [GtkController::class, 'import'])->name('data-gtk.import');
        Route::resource('data-gtk', GtkController::class)->parameters([
            'data-gtk' => 'gtk',
        ])->except(['create']);
        Route::post('data-gtk/{gtk}/toggle-status', [GtkController::class, 'toggleStatus'])->name('data-gtk.toggle-status');
        Route::post('data-gtk/{gtk}/reset-password', [GtkController::class, 'resetPassword'])->name('data-gtk.reset-password');
        Route::get('data-murid/template', [MuridController::class, 'template'])->name('data-murid.template');
        Route::get('data-murid/export', [MuridController::class, 'export'])->name('data-murid.export');
        Route::post('data-murid/import', [MuridController::class, 'import'])->name('data-murid.import');
        Route::resource('data-murid', MuridController::class)->parameters([
            'data-murid' => 'murid',
        ])->except(['create']);
        Route::post('data-murid/{murid}/toggle-status', [MuridController::class, 'toggleStatus'])->name('data-murid.toggle-status');
        Route::post('data-murid/{murid}/reset-password', [MuridController::class, 'resetPassword'])->name('data-murid.reset-password');
    });

    // Rombongan Belajar dan Bimbingan (Data Kelas)
    Route::prefix('rombel')->name('rombel.')->group(function () {
        Route::resource('kelas', KelasController::class)->parameters([
            'kelas' => 'kelas',
        ])->except(['show', 'create']);
        Route::post('kelas/{kelas}/toggle-status', [KelasController::class, 'toggleStatus'])->name('kelas.toggle-status');
    });

    // Konfigurasi Sekolah
    Route::prefix('konfigurasi-sekolah')->name('konfigurasi-sekolah.')->group(function () {
        // Tahun Ajaran dan Semester
        Route::get('/tahun-ajaran', [TahunAjaranController::class, 'index'])->name('tahun-ajaran.index');
        Route::post('/tahun-ajaran', [TahunAjaranController::class, 'store'])->name('tahun-ajaran.store');
        Route::put('/tahun-ajaran/{tahunAjaran}', [TahunAjaranController::class, 'update'])->name('tahun-ajaran.update');
        Route::delete('/tahun-ajaran/{tahunAjaran}', [TahunAjaranController::class, 'destroy'])->name('tahun-ajaran.destroy');
        Route::post('/tahun-ajaran/{tahunAjaran}/activate', [TahunAjaranController::class, 'activate'])->name('tahun-ajaran.activate');
        Route::post('/tahun-ajaran/{tahunAjaran}/deactivate', [TahunAjaranController::class, 'deactivate'])->name('tahun-ajaran.deactivate');

        Route::post('/tahun-ajaran/{tahunAjaran}/semester', [TahunAjaranController::class, 'storeSemester'])->name('tahun-ajaran.semester.store');
        Route::put('/tahun-ajaran/{tahunAjaran}/semester/{semester}', [TahunAjaranController::class, 'updateSemester'])->name('tahun-ajaran.semester.update');
        Route::delete('/tahun-ajaran/{tahunAjaran}/semester/{semester}', [TahunAjaranController::class, 'destroySemester'])->name('tahun-ajaran.semester.destroy');
        Route::post('/tahun-ajaran/{tahunAjaran}/semester/{semester}/activate', [TahunAjaranController::class, 'activateSemester'])->name('tahun-ajaran.semester.activate');

        // Kalender Akademik
        Route::get('/kalender-akademik/events', [KalenderAkademikController::class, 'events'])->name('kalender-akademik.events');
        Route::resource('kalender-akademik', KalenderAkademikController::class)->except(['show', 'create']);

        // Face Recognition - Pendaftaran Muka Murid
        Route::get('/regis-wajah', [PendaftaranMukaController::class, 'index'])->name('regis-wajah.index');
        Route::post('/regis-wajah', [PendaftaranMukaController::class, 'store'])->name('regis-wajah.store');
        Route::get('/regis-wajah/{murid}', [PendaftaranMukaController::class, 'show'])->name('regis-wajah.show');
        Route::patch('/regis-wajah/{faceProfile}/toggle-status', [PendaftaranMukaController::class, 'toggleStatus'])->name('regis-wajah.toggle-status');
        Route::delete('/regis-wajah/{faceProfile}', [PendaftaranMukaController::class, 'destroy'])->name('regis-wajah.destroy');
    });

    // Master Wilayah Administratif
    Route::prefix('wilayah')->name('wilayah.')->group(function () {
        Route::resource('provinsi', ProvinsiController::class)->except(['show', 'create']);
        Route::resource('kabupaten', KabupatenController::class)->except(['show', 'create']);
        Route::resource('kecamatan', KecamatanController::class)->except(['show', 'create']);
        Route::resource('kelurahan', KelurahanController::class)->except(['show', 'create']);

        // Endpoint Data Dropdown Wilayah (JSON)
        Route::prefix('dropdown')->name('dropdown.')->group(function () {
            Route::get('provinsi', [WilayahDropdownController::class, 'provinsi'])->name('provinsi');
            Route::get('kabupaten', [WilayahDropdownController::class, 'kabupaten'])->name('kabupaten');
            Route::get('kecamatan', [WilayahDropdownController::class, 'kecamatan'])->name('kecamatan');
            Route::get('kelurahan', [WilayahDropdownController::class, 'kelurahan'])->name('kelurahan');
        });
    });

    // Cadangan dan Pemulihan Basis Data (Backup dan Restore)
    Route::get('/backup-restore', [BackupRestoreController::class, 'index'])->name('backup-restore.index');
    Route::post('/backup-restore', [BackupRestoreController::class, 'store'])->name('backup-restore.create');
    Route::get('/backup-restore/download/{filename}', [BackupRestoreController::class, 'download'])->name('backup-restore.download');
    Route::post('/backup-restore/restore', [BackupRestoreController::class, 'restore'])->name('backup-restore.restore');
    Route::delete('/backup-restore/all', [BackupRestoreController::class, 'destroyAll'])->name('backup-restore.destroy-all');
    Route::delete('/backup-restore/{filename}', [BackupRestoreController::class, 'destroy'])->name('backup-restore.destroy');

    // Face Recognition - Absen Murid (Kiosk & Rekap Harian)
    Route::prefix('absensi-kbm')->name('absensi-kbm.')->group(function () {
        Route::get('/wajah', [AbsenMuridController::class, 'index'])->name('wajah.index');
        Route::get('/wajah/descriptors', [AbsenMuridController::class, 'descriptors'])->name('wajah.descriptors');
        Route::post('/wajah', [AbsenMuridController::class, 'store'])->name('wajah.store');
        Route::delete('/wajah/{absensi}', [AbsenMuridController::class, 'destroy'])->name('wajah.destroy');
    });
});
