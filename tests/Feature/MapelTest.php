<?php

use App\Models\Jurusan;
use App\Models\Mapel;
use App\Models\School;
use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_mapel_test',
        'email' => 'superadmin_mapel@infora.test',
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
        'username' => 'admin_sekolah_mapel_a',
        'email' => 'admin_mapel_a@infora.test',
        'user_type' => 'admin',
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_mapel_test',
        'email' => 'guru_mapel@infora.test',
        'user_type' => 'guru',
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);
});

test('guest is redirected to login when accessing master data mapel', function () {
    $response = $this->get(route('master.data-mapel'));

    $response->assertRedirect(route('login'));
});

test('unauthorized regular user without menu permission cannot access master data mapel', function () {
    $response = $this->actingAs($this->regularUser)->get(route('master.data-mapel'));

    $response->assertForbidden();
});

test('super admin can view master data mapel page and kpi cards', function () {
    Mapel::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'MAT',
        'nama' => 'Matematika Umum',
        'singkatan' => 'MTK',
        'kelompok' => 'Umum',
        'beban_jam' => 4,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-mapel.index', [
        'school_id' => $this->schoolA->id,
    ]));

    $response->assertOk();
    $response->assertSee('Master Data Mata Pelajaran');
    $response->assertSee('SMK Negeri 1 Maju');
    $response->assertSee('MAT');
    $response->assertSee('Matematika Umum');
    $response->assertSee('4 JP');
    $response->assertSee('modalCreateMapel');
    $response->assertSee('modalEditMapel');
    $response->assertSee('modalDeleteMapel');
});

test('single-school mode keeps displaying active school mapel and hides school selector', function () {
    $mapelA = Mapel::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'MAT-A',
        'nama' => 'Matematika SMK A',
    ]);

    $mapelB = Mapel::factory()->create([
        'school_id' => $this->schoolB->id,
        'kode' => 'MAT-B',
        'nama' => 'Matematika SMA B',
    ]);

    $responseB = $this->actingAs($this->superAdmin)->get(route('master.data-mapel', [
        'school_id' => $this->schoolB->id,
    ]));
    $responseB->assertOk();
    $responseB->assertSee('Matematika SMK A');
    $responseB->assertDontSee('Matematika SMA B');
    $responseB->assertDontSee('id="schoolFilterForm"', false);
});

test('search filter works accurately for kode, nama, singkatan, and keterangan', function () {
    Mapel::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'BIN',
        'nama' => 'Bahasa Indonesia Tingkat Lanjut',
        'singkatan' => 'BIND-L',
        'keterangan' => 'Materi sastra dan tata bahasa.',
    ]);

    Mapel::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'BIG',
        'nama' => 'Bahasa Inggris Vokasi',
        'singkatan' => 'BING',
        'keterangan' => 'Percakapan industri.',
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-mapel', [
        'school_id' => $this->schoolA->id,
        'search' => 'Sastra',
    ]));

    $response->assertOk();
    $response->assertSee('Bahasa Indonesia Tingkat Lanjut');
    $response->assertDontSee('Bahasa Inggris Vokasi');
});

test('status filter returns matching mapel', function () {
    Mapel::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'AKTIF',
        'nama' => 'Biologi Terapan Aktif',
        'is_active' => true,
    ]);

    Mapel::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'NON',
        'nama' => 'Astronomi Nonaktif',
        'is_active' => false,
    ]);

    // Filter Aktif
    $responseActive = $this->actingAs($this->superAdmin)->get(route('master.data-mapel', [
        'school_id' => $this->schoolA->id,
        'status' => 'aktif',
    ]));
    $responseActive->assertOk();
    $responseActive->assertSee('Biologi Terapan Aktif');
    $responseActive->assertDontSee('Astronomi Nonaktif');

    // Filter Nonaktif
    $responseInactive = $this->actingAs($this->superAdmin)->get(route('master.data-mapel', [
        'school_id' => $this->schoolA->id,
        'status' => 'nonaktif',
    ]));
    $responseInactive->assertOk();
    $responseInactive->assertSee('Astronomi Nonaktif');
    $responseInactive->assertDontSee('Biologi Terapan Aktif');
});

