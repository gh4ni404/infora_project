<?php

use App\Models\Gtk;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\School;
use App\Models\TahunAjaran;
use App\Models\Tingkatan;
use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_kelas_test',
        'email' => 'superadmin_kelas@infora.test',
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
        'username' => 'admin_sekolah_kelas_a',
        'email' => 'admin_kelas_a@infora.test',
        'user_type' => 'admin',
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_kelas_test',
        'email' => 'guru_kelas@infora.test',
        'user_type' => 'guru',
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);

    $this->tahunAjaranA = TahunAjaran::factory()->create([
        'school_id' => $this->schoolA->id,
        'tahun' => '2026/2027',
        'is_active' => true,
    ]);

    $this->tingkatanA = Tingkatan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'X',
        'nama' => 'Kelas X',
        'tingkat_angka' => 10,
        'is_active' => true,
    ]);

    $this->jurusanA = Jurusan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'PPLG',
        'nama' => 'Pengembangan Perangkat Lunak dan Gim',
        'singkatan' => 'PPLG',
        'is_active' => true,
    ]);

    $this->gtkA = Gtk::factory()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Budi Santoso',
        'nip' => '198501012010011001',
        'is_active' => true,
    ]);
});

test('guest is redirected to login when accessing data kelas', function () {
    $response = $this->get(route('rombel.kelas.index'));

    $response->assertRedirect(route('login'));
});

test('unauthorized regular user without menu permission cannot access data kelas', function () {
    $response = $this->actingAs($this->regularUser)->get(route('rombel.kelas.index'));

    $response->assertForbidden();
});

test('super admin can view data kelas page, kpi cards, and modal templates', function () {
    $kelas = Kelas::factory()->create([
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'tingkatan_id' => $this->tingkatanA->id,
        'jurusan_id' => $this->jurusanA->id,
        'wali_kelas_id' => $this->gtkA->id,
        'nama' => 'X PPLG 1',
        'kode' => '10-PPLG-1',
        'ruangan' => 'Lab Komputer 1',
        'kapasitas' => 36,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('rombel.kelas.index', [
        'school_id' => $this->schoolA->id,
    ]));

    $response->assertOk();
    $response->assertSee('Data Kelas (Rombongan Belajar)');
    $response->assertSee('SMK Negeri 1 Maju');
    $response->assertSee('X PPLG 1');
    $response->assertSee('10-PPLG-1');
    $response->assertSee('Budi Santoso');
    $response->assertSee('Lab Komputer 1');
    $response->assertSee('36');
    $response->assertSee('modalCreateKelas');
    $response->assertSee('modalEditKelas');
    $response->assertSee('modalDeleteKelas');
});

test('school selector filters kelas list correctly for super admin', function () {
    $tahunAjaranB = TahunAjaran::factory()->create([
        'school_id' => $this->schoolB->id,
        'tahun' => '2026/2027',
        'is_active' => true,
    ]);

    $tingkatanB = Tingkatan::factory()->create([
        'school_id' => $this->schoolB->id,
        'kode' => 'X',
        'nama' => 'Kelas X',
        'tingkat_angka' => 10,
    ]);

    $kelasA = Kelas::factory()->create([
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'tingkatan_id' => $this->tingkatanA->id,
        'nama' => 'X Rombel SMK A',
    ]);

    $kelasB = Kelas::factory()->create([
        'school_id' => $this->schoolB->id,
        'tahun_ajaran_id' => $tahunAjaranB->id,
        'tingkatan_id' => $tingkatanB->id,
        'nama' => 'X Rombel SMA B',
    ]);

    // Mode 1 sekolah selalu menampilkan sekolah aktif tunggal dan menyembunyikan selector multi-sekolah
    $responseA = $this->actingAs($this->superAdmin)->get(route('rombel.kelas.index', [
        'school_id' => $this->schoolB->id,
    ]));
    $responseA->assertOk();
    $responseA->assertSee('X Rombel SMK A');
    $responseA->assertDontSee('X Rombel SMA B');
    $responseA->assertDontSee('id="schoolFilterForm"', false);
});

test('can filter kelas by tahun ajaran, tingkatan, jurusan, and status', function () {
    $tingkatanXI = Tingkatan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'XI',
        'nama' => 'Kelas XI',
        'tingkat_angka' => 11,
    ]);

    $jurusanTKJ = Jurusan::factory()->create([
        'school_id' => $this->schoolA->id,
        'kode' => 'TKJ',
        'nama' => 'Teknik Komputer dan Jaringan',
        'singkatan' => 'TKJ',
    ]);

    $kelasX_PPLG = Kelas::factory()->create([
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'tingkatan_id' => $this->tingkatanA->id,
        'jurusan_id' => $this->jurusanA->id,
        'nama' => 'X Alpha Rombel',
        'is_active' => true,
    ]);

    $kelasXI_TKJ = Kelas::factory()->create([
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'tingkatan_id' => $tingkatanXI->id,
        'jurusan_id' => $jurusanTKJ->id,
        'nama' => 'XI Beta Rombel',
        'is_active' => false,
    ]);

    // Filter Tingkatan XI
    $responseTingkatan = $this->actingAs($this->superAdmin)->get(route('rombel.kelas.index', [
        'school_id' => $this->schoolA->id,
        'tingkatan_id' => $tingkatanXI->id,
    ]));
    $responseTingkatan->assertOk();
    $responseTingkatan->assertSee('XI Beta Rombel');
    $responseTingkatan->assertDontSee('X Alpha Rombel');

    // Filter Jurusan PPLG
    $responseJurusan = $this->actingAs($this->superAdmin)->get(route('rombel.kelas.index', [
        'school_id' => $this->schoolA->id,
        'jurusan_id' => $this->jurusanA->id,
    ]));
    $responseJurusan->assertOk();
    $responseJurusan->assertSee('X Alpha Rombel');
    $responseJurusan->assertDontSee('XI Beta Rombel');

    // Filter Status Nonaktif
    $responseStatus = $this->actingAs($this->superAdmin)->get(route('rombel.kelas.index', [
        'school_id' => $this->schoolA->id,
        'status' => 'nonaktif',
    ]));
    $responseStatus->assertOk();
    $responseStatus->assertSee('XI Beta Rombel');
    $responseStatus->assertDontSee('X Alpha Rombel');
});

