<?php

use App\Models\AbsensiMurid;
use App\Models\Kelas;
use App\Models\Menu;
use App\Models\Module;
use App\Models\Murid;
use App\Models\MuridFaceProfile;
use App\Models\School;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\Tingkatan;
use App\Models\User;
use App\Models\UserMenuPermission;
use Illuminate\Support\Facades\Storage;

function absenSamplePngBase64(): string
{
    return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
}

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_absen_test',
        'email' => 'superadmin_absen@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->schoolA = School::factory()->smk()->create([
        'name' => 'SMK Negeri 1 Pinrang',
        'is_active' => true,
    ]);

    $this->schoolB = School::factory()->sma()->create([
        'name' => 'SMA Negeri 3 Gowa',
        'is_active' => true,
    ]);

    $this->adminSekolahA = User::factory()->create([
        'username' => 'admin_absen_a',
        'email' => 'admin_absen_a@infora.test',
        'user_type' => 'admin',
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_absen_test',
        'email' => 'guru_absen@infora.test',
        'user_type' => 'guru',
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);

    $this->tingkatanA = Tingkatan::factory()->kelas10()->create([
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);

    $this->kelasA = Kelas::factory()->create([
        'school_id' => $this->schoolA->id,
        'tingkatan_id' => $this->tingkatanA->id,
        'nama' => 'X TKJ 1',
        'is_active' => true,
    ]);
});

test('guest is redirected to login when accessing absen murid', function () {
    $this->get(route('absen_murid'))->assertRedirect(route('login'));
    $this->get(route('absen_murid.descriptors'))->assertRedirect(route('login'));
});

test('unauthorized user without menu permission is forbidden from absen murid', function () {
    $this->actingAs($this->regularUser)
        ->get(route('absen_murid'))
        ->assertForbidden();
});

test('user with view permission can view absen murid and descriptors but cannot store without create permission', function () {
    $module = Module::create(['name' => 'FACE RECOGNITION', 'order' => 6, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Absen Murid',
        'route_name' => 'absen_murid',
        'icon' => 'scan-face',
        'order' => 2,
        'is_active' => true,
    ]);

    UserMenuPermission::create([
        'user_id' => $this->adminSekolahA->id,
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_create' => false,
        'can_edit' => false,
        'can_delete' => false,
    ]);

    $murid = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'kelas_id' => $this->kelasA->id,
        'nama_lengkap' => 'Nadia Putri',
        'is_active' => true,
    ]);

    MuridFaceProfile::factory()->create([
        'school_id' => $this->schoolA->id,
        'murid_id' => $murid->id,
    ]);

    $this->actingAs($this->adminSekolahA)
        ->get(route('absen_murid'))
        ->assertOk()
        ->assertSee('Absen Murid (Face Recognition)')
        ->assertSee('btnToggleVoice')
        ->assertSee('btnTestVoice');

    $this->actingAs($this->adminSekolahA)
        ->getJson(route('absen_murid.descriptors'))
        ->assertOk()
        ->assertJsonCount(1, 'students')
        ->assertJsonPath('students.0.nama_lengkap', 'Nadia Putri');

    $this->actingAs($this->adminSekolahA)
        ->postJson(route('absen_murid.store'), [
            'school_id' => $this->schoolA->id,
            'murid_id' => $murid->id,
            'mode' => 'masuk',
        ])
        ->assertForbidden();
});

test('descriptors endpoint filters active students with active faces per school and kelas', function () {
    $muridSiap = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'kelas_id' => $this->kelasA->id,
        'nama_lengkap' => 'Arif Hidayat',
        'is_active' => true,
    ]);
    MuridFaceProfile::factory()->create([
        'school_id' => $this->schoolA->id,
        'murid_id' => $muridSiap->id,
        'is_active' => true,
    ]);

    // Murid hanya punya sampel nonaktif
    $muridNonaktifSampel = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'kelas_id' => $this->kelasA->id,
        'nama_lengkap' => 'Sampel Mati',
        'is_active' => true,
    ]);
    MuridFaceProfile::factory()->nonaktif()->create([
        'school_id' => $this->schoolA->id,
        'murid_id' => $muridNonaktifSampel->id,
    ]);

    // Murid sekolah lain
    $muridB = Murid::factory()->create([
        'school_id' => $this->schoolB->id,
        'nama_lengkap' => 'Murid Sekolah B',
        'is_active' => true,
    ]);
    MuridFaceProfile::factory()->create([
        'school_id' => $this->schoolB->id,
        'murid_id' => $muridB->id,
    ]);

    $this->actingAs($this->superAdmin)
        ->getJson(route('absen_murid.descriptors', [
            'school_id' => $this->schoolA->id,
            'kelas_id' => $this->kelasA->id,
        ]))
        ->assertOk()
        ->assertJsonCount(1, 'students')
        ->assertJsonPath('students.0.nama_lengkap', 'Arif Hidayat')
        ->assertJsonCount(128, 'students.0.descriptors.0.descriptor');
});

