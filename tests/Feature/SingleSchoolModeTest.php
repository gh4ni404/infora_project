<?php

use App\Models\AbsensiMurid;
use App\Models\Kelurahan;
use App\Models\Murid;
use App\Models\MuridFaceProfile;
use App\Models\Provinsi;
use App\Models\Referensi;
use App\Models\School;
use App\Models\User;
use App\Services\DatabaseBackupService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $schoolTypeCat = Referensi::firstOrCreate(
        ['kode' => 'school_type'],
        ['nama' => 'Jenis Sekolah', 'is_active' => true, 'is_system' => true]
    );

    foreach (['SMA', 'SMK', 'MA'] as $type) {
        Referensi::firstOrCreate(
            ['kode' => $type],
            [
                'referensi_id' => $schoolTypeCat->id,
                'nama' => $type,
                'is_active' => true,
                'is_system' => true,
            ]
        );
    }

    $accreditationCat = Referensi::firstOrCreate(
        ['kode' => 'accreditation'],
        ['nama' => 'Akreditasi', 'is_active' => true, 'is_system' => true]
    );

    foreach (['A', 'B', 'C', 'Belum'] as $acc) {
        Referensi::firstOrCreate(
            ['kode' => $acc],
            [
                'referensi_id' => $accreditationCat->id,
                'nama' => $acc,
                'is_active' => true,
                'is_system' => true,
            ]
        );
    }

    Referensi::clearCache();

    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_single_school',
        'email' => 'superadmin_single@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);
});

test('single-school mode disables web routes for creating or deleting schools', function () {
    $school = School::factory()->smk()->create([
        'name' => 'SMK Negeri 1 Bone',
        'npsn' => '40301111',
    ]);

    expect(Route::has('master.data-sekolah.store'))->toBeFalse()
        ->and(Route::has('master.data-sekolah.destroy'))->toBeFalse()
        ->and(Route::has('master.data-sekolah.sync-admins'))->toBeFalse();

    $this->actingAs($this->superAdmin)
        ->post('/master/data-sekolah', [
            'name' => 'SMA Negeri 2 Bone',
            'npsn' => '40302222',
        ])
        ->assertStatus(405);

    $this->actingAs($this->superAdmin)
        ->delete('/master/data-sekolah/'.$school->id)
        ->assertStatus(405);
});

