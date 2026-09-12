<?php

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Provinsi;
use App\Models\School;
use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_kecamatan_tester',
        'email' => 'superadmin_kecamatan@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_kecamatan_tester',
        'email' => 'guru_kecamatan@infora.test',
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $this->provinsiSulsel = Provinsi::firstOrCreate(
        ['kode' => '73'],
        ['nama' => 'Sulawesi Selatan', 'status' => true]
    );

    $this->kabupatenBone = Kabupaten::firstOrCreate(
        ['kode' => '7308'],
        [
            'provinsi_id' => $this->provinsiSulsel->id,
            'tipe' => 'Kabupaten',
            'nama' => 'Bone',
            'status' => true,
        ]
    );

    $this->kabupatenMaros = Kabupaten::firstOrCreate(
        ['kode' => '7309'],
        [
            'provinsi_id' => $this->provinsiSulsel->id,
            'tipe' => 'Kabupaten',
            'nama' => 'Maros',
            'status' => true,
        ]
    );
});

test('guest is redirected to login when accessing kecamatan management', function () {
    $response = $this->get(route('wilayah.kecamatan'));

    $response->assertRedirect(route('login'));
});

test('non super admin cannot access kecamatan management', function () {
    $response = $this->actingAs($this->regularUser)->get(route('wilayah.kecamatan'));

    $response->assertForbidden();
});

test('super admin can view kecamatan list and see modals and seeded data', function () {
    $kecamatan = Kecamatan::firstOrCreate(
        ['kode' => '730821'],
        [
            'kabupaten_id' => $this->kabupatenBone->id,
            'nama' => 'Tanete Riattang',
            'status' => true,
        ]
    );

    $response = $this->actingAs($this->superAdmin)->get(route('wilayah.kecamatan'));

    $response->assertOk();
    $response->assertSee('Wilayah Kecamatan');
    $response->assertSee('730821');
    $response->assertSee('Kecamatan Tanete Riattang');
    $response->assertSee('modalCreateKecamatan', false);
    $response->assertSee('modalEditKecamatan', false);
    $response->assertSee('modalDeleteKecamatan', false);
});

test('super admin can search kecamatan by kode, nama, or kabupaten', function () {
    Kecamatan::firstOrCreate(
        ['kode' => '730801'],
        [
            'kabupaten_id' => $this->kabupatenBone->id,
            'nama' => 'Bontocani',
            'status' => true,
        ]
    );

    Kecamatan::firstOrCreate(
        ['kode' => '730901'],
        [
            'kabupaten_id' => $this->kabupatenMaros->id,
            'nama' => 'Mandai',
            'status' => true,
        ]
    );

    // Search by name
    $responseSearchName = $this->actingAs($this->superAdmin)->get(route('wilayah.kecamatan', ['search' => 'Bontocani']));
    $responseSearchName->assertOk();
    $responseSearchName->assertSee('Kecamatan Bontocani');
    $responseSearchName->assertDontSee('Kecamatan Mandai');

    // Search by Kemendagri code (730901)
    $responseSearchCode = $this->actingAs($this->superAdmin)->get(route('wilayah.kecamatan', ['search' => '730901']));
    $responseSearchCode->assertOk();
    $responseSearchCode->assertSee('Kecamatan Mandai');
    $responseSearchCode->assertDontSee('Kecamatan Bontocani');
});

test('super admin can filter kecamatan by kabupaten', function () {
    Kecamatan::firstOrCreate(
        ['kode' => '730802'],
        [
            'kabupaten_id' => $this->kabupatenBone->id,
            'nama' => 'Kahu',
            'status' => true,
        ]
    );

    Kecamatan::firstOrCreate(
        ['kode' => '730902'],
        [
            'kabupaten_id' => $this->kabupatenMaros->id,
            'nama' => 'Turikale',
            'status' => true,
        ]
    );

    $response = $this->actingAs($this->superAdmin)->get(route('wilayah.kecamatan', [
        'kabupaten_id' => $this->kabupatenBone->id,
    ]));

    $response->assertOk();
    $response->assertSee('Kecamatan Kahu');
    $response->assertDontSee('Kecamatan Turikale');
});

