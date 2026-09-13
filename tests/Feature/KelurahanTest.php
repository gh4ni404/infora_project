<?php

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Provinsi;
use App\Models\School;
use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_kelurahan_tester',
        'email' => 'superadmin_kelurahan@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_kelurahan_tester',
        'email' => 'guru_kelurahan@infora.test',
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

    $this->kecamatanTaneteRiattang = Kecamatan::firstOrCreate(
        ['kode' => '730821'],
        [
            'kabupaten_id' => $this->kabupatenBone->id,
            'nama' => 'Tanete Riattang',
            'status' => true,
        ]
    );

    $this->kecamatanTaneteRiattangBarat = Kecamatan::firstOrCreate(
        ['kode' => '730822'],
        [
            'kabupaten_id' => $this->kabupatenBone->id,
            'nama' => 'Tanete Riattang Barat',
            'status' => true,
        ]
    );
});

test('guest is redirected to login when accessing kelurahan management', function () {
    $response = $this->get(route('wilayah.kelurahan'));

    $response->assertRedirect(route('login'));
});

test('non super admin cannot access kelurahan management', function () {
    $response = $this->actingAs($this->regularUser)->get(route('wilayah.kelurahan'));

    $response->assertForbidden();
});

test('super admin can view kelurahan list and see modals and seeded elements', function () {
    $kelurahan = Kelurahan::firstOrCreate(
        ['kode' => '7308211001'],
        [
            'kecamatan_id' => $this->kecamatanTaneteRiattang->id,
            'tipe' => 'Kelurahan',
            'nama' => 'Biru',
            'kode_pos' => '92711',
            'status' => true,
        ]
    );

    $response = $this->actingAs($this->superAdmin)->get(route('wilayah.kelurahan'));

    $response->assertOk();
    $response->assertSee('Wilayah Kelurahan');
    $response->assertSee('7308211001');
    $response->assertSee('Biru');
    $response->assertSee('92711');
    $response->assertSee('modalCreateKelurahan', false);
    $response->assertSee('modalEditKelurahan', false);
    $response->assertSee('modalDeleteKelurahan', false);
});

test('super admin can search kelurahan by kode, nama, or kode pos', function () {
    Kelurahan::firstOrCreate(
        ['kode' => '7308211003'],
        [
            'kecamatan_id' => $this->kecamatanTaneteRiattang->id,
            'tipe' => 'Kelurahan',
            'nama' => 'Masumpu',
            'kode_pos' => '92718',
            'status' => true,
        ]
    );

    Kelurahan::firstOrCreate(
        ['kode' => '7308221003'],
        [
            'kecamatan_id' => $this->kecamatanTaneteRiattangBarat->id,
            'tipe' => 'Kelurahan',
            'nama' => 'Macanang',
            'kode_pos' => '92732',
            'status' => true,
        ]
    );

    // Search by name
    $resName = $this->actingAs($this->superAdmin)->get(route('wilayah.kelurahan', ['search' => 'Masumpu']));
    $resName->assertOk();
    $resName->assertSee('Masumpu');
    $resName->assertDontSee('Macanang');

    // Search by postal code
    $resPos = $this->actingAs($this->superAdmin)->get(route('wilayah.kelurahan', ['search' => '92732']));
    $resPos->assertOk();
    $resPos->assertSee('Macanang');
    $resPos->assertDontSee('Masumpu');
});

test('super admin can filter kelurahan by tipe', function () {
    Kelurahan::firstOrCreate(
        ['kode' => '7308211004'],
        [
            'kecamatan_id' => $this->kecamatanTaneteRiattang->id,
            'tipe' => 'Kelurahan',
            'nama' => 'Manurunge',
            'kode_pos' => '92712',
            'status' => true,
        ]
    );

    $desa = Kelurahan::firstOrCreate(
        ['kode' => '7308212005'],
        [
            'kecamatan_id' => $this->kecamatanTaneteRiattang->id,
            'tipe' => 'Desa',
            'nama' => 'Pappolo Testing Desa',
            'kode_pos' => '92717',
            'status' => true,
        ]
    );

    $resKel = $this->actingAs($this->superAdmin)->get(route('wilayah.kelurahan', ['tipe' => 'Kelurahan']));
    $resKel->assertOk();
    $resKel->assertSee('Manurunge');
    $resKel->assertDontSee('Pappolo Testing Desa');

    $resDesa = $this->actingAs($this->superAdmin)->get(route('wilayah.kelurahan', ['tipe' => 'Desa']));
    $resDesa->assertOk();
    $resDesa->assertSee('Pappolo Testing Desa');
    $resDesa->assertDontSee('Manurunge');
});

test('super admin can filter kelurahan by kecamatan', function () {
    Kelurahan::firstOrCreate(
        ['kode' => '7308211006'],
        [
            'kecamatan_id' => $this->kecamatanTaneteRiattang->id,
            'tipe' => 'Kelurahan',
            'nama' => 'Ta',
            'status' => true,
        ]
    );

    Kelurahan::firstOrCreate(
        ['kode' => '7308221004'],
        [
            'kecamatan_id' => $this->kecamatanTaneteRiattangBarat->id,
            'tipe' => 'Kelurahan',
            'nama' => 'Majang',
            'status' => true,
        ]
    );

    $resKec = $this->actingAs($this->superAdmin)->get(route('wilayah.kelurahan', [
        'kecamatan_id' => $this->kecamatanTaneteRiattang->id,
    ]));

    $resKec->assertOk();
    $resKec->assertSee('Ta');
    $resKec->assertDontSee('Majang');
});

