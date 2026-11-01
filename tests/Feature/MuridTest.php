<?php

use App\Models\Kelas;
use App\Models\Murid;
use App\Models\Referensi;
use App\Models\School;
use App\Models\Tingkatan;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

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
        'nama_lengkap' => 'Murid Sekolah A',
    ]);

    $muridB = Murid::factory()->create([
        'school_id' => $this->schoolB->id,
        'nama_lengkap' => 'Murid Sekolah B',
    ]);

    $responseA = $this->actingAs($this->superAdmin)->get(route('master.data-murid', [
        'school_id' => $this->schoolA->id,
    ]));

    $responseA->assertOk();
    $responseA->assertSee('Murid Sekolah A');
    $responseA->assertDontSee('Murid Sekolah B');

    $responseB = $this->actingAs($this->superAdmin)->get(route('master.data-murid', [
        'school_id' => $this->schoolB->id,
    ]));

    $responseB->assertOk();
    $responseB->assertSee('Murid Sekolah B');
    $responseB->assertDontSee('Murid Sekolah A');
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
        'kelas_id' => $this->kelasA->id,
        'angkatan' => '2026',
        'email' => 'annisa@example.com',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-murid.store'), $payload);

    $response->assertRedirect(route('master.data-murid', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('murids', [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Annisa Dwi Lestari',
        'nisn' => '0098765432',
        'nis' => '24050',
        'jenis_kelamin' => 'P',
        'angkatan' => '2026',
        'email' => 'annisa@example.com',
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
        'email' => 'rian.new@example.com',
        'status' => 'aktif',
        'is_active' => 1,
    ];

    $response = $this->actingAs($this->superAdmin)->put(route('master.data-murid.update', $murid), $payload);

    $response->assertRedirect(route('master.data-murid', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('murids', [
        'id' => $murid->id,
        'nama_lengkap' => 'Rian Hidayat Pratama',
        'nis' => '24111',
        'email' => 'rian.new@example.com',
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $murid->user_id,
        'name' => 'Rian Hidayat Pratama',
        'email' => 'rian.new@example.com',
        'is_active' => true,
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
        'nama_lengkap' => 'Murid Yang Dihapus',
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(route('master.data-murid.destroy', $murid));

    $response->assertRedirect(route('master.data-murid', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('murids', ['id' => $murid->id]);
    expect($murid->user->fresh()->is_active)->toBeFalse();
});

test('murid in kemenag madrasah requires 18 digit nism in nis field', function () {
    $schoolTypeCat = Referensi::firstOrCreate(
        ['kode' => 'school_type'],
        ['nama' => 'Jenis Sekolah', 'is_active' => true, 'is_system' => true]
    );

    Referensi::create([
        'referensi_id' => $schoolTypeCat->id,
        'nama' => 'MA',
        'kode' => 'MA',
        'extra_attributes' => ['is_madrasah' => true],
        'is_active' => true,
        'is_system' => true,
    ]);
    Referensi::clearCache();

    $madrasah = School::factory()->ma()->create([
        'name' => 'MAN 1 Makassar',
        'is_active' => true,
    ]);

    // Percobaan 1: Gagal tanpa NIS (NISM) di sekolah Kemenag
    $responseFail = $this->actingAs($this->superAdmin)->post(route('master.data-murid.store'), [
        'school_id' => $madrasah->id,
        'nama_lengkap' => 'Ahmad Fauzi',
        'jenis_kelamin' => 'L',
        'nisn' => '0081234567',
        'angkatan' => '2026',
    ]);

    $responseFail->assertSessionHasErrors(['nis']);
    expect(session('errors')->first('nis'))->toContain('NISM (Nomor Induk Murid Madrasah) wajib diisi');

    // Percobaan 2: Gagal bila panjang digit NIS bukan 18
    $responseInvalidLength = $this->actingAs($this->superAdmin)->post(route('master.data-murid.store'), [
        'school_id' => $madrasah->id,
        'nama_lengkap' => 'Ahmad Fauzi',
        'jenis_kelamin' => 'L',
        'nis' => '12345', // Kurang dari 18 digit
    ]);

    $responseInvalidLength->assertSessionHasErrors(['nis']);
    expect(session('errors')->first('nis'))->toContain('18 digit');

    // Percobaan 3: Berhasil bila NIS valid 18 digit (NISM)
    $validNism = '131273710001260001'; // 12 digit NSM + 2 digit tahun (26) + 4 digit urut (0001)
    $responseSuccess = $this->actingAs($this->superAdmin)->post(route('master.data-murid.store'), [
        'school_id' => $madrasah->id,
        'nama_lengkap' => 'Ahmad Fauzi',
        'jenis_kelamin' => 'L',
        'nis' => $validNism,
        'nisn' => '0081234567',
        'angkatan' => '2026',
    ]);

    $responseSuccess->assertRedirect();
    $this->assertDatabaseHas('murids', [
        'school_id' => $madrasah->id,
        'nama_lengkap' => 'Ahmad Fauzi',
        'nis' => $validNism,
    ]);
});

test('murid in non kemenag school can omit nis or use standard nis', function () {
    // Sekolah SMA (bukan Kemenag) tidak wajib mengisi NIS (bisa nullable)
    $response = $this->actingAs($this->superAdmin)->post(route('master.data-murid.store'), [
        'school_id' => $this->schoolB->id, // SMA
        'nama_lengkap' => 'Murid Reguler SMA',
        'jenis_kelamin' => 'P',
        'nisn' => '0098765432',
        'angkatan' => '2026',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('murids', [
        'school_id' => $this->schoolB->id,
        'nama_lengkap' => 'Murid Reguler SMA',
        'nis' => null,
    ]);
});

test('nisn validation enforces max 10 numeric digits', function () {
    // 1. Lebih dari 10 digit (11 digit) harus gagal validasi
    $responseOverflow = $this->actingAs($this->superAdmin)->post(route('master.data-murid.store'), [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Budi Santoso',
        'jenis_kelamin' => 'L',
        'nisn' => '00812345678', // 11 digit
    ]);

    $responseOverflow->assertSessionHasErrors(['nisn']);
    expect(session('errors')->first('nisn'))->toContain('10 digit');

    // 2. Berisi karakter selain angka harus gagal
    $responseAlphabet = $this->actingAs($this->superAdmin)->post(route('master.data-murid.store'), [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Budi Santoso',
        'jenis_kelamin' => 'L',
        'nisn' => '008123ABCD',
    ]);

    $responseAlphabet->assertSessionHasErrors(['nisn']);

    // 3. Tepat 10 digit angka berhasil
    $responseValid = $this->actingAs($this->superAdmin)->post(route('master.data-murid.store'), [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Budi Santoso',
        'jenis_kelamin' => 'L',
        'nisn' => '0081234567', // 10 digit
    ]);

    $responseValid->assertRedirect();
    $this->assertDatabaseHas('murids', [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Budi Santoso',
        'nisn' => '0081234567',
    ]);
});

test('form error component renders alert icon and message without hex colors', function () {
    $errors = new ViewErrorBag;
    $errors->put('default', new MessageBag([
        'nisn' => ['NISN maksimal terdiri dari 10 digit angka.'],
    ]));
    view()->share('errors', $errors);

    $rendered = Blade::render('<x-form-error name="nisn" />');

    expect($rendered)->toContain('form-error')
        ->and($rendered)->toContain('text-danger')
        ->and($rendered)->toContain('NISN maksimal terdiri dari 10 digit angka.')
        ->and($rendered)->not->toContain('#');
});

test('css contains inputShake animation and border danger token without hex color', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain('@keyframes inputShake')
        ->and($css)->toContain('.border-danger')
        ->and($css)->toContain('var(--infora-danger)')
        ->and($css)->toContain('var(--infora-danger-border)');
});

test('murid photo displays in index table and show modal response when uploaded', function () {
    $murid = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Nurul Fajrin',
        'foto_path' => 'photos/avatar/test_avatar.jpg',
    ]);

    // 1. Tampil di tabel data-murid
    $response = $this->actingAs($this->superAdmin)->get(route('master.data-murid', [
        'school_id' => $this->schoolA->id,
    ]));

    $response->assertOk();
    $response->assertSee($murid->foto_url);

    // 2. Terkirim di payload show modal JSON
    $responseShow = $this->actingAs($this->superAdmin)->get(route('master.data-murid.show', $murid));
    $responseShow->assertOk();
    $responseShow->assertJsonPath('foto_url', $murid->foto_url);
});
