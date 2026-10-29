<?php

use App\Models\Kelas;
use App\Models\Murid;
use App\Models\School;
use App\Models\Tingkatan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_murid_test',
        'email' => 'superadmin_murid@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->schoolA = School::factory()->smk()->create([
        'name' => 'SMK Negeri 1 Makassar',
        'is_active' => true,
    ]);

    $this->schoolB = School::factory()->sma()->create([
        'name' => 'SMA Negeri 2 Bone',
        'is_active' => true,
    ]);

    $this->adminSekolahA = User::factory()->create([
        'username' => 'admin_sekolah_murid_a',
        'email' => 'admin_murid_a@infora.test',
        'user_type' => 'admin',
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_murid_test',
        'email' => 'guru_murid@infora.test',
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
        'nama' => 'X PPLG 1',
        'is_active' => true,
    ]);
});

test('guest is redirected to login when accessing data murid', function () {
    $response = $this->get(route('master.data-murid'));

    $response->assertRedirect(route('login'));
});

test('unauthorized regular user without menu permission cannot access data murid', function () {
    $response = $this->actingAs($this->regularUser)->get(route('master.data-murid'));

    $response->assertForbidden();
});

test('super admin can view data murid page with kpi cards and modals', function () {
    Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'kelas_id' => $this->kelasA->id,
        'nama_lengkap' => 'Muhammad Raditya',
        'nisn' => '0081234567',
        'nis' => '24001',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-murid.index', [
        'school_id' => $this->schoolA->id,
    ]));

    $response->assertOk();
    $response->assertViewHas('provinsiList');
    $response->assertSee('Data Murid (Peserta Didik)');
    $response->assertSee('SMK Negeri 1 Makassar');
    $response->assertSee('Muhammad Raditya');
    $response->assertSee('0081234567');
    $response->assertSee('X PPLG 1');
    $response->assertSee('modalCreateMurid');
    $response->assertSee('modalEditMurid');
    $response->assertSee('modalShowMurid');
    $response->assertSee('modalImportMurid');
    $response->assertSee('modalDeleteMurid');
});

test('tenancy isolation strictly filters murid per school', function () {
    $muridA = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Siswa Sekolah A',
    ]);

    $muridB = Murid::factory()->create([
        'school_id' => $this->schoolB->id,
        'nama_lengkap' => 'Siswa Sekolah B',
    ]);

    $responseA = $this->actingAs($this->superAdmin)->get(route('master.data-murid', [
        'school_id' => $this->schoolA->id,
    ]));

    $responseA->assertOk();
    $responseA->assertSee('Siswa Sekolah A');
    $responseA->assertDontSee('Siswa Sekolah B');

    $responseB = $this->actingAs($this->superAdmin)->get(route('master.data-murid', [
        'school_id' => $this->schoolB->id,
    ]));

    $responseB->assertOk();
    $responseB->assertSee('Siswa Sekolah B');
    $responseB->assertDontSee('Siswa Sekolah A');
});

test('search filter matches nama, nisn, or nis', function () {
    Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Ahmad Fadhil',
        'nisn' => '0071111111',
        'nis' => '10001',
    ]);

    Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Bintang Pratama',
        'nisn' => '0072222222',
        'nis' => '10002',
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-murid', [
        'school_id' => $this->schoolA->id,
        'search' => 'Fadhil',
    ]));

    $response->assertOk();
    $response->assertSee('Ahmad Fadhil');
    $response->assertDontSee('Bintang Pratama');
});

test('filter kelas filters murid assigned to class and tanpa kelas', function () {
    $muridDenganKelas = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'kelas_id' => $this->kelasA->id,
        'nama_lengkap' => 'Murid Kelas PPLG',
    ]);

    $muridTanpaKelas = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'kelas_id' => null,
        'nama_lengkap' => 'Murid Belum Masuk Kelas',
    ]);

    $responseKelas = $this->actingAs($this->superAdmin)->get(route('master.data-murid', [
        'school_id' => $this->schoolA->id,
        'kelas_id' => $this->kelasA->id,
    ]));

    $responseKelas->assertOk();
    $responseKelas->assertSee('Murid Kelas PPLG');
    $responseKelas->assertDontSee('Murid Belum Masuk Kelas');

    $responseTanpa = $this->actingAs($this->superAdmin)->get(route('master.data-murid', [
        'school_id' => $this->schoolA->id,
        'kelas_id' => 'tanpa_kelas',
    ]));

    $responseTanpa->assertOk();
    $responseTanpa->assertSee('Murid Belum Masuk Kelas');
    $responseTanpa->assertDontSee('Murid Kelas PPLG');
});