test('form request automatically resolves single school_id when omitted by super admin', function () {
    $school = School::factory()->smk()->create([
        'name' => 'SMK Negeri 1 Bone',
        'npsn' => '40301111',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-jurusan.store'), [
        'kode' => 'RPL',
        'nama' => 'Rekayasa Perangkat Lunak',
        'jenjang' => 'SMK',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('master.data-jurusan.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('jurusan', [
        'school_id' => $school->id,
        'kode' => 'RPL',
    ]);
});

test('absen murid descriptors are cached and invalidated when face profile or murid changes', function () {
    $school = School::factory()->smk()->create(['is_active' => true]);
    $murid = Murid::factory()->create([
        'school_id' => $school->id,
        'nama_lengkap' => 'Ahmad Rifai',
        'is_active' => true,
    ]);

    $profile = MuridFaceProfile::factory()->create([
        'school_id' => $school->id,
        'murid_id' => $murid->id,
        'is_active' => true,
    ]);

    // 1. Panggil descriptors pertama -> mengisi cache
    $firstResponse = $this->actingAs($this->superAdmin)->getJson(route('absen_murid.descriptors'));
    $firstResponse->assertOk()->assertJsonPath('total_students', 1);

    $cacheKey = MuridFaceProfile::descriptorCacheKey($school->id, null);
    expect(Cache::has($cacheKey))->toBeTrue();

    // 2. Toggle status profil wajah -> cache version ter-increment & data terupdate
    $this->actingAs($this->superAdmin)
        ->patchJson(route('pendaftaran_muka.toggle-status', $profile))
        ->assertOk();

    $secondResponse = $this->actingAs($this->superAdmin)->getJson(route('absen_murid.descriptors'));
    $secondResponse->assertOk()->assertJsonPath('total_students', 0);

    // 3. Aktifkan lagi lalu nonaktifkan murid lewat MuridController -> cache ikut ter-invalidasi
    $this->actingAs($this->superAdmin)
        ->patchJson(route('pendaftaran_muka.toggle-status', $profile))
        ->assertOk();

    $this->actingAs($this->superAdmin)
        ->getJson(route('absen_murid.descriptors'))
        ->assertOk()
        ->assertJsonPath('total_students', 1);

    $this->actingAs($this->superAdmin)
        ->post(route('master.data-murid.toggle-status', $murid))
        ->assertRedirect();

    $this->actingAs($this->superAdmin)
        ->getJson(route('absen_murid.descriptors'))
        ->assertOk()
        ->assertJsonPath('total_students', 0);
});

test('school_only backup excludes Kemendagri region tables while preserving school tables', function () {
    School::factory()->smk()->create(['name' => 'SMK Uji Backup', 'npsn' => '40309876']);

    /** @var DatabaseBackupService $service */
    $service = app(DatabaseBackupService::class);
    $backup = $service->createBackup('school_only');

    try {
        expect($backup['type'])->toBe('school_only')
            ->and($backup['filename'])->toStartWith('backup_infora_school_')
            ->and(File::exists($backup['path']))->toBeTrue();

        $sqlContent = File::get($backup['path']);
        expect($sqlContent)->toContain('`schools`')
            ->and($sqlContent)->not->toContain('DROP TABLE IF EXISTS `provinsi`')
            ->and($sqlContent)->not->toContain('DROP TABLE IF EXISTS `kabupaten`')
            ->and($sqlContent)->not->toContain('DROP TABLE IF EXISTS `kecamatan`')
            ->and($sqlContent)->not->toContain('DROP TABLE IF EXISTS `kelurahan`');

        $listed = $service->listBackups()->firstWhere('filename', $backup['filename']);
        expect($listed)->not->toBeNull()
            ->and($listed['type'])->toBe('school_only')
            ->and($listed['type_label'])->toBe('Data Sekolah Saja (SQL)');
    } finally {
        File::delete($backup['path']);
    }
});

test('infora:init-school command initializes single school and admin account', function () {
    $this->artisan('infora:init-school', [
        '--name' => 'SMK Negeri 1 Nusantara',
        '--npsn' => '40308888',
        '--type' => 'SMK',
        '--status' => 'Negeri',
        '--accreditation' => 'A',
    ])->assertSuccessful();

    $this->assertDatabaseHas('schools', [
        'name' => 'SMK Negeri 1 Nusantara',
        'npsn' => '40308888',
        'school_type' => 'SMK',
    ]);

    $this->assertDatabaseHas('users', [
        'username' => '40308888',
        'user_type' => 'admin',
    ]);

    // Menjalankan ulang tanpa --force harus ditolak
    $this->artisan('infora:init-school', [
        '--name' => 'SMA Negeri 2',
        '--npsn' => '40309999',
    ])->assertFailed();

    expect(School::count())->toBe(1);

    // Menjalankan ulang dengan --force harus menyinkronkan admin
    $this->artisan('infora:init-school', ['--force' => true])->assertSuccessful();
});

test('infora:prune-attendance-photos command deletes old snapshots and preserves recent ones', function () {
    Storage::fake('public');

    $school = School::factory()->smk()->create();
    $muridOld = Murid::factory()->create(['school_id' => $school->id]);
    $muridRecent = Murid::factory()->create(['school_id' => $school->id]);

    $oldPath = 'absensi/murid/old_snapshot.webp';
    $recentPath = 'absensi/murid/recent_snapshot.webp';
    Storage::disk('public')->put($oldPath, 'old-image-data');
    Storage::disk('public')->put($recentPath, 'recent-image-data');

    $oldAttendance = AbsensiMurid::factory()->create([
        'school_id' => $school->id,
        'murid_id' => $muridOld->id,
        'tanggal' => now()->subDays(200)->toDateString(),
        'foto_masuk_path' => $oldPath,
        'foto_pulang_path' => null,
    ]);

    $recentAttendance = AbsensiMurid::factory()->create([
        'school_id' => $school->id,
        'murid_id' => $muridRecent->id,
        'tanggal' => now()->subDays(10)->toDateString(),
        'foto_masuk_path' => $recentPath,
        'foto_pulang_path' => null,
    ]);

    $this->artisan('infora:prune-attendance-photos', ['--days' => 180])
        ->assertSuccessful();

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($recentPath);

    expect($oldAttendance->fresh()->foto_masuk_path)->toBeNull()
        ->and($recentAttendance->fresh()->foto_masuk_path)->toBe($recentPath);
});

test('infora:setup-school sets up core seeders, single school, and admin with without-wilayah option', function () {
    $this->artisan('infora:setup-school', [
        '--name' => 'SMA Negeri 1 Harapan',
        '--npsn' => '40307777',
        '--type' => 'SMA',
        '--status' => 'Negeri',
        '--accreditation' => 'A',
        '--without-wilayah' => true,
        '--skip-optimize' => true,
    ])->assertSuccessful();

    $this->assertDatabaseHas('users', [
        'username' => 'superadmin',
        'user_type' => 'super_admin',
    ]);

    $this->assertDatabaseHas('schools', [
        'name' => 'SMA Negeri 1 Harapan',
        'npsn' => '40307777',
        'school_type' => 'SMA',
    ]);

    $this->assertDatabaseHas('users', [
        'username' => '40307777',
        'user_type' => 'admin',
    ]);

    $this->assertDatabaseCount('provinsi', 0);

    // Menjalankan ulang tanpa --force harus ditolak
    $this->artisan('infora:setup-school', [
        '--name' => 'SMK Negeri 2',
        '--npsn' => '40306666',
        '--without-wilayah' => true,
        '--skip-optimize' => true,
    ])->assertFailed();

    expect(School::count())->toBe(1);

    // Menjalankan ulang dengan --force harus berhasil menyinkronkan admin
    $this->artisan('infora:setup-school', [
        '--without-wilayah' => true,
        '--skip-optimize' => true,
        '--force' => true,
    ])->assertSuccessful();
});

test('infora:setup-school seeds regional master data when without-wilayah is not passed', function () {
    $this->artisan('infora:setup-school', [
        '--name' => 'SMK Negeri 3 Bone',
        '--npsn' => '40305555',
        '--type' => 'SMK',
        '--status' => 'Negeri',
        '--accreditation' => 'A',
        '--skip-optimize' => true,
    ])->assertSuccessful();

    expect(Provinsi::count())->toBe(38)
        ->and(Kelurahan::count())->toBe(295);

    $this->assertDatabaseHas('kelurahan', [
        'kode' => '7308191001',
        'nama' => 'Unyi',
    ]);
    $this->assertDatabaseHas('kelurahan', [
        'kode' => '7308241015',
        'nama' => 'Mampotu',
        'kode_pos' => null,
    ]);
    $this->assertDatabaseHas('schools', [
        'npsn' => '40305555',
    ]);
});

test('infora:setup-school fails cleanly in non-interactive mode when database connection fails', function () {
    $originalDefault = config('database.default');
    config()->set('database.default', 'mysql');
    config()->set('database.connections.mysql.database', '');

    try {
        $this->artisan('infora:setup-school', ['--no-interaction' => true])
            ->assertExitCode(1);
    } finally {
        config()->set('database.default', $originalDefault);
    }
});

test('infora:init-school infers SMA school_type from school name when type option is omitted', function () {
    $this->artisan('infora:init-school', [
        '--name' => 'SMA Negeri 20 Bone',
        '--npsn' => '40320001',
        '--no-interaction' => true,
    ])->assertSuccessful();

    $this->assertDatabaseHas('schools', [
        'name' => 'SMA Negeri 20 Bone',
        'npsn' => '40320001',
        'school_type' => 'SMA',
        'status' => 'Negeri',
    ]);
});

test('infora:init-school infers SD school_type from school name when type option is omitted', function () {
    $this->artisan('infora:init-school', [
        '--name' => 'SD Negeri 1 Bone',
        '--npsn' => '40320002',
        '--no-interaction' => true,
    ])->assertSuccessful();

    $this->assertDatabaseHas('schools', [
        'name' => 'SD Negeri 1 Bone',
        'npsn' => '40320002',
        'school_type' => 'SD',
        'status' => 'Negeri',
    ]);
});

test('infora:setup-school skips all seeders when without-seed option is passed', function () {
    $this->artisan('infora:setup-school', [
        '--name' => 'SMA Negeri 20 Bone',
        '--npsn' => '40320003',
        '--without-seed' => true,
        '--skip-optimize' => true,
        '--no-interaction' => true,
    ])->assertSuccessful();

    $this->assertDatabaseMissing('users', ['username' => 'superadmin']);
    $this->assertDatabaseCount('modules', 0);
    $this->assertDatabaseCount('provinsi', 0);
    $this->assertDatabaseHas('schools', [
        'name' => 'SMA Negeri 20 Bone',
        'npsn' => '40320003',
        'school_type' => 'SMA',
    ]);
});