test('super admin can create a new kelurahan with valid data', function () {
    $payload = [
        'provinsi_id' => $this->provinsiSulsel->id,
        'kabupaten_id' => $this->kabupatenBone->id,
        'kecamatan_id' => $this->kecamatanTaneteRiattang->id,
        'tipe' => 'Kelurahan',
        'kode' => '7308211099',
        'nama' => 'Kelurahan Baru Infora',
        'kode_pos' => '92719',
        'status' => '1',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('wilayah.kelurahan.store'), $payload);

    $response->assertRedirect(route('wilayah.kelurahan'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('kelurahan', [
        'kode' => '7308211099',
        'tipe' => 'Kelurahan',
        'nama' => 'Kelurahan Baru Infora',
        'kode_pos' => '92719',
        'status' => true,
    ]);
});

test('validation fails if kode does not match kecamatan prefix or length is invalid', function () {
    $payload = [
        'provinsi_id' => $this->provinsiSulsel->id,
        'kabupaten_id' => $this->kabupatenBone->id,
        'kecamatan_id' => $this->kecamatanTaneteRiattang->id, // 730821
        'tipe' => 'Kelurahan',
        'kode' => '7308991001', // Invalid prefix, does not start with 730821
        'nama' => 'Kelurahan Salah Prefix',
        'status' => '1',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('wilayah.kelurahan.store'), $payload);

    $response->assertSessionHasErrors(['kode']);
});

test('validation fails if kecamatan does not belong to selected kabupaten', function () {
    $kecamatanMaros = Kecamatan::firstOrCreate(
        ['kode' => '730901'],
        [
            'kabupaten_id' => $this->kabupatenMaros->id,
            'nama' => 'Mandai',
            'status' => true,
        ]
    );

    $payload = [
        'provinsi_id' => $this->provinsiSulsel->id,
        'kabupaten_id' => $this->kabupatenBone->id, // Bone
        'kecamatan_id' => $kecamatanMaros->id,       // Under Maros!
        'tipe' => 'Kelurahan',
        'kode' => '7309011001',
        'nama' => 'Kelurahan Silang Relasi',
        'status' => '1',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('wilayah.kelurahan.store'), $payload);

    $response->assertSessionHasErrors(['kecamatan_id']);
});

test('super admin can update an existing kelurahan', function () {
    $kelurahan = Kelurahan::firstOrCreate(
        ['kode' => '7308211088'],
        [
            'kecamatan_id' => $this->kecamatanTaneteRiattang->id,
            'tipe' => 'Kelurahan',
            'nama' => 'Nama Awal',
            'kode_pos' => '92711',
            'status' => true,
        ]
    );

    $payload = [
        'provinsi_id' => $this->provinsiSulsel->id,
        'kabupaten_id' => $this->kabupatenBone->id,
        'kecamatan_id' => $this->kecamatanTaneteRiattang->id,
        'tipe' => 'Kelurahan',
        'kode' => '7308211088',
        'nama' => 'Nama Terkini Diperbarui',
        'kode_pos' => '92715',
        'status' => '0',
    ];

    $response = $this->actingAs($this->superAdmin)->put(route('wilayah.kelurahan.update', $kelurahan), $payload);

    $response->assertRedirect(route('wilayah.kelurahan'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('kelurahan', [
        'id' => $kelurahan->id,
        'nama' => 'Nama Terkini Diperbarui',
        'kode_pos' => '92715',
        'status' => false,
    ]);
});

test('super admin cannot delete kelurahan that is linked to active school', function () {
    $kelurahan = Kelurahan::firstOrCreate(
        ['kode' => '7308211077'],
        [
            'kecamatan_id' => $this->kecamatanTaneteRiattang->id,
            'tipe' => 'Kelurahan',
            'nama' => 'Watampone Sekolah',
            'status' => true,
        ]
    );

    School::factory()->create([
        'village' => 'Watampone Sekolah',
        'district' => 'Tanete Riattang',
        'city' => 'Bone',
        'province' => 'Sulawesi Selatan',
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(route('wilayah.kelurahan.destroy', $kelurahan));

    $response->assertRedirect(route('wilayah.kelurahan'));
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('kelurahan', ['id' => $kelurahan->id]);
});

test('super admin can delete kelurahan that is not linked to school', function () {
    $kelurahan = Kelurahan::firstOrCreate(
        ['kode' => '7308211066'],
        [
            'kecamatan_id' => $this->kecamatanTaneteRiattang->id,
            'tipe' => 'Kelurahan',
            'nama' => 'Kelurahan Hapus Target',
            'status' => true,
        ]
    );

    $response = $this->actingAs($this->superAdmin)->delete(route('wilayah.kelurahan.destroy', $kelurahan));

    $response->assertRedirect(route('wilayah.kelurahan'));
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('kelurahan', ['id' => $kelurahan->id]);
});