test('kelompok filter returns matching mapel', function () {
    Mapel::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'UMUM1',
        'nama' => 'Pelajaran Muatan Umum',
        'kelompok' => 'Umum',
    ]);

    Mapel::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'MULOK1',
        'nama' => 'Bahasa Daerah Lokal',
        'kelompok' => 'Muatan Lokal',
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-mapel', [
        'school_id' => $this->schoolA->id,
        'kelompok' => 'Muatan Lokal',
    ]));

    $response->assertOk();
    $response->assertSee('Bahasa Daerah Lokal');
    $response->assertDontSee('Pelajaran Muatan Umum');
});

test('jurusan filter returns matching mapel', function () {
    $jurusanRpl = Jurusan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'RPL',
        'nama' => 'Rekayasa Perangkat Lunak',
    ]);

    $mapelKejuruan = Mapel::factory()->create([
        'school_id' => $this->schoolA->id,
        'jurusan_id' => $jurusanRpl->id,
        'kode' => 'WEB',
        'nama' => 'Pemrograman Web Terapan',
        'kelompok' => 'Kejuruan / Peminatan',
    ]);

    $mapelUmum = Mapel::factory()->create([
        'school_id' => $this->schoolA->id,
        'jurusan_id' => null,
        'kode' => 'PAI',
        'nama' => 'Pendidikan Agama Islam',
        'kelompok' => 'Umum',
    ]);

    // Filter by specific Jurusan
    $responseJurusan = $this->actingAs($this->superAdmin)->get(route('master.data-mapel', [
        'school_id' => $this->schoolA->id,
        'jurusan_id' => $jurusanRpl->id,
    ]));
    $responseJurusan->assertOk();
    $responseJurusan->assertSee('Pemrograman Web Terapan');
    $responseJurusan->assertDontSee('Pendidikan Agama Islam');

    // Filter by Umum (Tanpa Jurusan)
    $responseUmum = $this->actingAs($this->superAdmin)->get(route('master.data-mapel', [
        'school_id' => $this->schoolA->id,
        'jurusan_id' => 'umum',
    ]));
    $responseUmum->assertOk();
    $responseUmum->assertSee('Pendidikan Agama Islam');
    $responseUmum->assertDontSee('Pemrograman Web Terapan');
});

test('validation fails when storing mapel without required fields', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('master.data-mapel.store'), [
        'school_id' => $this->schoolA->id,
        'kode' => '',
        'nama' => '',
        'kelompok' => '',
    ]);

    $response->assertSessionHasErrors(['kode', 'nama', 'kelompok']);
});

test('validation fails when storing invalid kelompok or out of range beban_jam', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('master.data-mapel.store'), [
        'school_id' => $this->schoolA->id,
        'kode' => 'TEST',
        'nama' => 'Test Mapel',
        'kelompok' => 'Kelompok Tidak Valid',
        'beban_jam' => 25, // max 20
    ]);

    $response->assertSessionHasErrors(['kelompok', 'beban_jam']);
});

test('cannot assign jurusan belonging to another school (tenant validation)', function () {
    $jurusanB = Jurusan::factory()->create([
        'school_id' => $this->schoolB->id,
        'kode' => 'IPA',
        'nama' => 'MIPA SMA B',
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-mapel.store'), [
        'school_id' => $this->schoolA->id,
        'jurusan_id' => $jurusanB->id,
        'kode' => 'KIM',
        'nama' => 'Kimia Terapan',
        'kelompok' => 'Kejuruan / Peminatan',
        'beban_jam' => 3,
    ]);

    $response->assertSessionHasErrors(['jurusan_id']);
});

test('super admin can store new mapel with proper text normalization', function () {
    $jurusan = Jurusan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'TKJ',
        'nama' => 'Teknik Komputer dan Jaringan',
    ]);

    $payload = [
        'school_id' => $this->schoolA->id,
        'jurusan_id' => $jurusan->id,
        'kode' => 'jarkom',
        'singkatan' => 'jk',
        'nama' => 'jaringan komputer dan komunikasi data',
        'kelompok' => 'Kejuruan / Peminatan',
        'beban_jam' => '4',
        'is_active' => '1',
        'keterangan' => 'Dasar jaringan TKJ.',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-mapel.store'), $payload);

    $response->assertRedirect(route('master.data-mapel.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('mapel', [
        'school_id' => $this->schoolA->id,
        'jurusan_id' => $jurusan->id,
        'kode' => 'JARKOM', // UPPERCASE
        'singkatan' => 'JK', // UPPERCASE
        'nama' => 'Jaringan Komputer Dan Komunikasi Data', // Title Case
        'kelompok' => 'Kejuruan / Peminatan',
        'beban_jam' => 4,
        'is_active' => true,
    ]);
});

