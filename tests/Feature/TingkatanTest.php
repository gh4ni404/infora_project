<?php

use App\Models\School;
use App\Models\Tingkatan;
use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_tingkatan_test',
        'email' => 'superadmin_tingkatan@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->schoolA = School::factory()->smk()->create([
        'name' => 'SMK Negeri 1 Maju',
        'is_active' => true,
    ]);

    $this->schoolB = School::factory()->sma()->create([
        'name' => 'SMA Negeri 2 Unggul',
        'is_active' => true,
    ]);

    $this->adminSekolahA = User::factory()->create([
        'username' => 'admin_sekolah_a',
        'email' => 'admin_a@infora.test',
        'user_type' => 'admin',
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_tingkatan_test',
        'email' => 'guru_tingkatan@infora.test',
        'user_type' => 'guru',
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);
});

test('guest is redirected to login when accessing master data tingkatan', function () {
    $response = $this->get(route('master.data-tingkatan'));

    $response->assertRedirect(route('login'));
});

test('unauthorized regular user without menu permission cannot access master data tingkatan', function () {
    $response = $this->actingAs($this->regularUser)->get(route('master.data-tingkatan'));

    $response->assertForbidden();
});

test('super admin can view master data tingkatan page and kpi cards', function () {
    Tingkatan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'X',
        'nama' => 'Kelas 10',
        'tingkat_angka' => 10,
        'fase' => 'Fase E',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-tingkatan.index', [
        'school_id' => $this->schoolA->id,
    ]));

    $response->assertOk();
    $response->assertSee('Master Data Tingkatan');
    $response->assertSee('SMK Negeri 1 Maju');
    $response->assertSee('Kelas 10');
    $response->assertSee('Fase E');
    $response->assertSee('modalCreateTingkatan');
    $response->assertSee('modalEditTingkatan');
    $response->assertSee('modalDeleteTingkatan');
});

test('school selector filters tingkatan list correctly for super admin', function () {
    $tingkatanA = Tingkatan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'X',
        'nama' => 'Tingkat Sepuluh SMK',
        'tingkat_angka' => 10,
    ]);

    $tingkatanB = Tingkatan::factory()->create([
        'school_id' => $this->schoolB->id,
        'kode' => 'XI',
        'nama' => 'Tingkat Sebelas SMA',
        'tingkat_angka' => 11,
    ]);

    // View School A
    $responseA = $this->actingAs($this->superAdmin)->get(route('master.data-tingkatan', [
        'school_id' => $this->schoolA->id,
    ]));
    $responseA->assertOk();
    $responseA->assertSee('Tingkat Sepuluh SMK');
    $responseA->assertDontSee('Tingkat Sebelas SMA');

    // View School B
    $responseB = $this->actingAs($this->superAdmin)->get(route('master.data-tingkatan', [
        'school_id' => $this->schoolB->id,
    ]));
    $responseB->assertOk();
    $responseB->assertSee('Tingkat Sebelas SMA');
    $responseB->assertDontSee('Tingkat Sepuluh SMK');
});

test('search filter works accurately for kode, nama, and tingkat_angka', function () {
    Tingkatan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'X',
        'nama' => 'Kelas Sepuluh Kejuruan',
        'tingkat_angka' => 10,
        'fase' => 'Fase E',
    ]);

    Tingkatan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'XII',
        'nama' => 'Kelas Dua Belas Akhir',
        'tingkat_angka' => 12,
        'fase' => 'Fase F',
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-tingkatan', [
        'school_id' => $this->schoolA->id,
        'search' => 'Sepuluh',
    ]));

    $response->assertOk();
    $response->assertSee('Kelas Sepuluh Kejuruan');
    $response->assertDontSee('Kelas Dua Belas Akhir');
});

test('status filter returns matching tingkatan', function () {
    Tingkatan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'X',
        'nama' => 'Kelas 10 Aktif',
        'tingkat_angka' => 10,
        'is_active' => true,
    ]);

    Tingkatan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'XIII',
        'nama' => 'Kelas 13 Nonaktif',
        'tingkat_angka' => 13,
        'is_active' => false,
    ]);

    // Filter Aktif
    $responseActive = $this->actingAs($this->superAdmin)->get(route('master.data-tingkatan', [
        'school_id' => $this->schoolA->id,
        'status' => 'aktif',
    ]));
    $responseActive->assertOk();
    $responseActive->assertSee('Kelas 10 Aktif');
    $responseActive->assertDontSee('Kelas 13 Nonaktif');

    // Filter Nonaktif
    $responseInactive = $this->actingAs($this->superAdmin)->get(route('master.data-tingkatan', [
        'school_id' => $this->schoolA->id,
        'status' => 'nonaktif',
    ]));
    $responseInactive->assertOk();
    $responseInactive->assertSee('Kelas 13 Nonaktif');
    $responseInactive->assertDontSee('Kelas 10 Aktif');
});

test('validation fails when storing tingkatan without required fields', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('master.data-tingkatan.store'), [
        'school_id' => $this->schoolA->id,
        'kode' => '',
        'nama' => '',
        'tingkat_angka' => '',
    ]);

    $response->assertSessionHasErrors(['kode', 'nama', 'tingkat_angka']);
});

