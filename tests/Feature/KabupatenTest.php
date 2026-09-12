<?php

use App\Models\Kabupaten;
use App\Models\Provinsi;
use App\Models\School;
use App\Models\SubMenu;
use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_kabupaten_tester',
        'email' => 'superadmin_kabupaten@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_kabupaten_tester',
        'email' => 'guru_kabupaten@infora.test',
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $this->provinsiSulsel = Provinsi::firstOrCreate(
        ['kode' => '73'],
        ['nama' => 'Sulawesi Selatan', 'status' => true]
    );

    $this->provinsiDki = Provinsi::firstOrCreate(
        ['kode' => '31'],
        ['nama' => 'DKI Jakarta', 'status' => true]
    );
});

test('guest is redirected to login when accessing kabupaten management', function () {
    $response = $this->get(route('wilayah.kabupaten'));

    $response->assertRedirect(route('login'));
});

test('non super admin cannot access kabupaten management', function () {
    $response = $this->actingAs($this->regularUser)->get(route('wilayah.kabupaten'));

    $response->assertForbidden();
});

test('super admin can view kabupaten list and see modals and seeded data', function () {
    $kabupaten = Kabupaten::firstOrCreate(
        ['kode' => '7371'],
        [
            'provinsi_id' => $this->provinsiSulsel->id,
            'tipe' => 'Kota',
            'nama' => 'Makassar',
            'status' => true,
        ]
    );

    $response = $this->actingAs($this->superAdmin)->get(route('wilayah.kabupaten'));

    $response->assertOk();
    $response->assertSee('Wilayah Kabupaten');
    $response->assertSee('7371');
    $response->assertSee('Kota Makassar');
    $response->assertSee('modalCreateKabupaten', false);
    $response->assertSee('modalEditKabupaten', false);
    $response->assertSee('modalDeleteKabupaten', false);
});

test('super admin can search kabupaten by kode, nama, or provinsi', function () {
    Kabupaten::firstOrCreate(
        ['kode' => '7309'],
        [
            'provinsi_id' => $this->provinsiSulsel->id,
            'tipe' => 'Kabupaten',
            'nama' => 'Maros',
            'status' => true,
        ]
    );

    Kabupaten::firstOrCreate(
        ['kode' => '3174'],
        [
            'provinsi_id' => $this->provinsiDki->id,
            'tipe' => 'Kota',
            'nama' => 'Jakarta Selatan',
            'status' => true,
        ]
    );

    // Search by name
    $responseSearchName = $this->actingAs($this->superAdmin)->get(route('wilayah.kabupaten', ['search' => 'Maros']));
    $responseSearchName->assertOk();
    $responseSearchName->assertSee('Kabupaten Maros');
    $responseSearchName->assertDontSee('Kota Jakarta Selatan');

    // Search by Kemendagri code (3174 for Jakarta Selatan)
    $responseSearchCode = $this->actingAs($this->superAdmin)->get(route('wilayah.kabupaten', ['search' => '3174']));
    $responseSearchCode->assertOk();
    $responseSearchCode->assertSee('Kota Jakarta Selatan');
    $responseSearchCode->assertDontSee('Kabupaten Maros');
});

test('super admin can filter kabupaten by provinsi and tipe', function () {
    $maros = Kabupaten::firstOrCreate(
        ['kode' => '7309'],
        [
            'provinsi_id' => $this->provinsiSulsel->id,
            'tipe' => 'Kabupaten',
            'nama' => 'Maros',
            'status' => true,
        ]
    );

    $makassar = Kabupaten::firstOrCreate(
        ['kode' => '7371'],
        [
            'provinsi_id' => $this->provinsiSulsel->id,
            'tipe' => 'Kota',
            'nama' => 'Makassar',
            'status' => true,
        ]
    );

    $jaksel = Kabupaten::firstOrCreate(
        ['kode' => '3174'],
        [
            'provinsi_id' => $this->provinsiDki->id,
            'tipe' => 'Kota',
            'nama' => 'Jakarta Selatan',
            'status' => true,
        ]
    );

    // Filter by DKI
    $responseProv = $this->actingAs($this->superAdmin)->get(route('wilayah.kabupaten', ['provinsi_id' => $this->provinsiDki->id]));
    $responseProv->assertOk();
    $responseProv->assertSee('Kota Jakarta Selatan');
    $responseProv->assertDontSee('Kabupaten Maros');

    // Filter by Tipe = Kabupaten
    $responseTipe = $this->actingAs($this->superAdmin)->get(route('wilayah.kabupaten', ['provinsi_id' => $this->provinsiSulsel->id, 'tipe' => 'Kabupaten']));
    $responseTipe->assertOk();
    $responseTipe->assertSee('Kabupaten Maros');
    $responseTipe->assertDontSee('Kota Makassar');
});

test('super admin can create a new kabupaten with matching 2-digit province prefix', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('wilayah.kabupaten.store'), [
        'provinsi_id' => $this->provinsiSulsel->id,
        'tipe' => 'Kabupaten',
        'kode' => '7306',
        'nama' => 'gowa',
        'status' => '1',
    ]);

    $response->assertRedirect(route('wilayah.kabupaten'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('kabupaten', [
        'kode' => '7306',
        'tipe' => 'Kabupaten',
        'nama' => 'Gowa',
        'status' => 1,
    ]);
});

