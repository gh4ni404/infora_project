<?php

use App\Models\Jurusan;
use App\Models\School;
use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_jurusan_test',
        'email' => 'superadmin_jurusan@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_jurusan_test',
        'email' => 'guru_jurusan@infora.test',
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $this->schoolSmk = School::factory()->smk()->create([
        'name' => 'SMK Negeri 8 Bone',
        'is_active' => true,
    ]);

    $this->schoolSma = School::factory()->sma()->create([
        'name' => 'SMA Negeri 4 Bone',
        'is_active' => true,
    ]);
});

test('guest is redirected to login when accessing master data jurusan', function () {
    $response = $this->get(route('master.data-jurusan'));

    $response->assertRedirect(route('login'));
});

test('non super admin cannot access master data jurusan', function () {
    $response = $this->actingAs($this->regularUser)->get(route('master.data-jurusan'));

    $response->assertForbidden();
});

test('super admin can view master data jurusan page', function () {
    Jurusan::factory()->create([
        'school_id' => $this->schoolSmk->id,
        'kode' => 'RPL',
        'nama' => 'Rekayasa Perangkat Lunak',
        'jenjang' => 'SMK',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-jurusan', [
        'school_id' => $this->schoolSmk->id,
    ]));

    $response->assertOk();
    $response->assertSee('Master Data Jurusan');
    $response->assertSee('SMK Negeri 8 Bone');
    $response->assertSee('RPL');
    $response->assertSee('Rekayasa Perangkat Lunak');
    $response->assertSee('modalCreateJurusan');
    $response->assertSee('modalEditJurusan');
    $response->assertSee('modalDeleteJurusan');
});

test('school selector filters jurusan list correctly', function () {
    $jurusanSmk = Jurusan::factory()->create([
        'school_id' => $this->schoolSmk->id,
        'kode' => 'TKJ',
        'nama' => 'Teknik Komputer Jaringan',
    ]);

    $jurusanSma = Jurusan::factory()->create([
        'school_id' => $this->schoolSma->id,
        'kode' => 'MIPA',
        'nama' => 'Matematika Dan Ilmu Alam',
    ]);

    // View SMK page
    $responseSmk = $this->actingAs($this->superAdmin)->get(route('master.data-jurusan', [
        'school_id' => $this->schoolSmk->id,
    ]));
    $responseSmk->assertOk();
    $responseSmk->assertSee('Teknik Komputer Jaringan');
    $responseSmk->assertDontSee('Matematika Dan Ilmu Alam');

    // View SMA page
    $responseSma = $this->actingAs($this->superAdmin)->get(route('master.data-jurusan', [
        'school_id' => $this->schoolSma->id,
    ]));
    $responseSma->assertOk();
    $responseSma->assertSee('Matematika Dan Ilmu Alam');
    $responseSma->assertDontSee('Teknik Komputer Jaringan');
});

test('search filter works accurately', function () {
    Jurusan::factory()->create([
        'school_id' => $this->schoolSmk->id,
        'kode' => 'TKG',
        'nama' => 'Teknik Komputer Grafika',
        'kepala_jurusan' => 'Ahmad Fathir',
    ]);

    Jurusan::factory()->create([
        'school_id' => $this->schoolSmk->id,
        'kode' => 'TKL',
        'nama' => 'Teknik Kendaraan Listrik',
        'kepala_jurusan' => 'Budi Santoso',
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-jurusan', [
        'school_id' => $this->schoolSmk->id,
        'search' => 'Fathir',
    ]));

    $response->assertOk();
    $response->assertSee('Teknik Komputer Grafika');
    $response->assertDontSee('Teknik Kendaraan Listrik');
});

test('status filter returns matching jurusan', function () {
    Jurusan::factory()->create([
        'school_id' => $this->schoolSmk->id,
        'kode' => 'TMR',
        'nama' => 'Teknik Mekatronika Robotika',
        'is_active' => true,
    ]);

    Jurusan::factory()->create([
        'school_id' => $this->schoolSmk->id,
        'kode' => 'ATD',
        'nama' => 'Animasi Tiga Dimensi',
        'is_active' => false,
    ]);

    $responseActive = $this->actingAs($this->superAdmin)->get(route('master.data-jurusan', [
        'school_id' => $this->schoolSmk->id,
        'status' => 'aktif',
    ]));
    $responseActive->assertOk();
    $responseActive->assertSee('Teknik Mekatronika Robotika');
    $responseActive->assertDontSee('Animasi Tiga Dimensi');

    $responseInactive = $this->actingAs($this->superAdmin)->get(route('master.data-jurusan', [
        'school_id' => $this->schoolSmk->id,
        'status' => 'nonaktif',
    ]));
    $responseInactive->assertOk();
    $responseInactive->assertSee('Animasi Tiga Dimensi');
    $responseInactive->assertDontSee('Teknik Mekatronika Robotika');
});