test('super admin can create a new kecamatan with valid data', function () {
    $payload = [
        'provinsi_id' => $this->provinsiSulsel->id,
        'kabupaten_id' => $this->kabupatenBone->id,
        'kode' => '730899',
        'nama' => 'Kecamatan Uji Coba',
        'status' => '1',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('wilayah.kecamatan.store'), $payload);

    $response->assertRedirect(route('wilayah.kecamatan'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('kecamatan', [
        'kabupaten_id' => $this->kabupatenBone->id,
        'kode' => '730899',
        'nama' => 'Kecamatan Uji Coba',
        'status' => true,
    ]);
});

test('creation fails when kode is not 6 digits or does not match kabupaten code', function () {
    // Kurang dari 6 digit
    $responseShort = $this->actingAs($this->superAdmin)->post(route('wilayah.kecamatan.store'), [
        'provinsi_id' => $this->provinsiSulsel->id,
        'kabupaten_id' => $this->kabupatenBone->id,
        'kode' => '73081',
        'nama' => 'Test Pendek',
    ]);
    $responseShort->assertSessionHasErrors('kode');

    // Awalan tidak sesuai kabupaten (Bone adalah 7308, ini diberi 7309)
    $responseMismatch = $this->actingAs($this->superAdmin)->post(route('wilayah.kecamatan.store'), [
        'provinsi_id' => $this->provinsiSulsel->id,
        'kabupaten_id' => $this->kabupatenBone->id,
        'kode' => '730999',
        'nama' => 'Test Mismatch',
    ]);
    $responseMismatch->assertSessionHasErrors('kode');
});

test('creation fails when kabupaten does not belong to selected provinsi', function () {
    $provinsiJatim = Provinsi::firstOrCreate(
        ['kode' => '35'],
        ['nama' => 'Jawa Timur', 'status' => true]
    );

    $response = $this->actingAs($this->superAdmin)->post(route('wilayah.kecamatan.store'), [
        'provinsi_id' => $provinsiJatim->id, // Mismatch provinsi
        'kabupaten_id' => $this->kabupatenBone->id, // Milik Sulsel (73)
        'kode' => '730898',
        'nama' => 'Test Hierarchy',
    ]);

    $response->assertSessionHasErrors('kabupaten_id');
});

test('creation fails when kode is duplicate', function () {
    Kecamatan::firstOrCreate(
        ['kode' => '730803'],
        [
            'kabupaten_id' => $this->kabupatenBone->id,
            'nama' => 'Kajuara',
            'status' => true,
        ]
    );

    $response = $this->actingAs($this->superAdmin)->post(route('wilayah.kecamatan.store'), [
        'provinsi_id' => $this->provinsiSulsel->id,
        'kabupaten_id' => $this->kabupatenBone->id,
        'kode' => '730803',
        'nama' => 'Kajuara Duplikat',
    ]);

    $response->assertSessionHasErrors('kode');
});

test('super admin can update an existing kecamatan', function () {
    $kecamatan = Kecamatan::firstOrCreate(
        ['kode' => '730804'],
        [
            'kabupaten_id' => $this->kabupatenBone->id,
            'nama' => 'Salomekko',
            'status' => true,
        ]
    );

    $response = $this->actingAs($this->superAdmin)->put(route('wilayah.kecamatan.update', $kecamatan), [
        'provinsi_id' => $this->provinsiSulsel->id,
        'kabupaten_id' => $this->kabupatenBone->id,
        'kode' => '730804',
        'nama' => 'Salomekko Update',
        'status' => '0',
    ]);

    $response->assertRedirect(route('wilayah.kecamatan'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('kecamatan', [
        'id' => $kecamatan->id,
        'nama' => 'Salomekko Update',
        'status' => false,
    ]);
});

test('super admin cannot delete kecamatan linked to an active school', function () {
    $kecamatan = Kecamatan::firstOrCreate(
        ['kode' => '730805'],
        [
            'kabupaten_id' => $this->kabupatenBone->id,
            'nama' => 'Tonra',
            'status' => true,
        ]
    );

    School::factory()->create([
        'district' => 'Tonra',
        'city' => 'Bone',
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(route('wilayah.kecamatan.destroy', $kecamatan));

    $response->assertRedirect(route('wilayah.kecamatan'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('kecamatan', [
        'id' => $kecamatan->id,
    ]);
});

test('super admin can delete kecamatan not linked to any school', function () {
    $kecamatan = Kecamatan::create([
        'kabupaten_id' => $this->kabupatenBone->id,
        'kode' => '730897',
        'nama' => 'Kecamatan Terhapus',
        'status' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(route('wilayah.kecamatan.destroy', $kecamatan));

    $response->assertRedirect(route('wilayah.kecamatan'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('kecamatan', [
        'id' => $kecamatan->id,
    ]);
});

test('edit route redirects to index', function () {
    $kecamatan = Kecamatan::firstOrCreate(
        ['kode' => '730806'],
        [
            'kabupaten_id' => $this->kabupatenBone->id,
            'nama' => 'Libureng',
            'status' => true,
        ]
    );

    $response = $this->actingAs($this->superAdmin)->get(route('wilayah.kecamatan.edit', $kecamatan));

    $response->assertRedirect(route('wilayah.kecamatan'));
});