test('validation fails when storing duplicate kode in same school', function () {
    Tingkatan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'X',
        'tingkat_angka' => 10,
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-tingkatan.store'), [
        'school_id' => $this->schoolA->id,
        'kode' => 'X',
        'nama' => 'Kelas Sepuluh Duplikat',
        'tingkat_angka' => 11,
    ]);

    $response->assertSessionHasErrors(['kode']);
});

test('validation fails when storing duplicate tingkat_angka in same school', function () {
    Tingkatan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'X',
        'tingkat_angka' => 10,
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-tingkatan.store'), [
        'school_id' => $this->schoolA->id,
        'kode' => 'SEPULUH',
        'nama' => 'Kelas Sepuluh Duplikat Angka',
        'tingkat_angka' => 10,
    ]);

    $response->assertSessionHasErrors(['tingkat_angka']);
});

test('can store same kode in different schools (multi-tenancy isolation)', function () {
    Tingkatan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'X',
        'tingkat_angka' => 10,
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-tingkatan.store'), [
        'school_id' => $this->schoolB->id,
        'kode' => 'X',
        'nama' => 'Kelas 10 SMA',
        'tingkat_angka' => 10,
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('master.data-tingkatan.index', ['school_id' => $this->schoolB->id]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('tingkatan', [
        'school_id' => $this->schoolB->id,
        'kode' => 'X',
        'tingkat_angka' => 10,
    ]);
});

test('super admin can store new tingkatan with proper text normalization', function () {
    $payload = [
        'school_id' => $this->schoolA->id,
        'kode' => 'xi',
        'nama' => 'kelas sebelas kejuruan',
        'tingkat_angka' => '11',
        'fase' => 'fase f',
        'is_active' => '1',
        'keterangan' => 'Tingkatan menengah SMK.',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-tingkatan.store'), $payload);

    $response->assertRedirect(route('master.data-tingkatan.index', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('tingkatan', [
        'school_id' => $this->schoolA->id,
        'kode' => 'XI', // UPPERCASE
        'nama' => 'Kelas Sebelas Kejuruan', // Title Case
        'tingkat_angka' => 11,
        'fase' => 'Fase F', // Title Case
        'is_active' => true,
    ]);
});

test('super admin can update existing tingkatan', function () {
    $tingkatan = Tingkatan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'X',
        'nama' => 'Kelas 10',
        'tingkat_angka' => 10,
        'fase' => 'Fase E',
        'is_active' => true,
    ]);

    $payload = [
        'school_id' => $this->schoolA->id,
        'kode' => 'X-BARU',
        'nama' => 'Kelas 10 Peminatan Baru',
        'tingkat_angka' => '10',
        'fase' => 'Fase E Baru',
        'is_active' => '1',
        'keterangan' => 'Keterangan diperbarui.',
    ];

    $response = $this->actingAs($this->superAdmin)->put(route('master.data-tingkatan.update', $tingkatan), $payload);

    $response->assertRedirect(route('master.data-tingkatan.index', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('tingkatan', [
        'id' => $tingkatan->id,
        'kode' => 'X-BARU',
        'nama' => 'Kelas 10 Peminatan Baru',
    ]);
});

test('non superadmin user cannot update tingkatan belonging to another school', function () {
    $tingkatanB = Tingkatan::factory()->create([
        'school_id' => $this->schoolB->id,
        'kode' => 'XII',
        'nama' => 'Kelas 12',
        'tingkat_angka' => 12,
    ]);

    $payload = [
        'school_id' => $this->schoolB->id,
        'kode' => 'XII-HACK',
        'nama' => 'Hacked Name',
        'tingkat_angka' => '12',
    ];

    $response = $this->actingAs($this->adminSekolahA)->put(route('master.data-tingkatan.update', $tingkatanB), $payload);

    $response->assertForbidden();
});

test('super admin can toggle active status of tingkatan', function () {
    $tingkatan = Tingkatan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'X',
        'is_active' => true,
    ]);

    // Deactivate
    $response = $this->actingAs($this->superAdmin)->post(route('master.data-tingkatan.toggle-status', $tingkatan));
    $response->assertRedirect(route('master.data-tingkatan.index', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');
    expect($tingkatan->fresh()->is_active)->toBeFalse();

    // Reactivate
    $response = $this->actingAs($this->superAdmin)->post(route('master.data-tingkatan.toggle-status', $tingkatan));
    $response->assertRedirect(route('master.data-tingkatan.index', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');
    expect($tingkatan->fresh()->is_active)->toBeTrue();
});

test('super admin can delete tingkatan', function () {
    $tingkatan = Tingkatan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'X',
        'nama' => 'Kelas 10',
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(route('master.data-tingkatan.destroy', $tingkatan));

    $response->assertRedirect(route('master.data-tingkatan.index', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('tingkatan', [
        'id' => $tingkatan->id,
    ]);
});

test('non superadmin user cannot delete tingkatan belonging to another school', function () {
    $tingkatanB = Tingkatan::factory()->create([
        'school_id' => $this->schoolB->id,
        'kode' => 'XII',
        'nama' => 'Kelas 12',
    ]);

    $response = $this->actingAs($this->adminSekolahA)->delete(route('master.data-tingkatan.destroy', $tingkatanB));

    $response->assertForbidden();
});