test('validation fails when storing jurusan without required fields', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('master.data-jurusan.store'), [
        'school_id' => $this->schoolSmk->id,
        'kode' => '',
        'nama' => '',
        'jenjang' => '',
    ]);

    $response->assertSessionHasErrors(['kode', 'nama', 'jenjang']);
});

test('super admin can store new jurusan with proper formatting', function () {
    $payload = [
        'school_id' => $this->schoolSmk->id,
        'kode' => 'rpl',
        'singkatan' => 'rpl',
        'nama' => 'rekayasa perangkat lunak',
        'jenjang' => 'SMK',
        'bidang_keahlian' => 'teknologi informasi',
        'program_keahlian' => 'pengembangan perangkat lunak dan gim',
        'kepala_jurusan' => 'ir. hendra wijaya, s.kom.',
        'is_active' => '1',
        'deskripsi' => 'Jurusan software engineering unggulan.',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-jurusan.store'), $payload);

    $response->assertRedirect(route('master.data-jurusan', ['school_id' => $this->schoolSmk->id]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('jurusan', [
        'school_id' => $this->schoolSmk->id,
        'kode' => 'RPL', // UPPERCASE
        'singkatan' => 'RPL', // UPPERCASE
        'nama' => 'Rekayasa Perangkat Lunak', // Title Case
        'jenjang' => 'SMK',
        'is_active' => true,
    ]);
});

test('super admin can update existing jurusan', function () {
    $jurusan = Jurusan::factory()->create([
        'school_id' => $this->schoolSmk->id,
        'kode' => 'TKJ',
        'nama' => 'Teknik Komputer Jaringan',
        'jenjang' => 'SMK',
        'is_active' => true,
    ]);

    $payload = [
        'school_id' => $this->schoolSmk->id,
        'kode' => 'TJKT',
        'singkatan' => 'TJKT',
        'nama' => 'Teknik Jaringan Komputer Dan Telekomunikasi',
        'jenjang' => 'SMK',
        'bidang_keahlian' => 'Teknologi Informasi',
        'program_keahlian' => 'Teknik Komputer',
        'kepala_jurusan' => 'Drs. Syahrul',
        'is_active' => '1',
        'deskripsi' => 'Nama kurikulum baru.',
    ];

    $response = $this->actingAs($this->superAdmin)->put(route('master.data-jurusan.update', $jurusan), $payload);

    $response->assertRedirect(route('master.data-jurusan', ['school_id' => $this->schoolSmk->id]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('jurusan', [
        'id' => $jurusan->id,
        'kode' => 'TJKT',
        'nama' => 'Teknik Jaringan Komputer Dan Telekomunikasi',
    ]);
});

test('super admin can toggle active status of jurusan', function () {
    $jurusan = Jurusan::factory()->create([
        'school_id' => $this->schoolSmk->id,
        'kode' => 'RPL',
        'is_active' => true,
    ]);

    // Deactivate
    $response = $this->actingAs($this->superAdmin)->post(route('master.data-jurusan.toggle-status', $jurusan));
    $response->assertRedirect(route('master.data-jurusan', ['school_id' => $this->schoolSmk->id]));
    $response->assertSessionHas('success');
    expect($jurusan->fresh()->is_active)->toBeFalse();

    // Reactivate
    $response = $this->actingAs($this->superAdmin)->post(route('master.data-jurusan.toggle-status', $jurusan));
    $response->assertRedirect(route('master.data-jurusan', ['school_id' => $this->schoolSmk->id]));
    $response->assertSessionHas('success');
    expect($jurusan->fresh()->is_active)->toBeTrue();
});

test('super admin can delete jurusan', function () {
    $jurusan = Jurusan::factory()->create([
        'school_id' => $this->schoolSmk->id,
        'kode' => 'RPL',
        'nama' => 'Rekayasa Perangkat Lunak',
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(route('master.data-jurusan.destroy', $jurusan));

    $response->assertRedirect(route('master.data-jurusan', ['school_id' => $this->schoolSmk->id]));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('jurusan', [
        'id' => $jurusan->id,
    ]);
});