test('store records masuk attendance with welcome greeting, snapshot, and active semester', function () {
    Storage::fake('public');
    $this->travelTo(now()->setTime(7, 5, 0));

    $ta = TahunAjaran::create([
        'school_id' => $this->schoolA->id,
        'tahun' => '2026/2027',
        'is_active' => true,
    ]);
    $semester = Semester::create([
        'tahun_ajaran_id' => $ta->id,
        'semester' => 'ganjil',
        'is_active' => true,
    ]);

    $murid = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'kelas_id' => $this->kelasA->id,
        'nama_lengkap' => 'Muhammad Raditya',
        'is_active' => true,
    ]);
    $faceProfile = MuridFaceProfile::factory()->create([
        'school_id' => $this->schoolA->id,
        'murid_id' => $murid->id,
    ]);

    $response = $this->actingAs($this->superAdmin)->postJson(route('absen_murid.store'), [
        'school_id' => $this->schoolA->id,
        'murid_id' => $murid->id,
        'face_profile_id' => $faceProfile->id,
        'mode' => 'masuk',
        'match_score' => 0.875,
        'jam_batas_masuk' => '07:15',
        'foto_base64' => absenSamplePngBase64(),
    ]);

    $response->assertCreated()
        ->assertJsonPath('already_recorded', false)
        ->assertJsonPath('greeting', 'Selamat datang, Muhammad Raditya!')
        ->assertJsonPath('attendance.status', 'hadir')
        ->assertJsonPath('attendance.match_score_percent', '87.5%')
        ->assertJsonPath('stats.hadir_hari_ini', 1);

    $attendance = AbsensiMurid::where('murid_id', $murid->id)->first();
    expect($attendance)->not->toBeNull()
        ->and($attendance->status)->toBe('hadir')
        ->and($attendance->tahun_ajaran_id)->toBe($ta->id)
        ->and($attendance->semester_id)->toBe($semester->id)
        ->and($attendance->foto_masuk_path)->not->toBeNull();

    Storage::disk('public')->assertExists($attendance->foto_masuk_path);

    // Scan kedua di hari yang sama tidak membuat duplikat
    $duplicateResponse = $this->actingAs($this->superAdmin)->postJson(route('absen_murid.store'), [
        'school_id' => $this->schoolA->id,
        'murid_id' => $murid->id,
        'mode' => 'masuk',
    ]);

    $duplicateResponse->assertOk()
        ->assertJsonPath('already_recorded', true);

    expect(AbsensiMurid::where('murid_id', $murid->id)->count())->toBe(1);
});

test('store marks terlambat when check in exceeds jam_batas_masuk and handles pulang mode', function () {
    $this->travelTo(now()->setTime(7, 45, 0));

    $murid = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'kelas_id' => $this->kelasA->id,
        'nama_lengkap' => 'Dewi Kartika',
        'is_active' => true,
    ]);

    // Pulang sebelum masuk ditolak
    $this->actingAs($this->superAdmin)->postJson(route('absen_murid.store'), [
        'school_id' => $this->schoolA->id,
        'murid_id' => $murid->id,
        'mode' => 'pulang',
    ])->assertUnprocessable();

    // Masuk melewati batas 07:15 -> terlambat
    $this->actingAs($this->superAdmin)->postJson(route('absen_murid.store'), [
        'school_id' => $this->schoolA->id,
        'murid_id' => $murid->id,
        'mode' => 'masuk',
        'jam_batas_masuk' => '07:15',
    ])
        ->assertCreated()
        ->assertJsonPath('attendance.status', 'terlambat')
        ->assertJsonPath('stats.terlambat_hari_ini', 1);

    // Pulang setelah masuk berhasil
    $this->travelTo(now()->setTime(14, 15, 0));
    $this->actingAs($this->superAdmin)->postJson(route('absen_murid.store'), [
        'school_id' => $this->schoolA->id,
        'murid_id' => $murid->id,
        'mode' => 'pulang',
    ])
        ->assertOk()
        ->assertJsonPath('already_recorded', false)
        ->assertJsonPath('greeting', 'Sampai jumpa, Dewi Kartika!')
        ->assertJsonPath('attendance.jam_pulang', '14:15:00');
});

test('store validates cross-school murid and invalid input', function () {
    $muridB = Murid::factory()->create([
        'school_id' => $this->schoolB->id,
        'is_active' => true,
    ]);

    $this->actingAs($this->superAdmin)->postJson(route('absen_murid.store'), [
        'school_id' => $this->schoolA->id,
        'murid_id' => $muridB->id,
        'mode' => 'invalid_mode',
        'jam_batas_masuk' => '99:99',
        'foto_base64' => 'invalid-data',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['murid_id', 'mode', 'jam_batas_masuk', 'foto_base64']);
});

test('destroy removes attendance record with snapshot and enforces cross-school IDOR protection', function () {
    Storage::fake('public');

    $muridA = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);

    $this->actingAs($this->superAdmin)->postJson(route('absen_murid.store'), [
        'school_id' => $this->schoolA->id,
        'murid_id' => $muridA->id,
        'mode' => 'masuk',
        'foto_base64' => absenSamplePngBase64(),
    ])->assertCreated();

    $absenA = AbsensiMurid::where('murid_id', $muridA->id)->firstOrFail();
    $snapshotPath = $absenA->foto_masuk_path;

    $this->actingAs($this->superAdmin)
        ->delete(route('absen_murid.destroy', $absenA))
        ->assertRedirect(route('absen_murid', ['school_id' => $this->schoolA->id]));

    $this->assertDatabaseMissing('absensi_murids', ['id' => $absenA->id]);
    Storage::disk('public')->assertMissing($snapshotPath);

    // Cross-school IDOR test
    $module = Module::create(['name' => 'FACE RECOGNITION', 'order' => 6, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Absen Murid',
        'route_name' => 'absen_murid',
        'icon' => 'scan-face',
        'order' => 2,
        'is_active' => true,
    ]);
    UserMenuPermission::create([
        'user_id' => $this->adminSekolahA->id,
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_create' => true,
        'can_edit' => true,
        'can_delete' => true,
    ]);

    $absenB = AbsensiMurid::factory()->create([
        'school_id' => $this->schoolB->id,
    ]);

    $this->actingAs($this->adminSekolahA)
        ->delete(route('absen_murid.destroy', $absenB))
        ->assertForbidden();
});
