<?php

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Provinsi;
use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_dropdown_tester',
        'email' => 'superadmin_dropdown@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);
});

test('guest is redirected to login when accessing wilayah dropdown endpoints', function () {
    $this->get(route('wilayah.dropdown.provinsi'))->assertRedirect(route('login'));
    $this->get(route('wilayah.dropdown.kabupaten'))->assertRedirect(route('login'));
    $this->get(route('wilayah.dropdown.kecamatan'))->assertRedirect(route('login'));
    $this->get(route('wilayah.dropdown.kelurahan'))->assertRedirect(route('login'));
});

test('super admin can fetch active provinsi list as json', function () {
    Provinsi::factory()->create([
        'kode' => '73',
        'nama' => 'Sulawesi Selatan',
        'status' => true,
    ]);

    Provinsi::factory()->create([
        'kode' => '74',
        'nama' => 'Sulawesi Tenggara',
        'status' => false,
    ]);

    $response = $this->actingAs($this->superAdmin)->getJson(route('wilayah.dropdown.provinsi'));

    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment([
        'kode' => '73',
        'nama' => 'Sulawesi Selatan',
    ]);
    $response->assertJsonMissing([
        'kode' => '74',
    ]);
});

test('super admin can fetch kabupaten list filtered by provinsi_id', function () {
    $prov1 = Provinsi::factory()->create(['kode' => '73', 'nama' => 'Sulawesi Selatan']);
    $prov2 = Provinsi::factory()->create(['kode' => '74', 'nama' => 'Sulawesi Tenggara']);

    $kab1 = Kabupaten::factory()->create([
        'provinsi_id' => $prov1->id,
        'kode' => '73.08',
        'nama' => 'Bone',
        'tipe' => 'Kabupaten',
        'status' => true,
    ]);

    Kabupaten::factory()->create([
        'provinsi_id' => $prov2->id,
        'kode' => '74.01',
        'nama' => 'Kolaka',
        'status' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->getJson(route('wilayah.dropdown.kabupaten', [
        'provinsi_id' => $prov1->id,
    ]));

    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment([
        'id' => $kab1->id,
        'nama' => 'Bone',
    ]);
    $response->assertJsonMissing([
        'nama' => 'Kolaka',
    ]);
});

test('super admin can fetch kecamatan list filtered by kabupaten_id', function () {
    $prov = Provinsi::factory()->create();
    $kab1 = Kabupaten::factory()->create(['provinsi_id' => $prov->id]);
    $kab2 = Kabupaten::factory()->create(['provinsi_id' => $prov->id]);

    $kec1 = Kecamatan::factory()->create([
        'kabupaten_id' => $kab1->id,
        'kode' => '73.08.01',
        'nama' => 'Ajangale',
        'status' => true,
    ]);

    Kecamatan::factory()->create([
        'kabupaten_id' => $kab2->id,
        'kode' => '73.09.01',
        'nama' => 'Kecamatan Lain',
        'status' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->getJson(route('wilayah.dropdown.kecamatan', [
        'kabupaten_id' => $kab1->id,
    ]));

    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment([
        'id' => $kec1->id,
        'nama' => 'Ajangale',
    ]);
});

test('super admin can fetch kelurahan list with postal code filtered by kecamatan_id', function () {
    $prov = Provinsi::factory()->create();
    $kab = Kabupaten::factory()->create(['provinsi_id' => $prov->id]);
    $kec = Kecamatan::factory()->create(['kabupaten_id' => $kab->id]);

    $kel = Kelurahan::factory()->create([
        'kecamatan_id' => $kec->id,
        'kode' => '73.08.01.2001',
        'nama' => 'Welado',
        'tipe' => 'Desa',
        'kode_pos' => '92755',
        'status' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->getJson(route('wilayah.dropdown.kelurahan', [
        'kecamatan_id' => $kec->id,
    ]));

    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment([
        'id' => $kel->id,
        'nama' => 'Welado',
        'kode_pos' => '92755',
    ]);
});