test('validates 2-digit prefix must match selected province code', function () {
    // Province Sulsel has code 73, but user attempts code 3201
    $response = $this->actingAs($this->superAdmin)->post(route('wilayah.kabupaten.store'), [
        'provinsi_id' => $this->provinsiSulsel->id,
        'tipe' => 'Kabupaten',
        'kode' => '3201',
        'nama' => 'Kabupaten Salah Awalan',
        'status' => '1',
    ]);

    $response->assertSessionHasErrors(['kode']);
});

test('validates 4-digit code format and uniqueness on kabupaten creation', function () {
    Kabupaten::firstOrCreate(
        ['kode' => '7309'],
        [
            'provinsi_id' => $this->provinsiSulsel->id,
            'tipe' => 'Kabupaten',
            'nama' => 'Maros',
            'status' => true,
        ]
    );

    // Duplicate code
    $responseDuplicate = $this->actingAs($this->superAdmin)->post(route('wilayah.kabupaten.store'), [
        'provinsi_id' => $this->provinsiSulsel->id,
        'tipe' => 'Kabupaten',
        'kode' => '7309',
        'nama' => 'Maros Duplikat',
    ]);
    $responseDuplicate->assertSessionHasErrors(['kode']);

    // Non 4-digit code
    $responseNon4 = $this->actingAs($this->superAdmin)->post(route('wilayah.kabupaten.store'), [
        'provinsi_id' => $this->provinsiSulsel->id,
        'tipe' => 'Kabupaten',
        'kode' => '730',
        'nama' => 'Tiga Digit',
    ]);
    $responseNon4->assertSessionHasErrors(['kode']);
});

test('super admin can update an existing kabupaten', function () {
    $kabupaten = Kabupaten::firstOrCreate(
        ['kode' => '7399'],
        [
            'provinsi_id' => $this->provinsiSulsel->id,
            'tipe' => 'Kabupaten',
            'nama' => 'Wilayah Percobaan',
            'status' => true,
        ]
    );

    $response = $this->actingAs($this->superAdmin)->put(route('wilayah.kabupaten.update', $kabupaten), [
        'provinsi_id' => $this->provinsiSulsel->id,
        'tipe' => 'Kota',
        'kode' => '7399',
        'nama' => 'wilayah percobaan terupdate',
        'status' => '0',
    ]);

    $response->assertRedirect(route('wilayah.kabupaten'));
    $response->assertSessionHas('success');

    $kabupaten->refresh();
    expect($kabupaten->tipe)->toBe('Kota');
    expect($kabupaten->nama)->toBe('Wilayah Percobaan Terupdate');
    expect($kabupaten->nama_lengkap)->toBe('Kota Wilayah Percobaan Terupdate');
    expect($kabupaten->status)->toBeFalse();
});

test('super admin can delete an unlinked kabupaten', function () {
    $kabupaten = Kabupaten::create([
        'provinsi_id' => $this->provinsiSulsel->id,
        'tipe' => 'Kabupaten',
        'kode' => '7388',
        'nama' => 'Kabupaten Siap Hapus',
        'status' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(route('wilayah.kabupaten.destroy', $kabupaten));

    $response->assertRedirect(route('wilayah.kabupaten'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('kabupaten', [
        'id' => $kabupaten->id,
    ]);
});

test('prevents deleting a kabupaten linked to active school records', function () {
    $kabupaten = Kabupaten::firstOrCreate(
        ['kode' => '7308'],
        [
            'provinsi_id' => $this->provinsiSulsel->id,
            'tipe' => 'Kabupaten',
            'nama' => 'Bone',
            'status' => true,
        ]
    );

    School::factory()->create([
        'name' => 'SMK Negeri 1 Bone',
        'npsn' => '40301112',
        'school_type' => 'SMK',
        'status' => 'Negeri',
        'city' => 'Bone',
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(route('wilayah.kabupaten.destroy', $kabupaten));

    $response->assertRedirect(route('wilayah.kabupaten'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('kabupaten', [
        'id' => $kabupaten->id,
    ]);
});

test('prevents deleting a province that still has child kabupaten records', function () {
    Kabupaten::firstOrCreate(
        ['kode' => '7309'],
        [
            'provinsi_id' => $this->provinsiSulsel->id,
            'tipe' => 'Kabupaten',
            'nama' => 'Maros',
            'status' => true,
        ]
    );

    $response = $this->actingAs($this->superAdmin)->delete(route('wilayah.provinsi.destroy', $this->provinsiSulsel));

    $response->assertRedirect(route('wilayah.provinsi'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('provinsi', [
        'id' => $this->provinsiSulsel->id,
    ]);
});

test('sidebar submenu wilayah.kabupaten resolves to route successfully', function () {
    $submenu = SubMenu::where('route_name', 'wilayah.kabupaten')->first();

    if (! $submenu) {
        $submenu = SubMenu::factory()->create([
            'route_name' => 'wilayah.kabupaten',
            'name' => 'Kabupaten',
        ]);
    }

    expect($submenu->route_url)->toBe(route('wilayah.kabupaten'));
});