test('super admin can create new murid and automatically generate user account', function () {
    $payload = [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'annisa dwi lestari',
        'jenis_kelamin' => 'P',
        'nisn' => '0098765432',
        'nis' => '24050',
        'nik' => '7308123456789012',
        'kelas_id' => $this->kelasA->id,
        'angkatan' => '2026',
        'tempat_lahir' => 'bone',
        'tanggal_lahir' => '2010-08-17',
        'agama' => 'Islam',
        'no_hp' => '081234567899',
        'email' => 'annisa@example.com',
        'nama_ayah' => 'haji ruslan',
        'nama_ibu' => 'hajjah rosma',
        'no_hp_ortu' => '081298765433',
        'status' => 'aktif',
        'is_active' => 1,
        'create_user_account' => 1,
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-murid.store'), $payload);

    $response->assertRedirect(route('master.data-murid', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('murids', [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Annisa Dwi Lestari',
        'nisn' => '0098765432',
        'nis' => '24050',
        'tempat_lahir' => 'Bone',
        'nama_ayah' => 'Haji Ruslan',
        'nama_ibu' => 'Hajjah Rosma',
    ]);

    $murid = Murid::where('nisn', '0098765432')->first();
    expect($murid)->not->toBeNull()
        ->and($murid->user_id)->not->toBeNull();

    $this->assertDatabaseHas('users', [
        'id' => $murid->user_id,
        'user_type' => 'murid',
        'username' => '0098765432',
        'is_active' => true,
    ]);
});

test('store murid validates required fields and unique nisn per school', function () {
    $existing = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'nisn' => '0055555555',
        'nis' => '11111',
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-murid.store'), [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => '',
        'jenis_kelamin' => '',
        'nisn' => '0055555555',
        'nis' => '11111',
    ]);

    $response->assertSessionHasErrors(['nama_lengkap', 'jenis_kelamin', 'nisn', 'nis']);
});

test('show returns json data for preview modal', function () {
    $murid = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'kelas_id' => $this->kelasA->id,
        'nama_lengkap' => 'Zahra Amelia',
        'nisn' => '0067891234',
        'nis' => '24099',
        'jenis_kelamin' => 'P',
        'agama' => 'Islam',
    ]);

    $response = $this->actingAs($this->superAdmin)->getJson(route('master.data-murid.show', $murid));

    $response->assertOk();
    $response->assertJson([
        'id' => $murid->id,
        'nama_lengkap' => 'Zahra Amelia',
        'nisn' => '0067891234',
        'nis' => '24099',
        'jenis_kelamin' => 'P',
        'kelas_nama' => 'X PPLG 1',
    ]);
});

test('super admin can update murid data and sync user account', function () {
    $murid = Murid::factory()->denganUser()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Rian Hidayat',
        'nisn' => '0078888888',
    ]);

    $payload = [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'rian hidayat pratama',
        'jenis_kelamin' => 'L',
        'nisn' => '0078888888',
        'nis' => '24111',
        'angkatan' => '2026',
        'status' => 'aktif',
        'is_active' => 1,
        'username' => 'rian_hidayat_new',
    ];

    $response = $this->actingAs($this->superAdmin)->put(route('master.data-murid.update', $murid), $payload);

    $response->assertRedirect(route('master.data-murid', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('murids', [
        'id' => $murid->id,
        'nama_lengkap' => 'Rian Hidayat Pratama',
        'nis' => '24111',
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $murid->user_id,
        'username' => 'rian_hidayat_new',
    ]);
});

test('admin sekolah cannot update or delete murid from another school (IDOR protection)', function () {
    $muridB = Murid::factory()->create([
        'school_id' => $this->schoolB->id,
        'nama_lengkap' => 'Murid Sekolah B',
    ]);

    $response = $this->actingAs($this->adminSekolahA)->put(route('master.data-murid.update', $muridB), [
        'school_id' => $this->schoolB->id,
        'nama_lengkap' => 'Hacked Name',
        'jenis_kelamin' => 'L',
    ]);

    $response->assertForbidden();

    $deleteResponse = $this->actingAs($this->adminSekolahA)->delete(route('master.data-murid.destroy', $muridB));
    $deleteResponse->assertForbidden();
});

test('toggle status switches murid and user active state', function () {
    $murid = Murid::factory()->denganUser()->create([
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-murid.toggle-status', $murid));

    $response->assertRedirect(route('master.data-murid', ['school_id' => $this->schoolA->id]));
    expect($murid->fresh()->is_active)->toBeFalse()
        ->and($murid->user->fresh()->is_active)->toBeFalse();

    $this->actingAs($this->superAdmin)->post(route('master.data-murid.toggle-status', $murid));
    expect($murid->fresh()->is_active)->toBeTrue()
        ->and($murid->user->fresh()->is_active)->toBeTrue();
});

test('reset password resets user account password to default', function () {
    $murid = Murid::factory()->denganUser()->create([
        'school_id' => $this->schoolA->id,
        'nisn' => '0081239999',
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-murid.reset-password', $murid));

    $response->assertRedirect(route('master.data-murid', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    // Cek kata sandi dapat digunakan untuk login
    $this->assertTrue(Hash::check('0081239999', $murid->user->fresh()->password));
});

test('destroy soft deletes murid and deactivates user account', function () {
    $murid = Murid::factory()->denganUser()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Siswa Yang Dihapus',
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(route('master.data-murid.destroy', $murid));

    $response->assertRedirect(route('master.data-murid', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('murids', ['id' => $murid->id]);
    expect($murid->user->fresh()->is_active)->toBeFalse();
});