test('search filters kelas by nama, kode, ruangan, or wali kelas nama', function () {
    $kelasTarget = Kelas::factory()->create([
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'tingkatan_id' => $this->tingkatanA->id,
        'wali_kelas_id' => $this->gtkA->id,
        'nama' => 'X Spesial Search',
        'kode' => '10-SPEC',
        'ruangan' => 'Lab Multimedia 3',
    ]);

    $kelasOther = Kelas::factory()->create([
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'tingkatan_id' => $this->tingkatanA->id,
        'nama' => 'X Lainnya',
        'kode' => '10-LAIN',
        'ruangan' => 'Ruang 102',
    ]);

    // Search by nama
    $responseNama = $this->actingAs($this->superAdmin)->get(route('rombel.kelas.index', [
        'school_id' => $this->schoolA->id,
        'search' => 'Spesial',
    ]));
    $responseNama->assertOk();
    $responseNama->assertSee('X Spesial Search');
    $responseNama->assertDontSee('X Lainnya');

    // Search by ruangan
    $responseRuangan = $this->actingAs($this->superAdmin)->get(route('rombel.kelas.index', [
        'school_id' => $this->schoolA->id,
        'search' => 'Multimedia 3',
    ]));
    $responseRuangan->assertOk();
    $responseRuangan->assertSee('X Spesial Search');
    $responseRuangan->assertDontSee('X Lainnya');

    // Search by wali kelas nama
    $responseWali = $this->actingAs($this->superAdmin)->get(route('rombel.kelas.index', [
        'school_id' => $this->schoolA->id,
        'search' => 'Budi Santoso',
    ]));
    $responseWali->assertOk();
    $responseWali->assertSee('X Spesial Search');
});