test('can store same kode in same school (non-unique verified)', function () {
    Mapel::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'PROD',
        'nama' => 'Produktif RPL',
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-mapel.store'), [
        'school_id' => $this->schoolA->id,
        'kode' => 'PROD',
        'nama' => 'Produktif TKJ',
        'kelompok' => 'Kejuruan / Peminatan',
        'beban_jam' => 4,
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('master.data-mapel.index'));
    $response->assertSessionHas('success');

    expect(Mapel::where('school_id', $this->schoolA->id)->where('kode', 'PROD')->count())->toBe(2);
});

test('super admin can update existing mapel', function () {
    $mapel = Mapel::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'MAT',
        'nama' => 'Matematika Lama',
        'singkatan' => 'MTK',
        'kelompok' => 'Umum',
        'beban_jam' => 2,
        'is_active' => true,
    ]);

    $payload = [
        'school_id' => $this->schoolA->id,
        'kode' => 'MAT-WAJIB',
        'singkatan' => 'MTK-W',
        'nama' => 'Matematika Wajib Kurikulum Merdeka',
        'kelompok' => 'Umum',
        'beban_jam' => '4',
        'is_active' => '1',
        'keterangan' => 'Alokasi jam ditambah.',
    ];

    $response = $this->actingAs($this->superAdmin)->put(route('master.data-mapel.update', $mapel), $payload);

    $response->assertRedirect(route('master.data-mapel.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('mapel', [
        'id' => $mapel->id,
        'kode' => 'MAT-WAJIB',
        'singkatan' => 'MTK-W',
        'nama' => 'Matematika Wajib Kurikulum Merdeka',
        'beban_jam' => 4,
    ]);
});

test('non superadmin user cannot update mapel belonging to another school (tenant IDOR protection)', function () {
    $mapelB = Mapel::factory()->create([
        'school_id' => $this->schoolB->id,
        'kode' => 'FIS',
        'nama' => 'Fisika SMA B',
    ]);

    $payload = [
        'school_id' => $this->schoolB->id,
        'kode' => 'FIS-HACK',
        'nama' => 'Hacked Physics',
        'kelompok' => 'Umum',
        'beban_jam' => 2,
    ];

    $response = $this->actingAs($this->adminSekolahA)->put(route('master.data-mapel.update', $mapelB), $payload);

    $response->assertForbidden();
});

test('super admin can toggle active status of mapel', function () {
    $mapel = Mapel::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'BIO',
        'is_active' => true,
    ]);

    // Deactivate
    $response = $this->actingAs($this->superAdmin)->post(route('master.data-mapel.toggle-status', $mapel));
    $response->assertRedirect(route('master.data-mapel.index'));
    $response->assertSessionHas('success');
    expect($mapel->fresh()->is_active)->toBeFalse();

    // Reactivate
    $response = $this->actingAs($this->superAdmin)->post(route('master.data-mapel.toggle-status', $mapel));
    $response->assertRedirect(route('master.data-mapel.index'));
    $response->assertSessionHas('success');
    expect($mapel->fresh()->is_active)->toBeTrue();
});

test('super admin can delete mapel', function () {
    $mapel = Mapel::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'DELETE',
        'nama' => 'Mata Pelajaran Dihapus',
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(route('master.data-mapel.destroy', $mapel));

    $response->assertRedirect(route('master.data-mapel.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('mapel', [
        'id' => $mapel->id,
    ]);
});

test('non superadmin user cannot delete mapel belonging to another school', function () {
    $mapelB = Mapel::factory()->create([
        'school_id' => $this->schoolB->id,
        'kode' => 'DEL-B',
        'nama' => 'Mapel Sekolah B',
    ]);

    $response = $this->actingAs($this->adminSekolahA)->delete(route('master.data-mapel.destroy', $mapelB));

    $response->assertForbidden();
});