test('can create a new kelas with valid data', function () {
    $payload = [
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'tingkatan_id' => $this->tingkatanA->id,
        'jurusan_id' => $this->jurusanA->id,
        'wali_kelas_id' => $this->gtkA->id,
        'nama' => 'X PPLG 2',
        'kode' => '10-PPLG-2',
        'ruangan' => 'Ruang Teori 105',
        'kapasitas' => 36,
        'is_active' => true,
        'keterangan' => 'Kelas binaan industri perangkat lunak',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('rombel.kelas.store'), $payload);

    $response->assertRedirect(route('rombel.kelas.index', [
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
    ]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('kelas', [
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'tingkatan_id' => $this->tingkatanA->id,
        'jurusan_id' => $this->jurusanA->id,
        'wali_kelas_id' => $this->gtkA->id,
        'nama' => 'X PPLG 2',
        'kode' => '10-PPLG-2',
        'ruangan' => 'Ruang Teori 105',
        'kapasitas' => 36,
        'is_active' => true,
    ]);
});

test('prevents duplicate kelas name in the same school and tahun ajaran', function () {
    Kelas::factory()->create([
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'nama' => 'X RPL 1',
    ]);

    $payload = [
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'tingkatan_id' => $this->tingkatanA->id,
        'nama' => 'X RPL 1',
        'kapasitas' => 36,
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('rombel.kelas.store'), $payload);

    $response->assertSessionHasErrors(['nama']);
});

test('allows same kelas name in different tahun ajaran or different school', function () {
    Kelas::factory()->create([
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'nama' => 'X RPL 1',
    ]);

    $tahunAjaranNext = TahunAjaran::factory()->create([
        'school_id' => $this->schoolA->id,
        'tahun' => '2027/2028',
    ]);

    $payload = [
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $tahunAjaranNext->id,
        'tingkatan_id' => $this->tingkatanA->id,
        'nama' => 'X RPL 1',
        'kapasitas' => 36,
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('rombel.kelas.store'), $payload);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('kelas', [
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $tahunAjaranNext->id,
        'nama' => 'X RPL 1',
    ]);
});

test('can update existing kelas', function () {
    $kelas = Kelas::factory()->create([
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'tingkatan_id' => $this->tingkatanA->id,
        'nama' => 'X PPLG Lama',
        'kapasitas' => 32,
    ]);

    $payload = [
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'tingkatan_id' => $this->tingkatanA->id,
        'jurusan_id' => $this->jurusanA->id,
        'wali_kelas_id' => $this->gtkA->id,
        'nama' => 'X PPLG Baru',
        'kode' => '10-PPLG-BARU',
        'ruangan' => 'Ruang 202',
        'kapasitas' => 36,
        'is_active' => true,
    ];

    $response = $this->actingAs($this->superAdmin)->put(route('rombel.kelas.update', $kelas), $payload);

    $response->assertRedirect(route('rombel.kelas.index', [
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
    ]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('kelas', [
        'id' => $kelas->id,
        'nama' => 'X PPLG Baru',
        'kode' => '10-PPLG-BARU',
        'ruangan' => 'Ruang 202',
        'kapasitas' => 36,
    ]);
});

test('can update existing kelas keeping same name (unique rule ignores current model id)', function () {
    $kelas = Kelas::factory()->create([
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'tingkatan_id' => $this->tingkatanA->id,
        'nama' => 'X PPLG Tetap',
        'kapasitas' => 32,
    ]);

    $payload = [
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'tingkatan_id' => $this->tingkatanA->id,
        'nama' => 'X PPLG Tetap',
        'kapasitas' => 38,
    ];

    $response = $this->actingAs($this->superAdmin)->put(route('rombel.kelas.update', $kelas), $payload);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('kelas', [
        'id' => $kelas->id,
        'nama' => 'X PPLG Tetap',
        'kapasitas' => 38,
    ]);
});

test('tenant idor protection: non-superadmin cannot update kelas of another school', function () {
    $tahunAjaranB = TahunAjaran::factory()->create([
        'school_id' => $this->schoolB->id,
    ]);
    $tingkatanB = Tingkatan::factory()->create([
        'school_id' => $this->schoolB->id,
    ]);

    $kelasSchoolB = Kelas::factory()->create([
        'school_id' => $this->schoolB->id,
        'tahun_ajaran_id' => $tahunAjaranB->id,
        'tingkatan_id' => $tingkatanB->id,
        'nama' => 'Kelas SMA B',
    ]);

    // Admin Sekolah A mencoba mengubah data Kelas milik Sekolah B
    $response = $this->actingAs($this->adminSekolahA)->put(route('rombel.kelas.update', $kelasSchoolB), [
        'school_id' => $this->schoolB->id,
        'tahun_ajaran_id' => $tahunAjaranB->id,
        'tingkatan_id' => $tingkatanB->id,
        'nama' => 'Hacked Class Name',
        'kapasitas' => 36,
    ]);

    $response->assertForbidden();
});

test('tenant idor protection: non-superadmin cannot delete kelas of another school', function () {
    $tahunAjaranB = TahunAjaran::factory()->create([
        'school_id' => $this->schoolB->id,
    ]);
    $tingkatanB = Tingkatan::factory()->create([
        'school_id' => $this->schoolB->id,
    ]);

    $kelasSchoolB = Kelas::factory()->create([
        'school_id' => $this->schoolB->id,
        'tahun_ajaran_id' => $tahunAjaranB->id,
        'tingkatan_id' => $tingkatanB->id,
        'nama' => 'Kelas SMA B',
    ]);

    $response = $this->actingAs($this->adminSekolahA)->delete(route('rombel.kelas.destroy', $kelasSchoolB));

    $response->assertForbidden();
    $this->assertDatabaseHas('kelas', ['id' => $kelasSchoolB->id]);
});

test('can delete kelas', function () {
    $kelas = Kelas::factory()->create([
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'tingkatan_id' => $this->tingkatanA->id,
        'nama' => 'X RPL Akan Dihapus',
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(route('rombel.kelas.destroy', $kelas));

    $response->assertRedirect(route('rombel.kelas.index', [
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
    ]));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('kelas', [
        'id' => $kelas->id,
    ]);
});

test('can toggle status of kelas', function () {
    $kelas = Kelas::factory()->create([
        'school_id' => $this->schoolA->id,
        'tahun_ajaran_id' => $this->tahunAjaranA->id,
        'tingkatan_id' => $this->tingkatanA->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('rombel.kelas.toggle-status', $kelas));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($kelas->fresh()->is_active)->toBeFalse();

    // Toggle back to active
    $this->actingAs($this->superAdmin)->post(route('rombel.kelas.toggle-status', $kelas));
    expect($kelas->fresh()->is_active)->toBeTrue();
});
