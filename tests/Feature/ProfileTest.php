<?php

use App\Models\Gtk;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Provinsi;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->school = School::factory()->create([
        'name' => 'SMK Negeri 1 Watampone',
        'is_active' => true,
    ]);

    $this->superAdmin = User::factory()->create([
        'name' => 'Super Administrator',
        'username' => 'superadmin_profile_test',
        'email' => 'superadmin_profile@infora.test',
        'password' => Hash::make('password_rahasia_123'),
        'user_type' => 'super_admin',
        'is_active' => true,
        'avatar_path' => 'photos/avatar/dummy.webp',
    ]);

    $this->guruUser = User::factory()->create([
        'school_id' => $this->school->id,
        'name' => 'Budi Santoso, S.Pd.',
        'username' => 'guru_profile_test',
        'email' => 'guru_profile@infora.test',
        'password' => Hash::make('password_rahasia_456'),
        'user_type' => 'guru',
        'is_active' => true,
        'avatar_path' => 'photos/avatar/guru.webp',
    ]);

    $this->gtk = Gtk::factory()->create([
        'school_id' => $this->school->id,
        'user_id' => $this->guruUser->id,
        'nama_lengkap' => 'Budi Santoso',
        'gelar_belakang' => 'S.Pd.',
        'nip' => '198501012010011005',
        'email' => 'guru_profile@infora.test',
        'no_hp' => '081234567890',
        'alamat' => 'Jl. Pendidikan No. 10',
        'jenis_ptk' => ['Guru Mata Pelajaran', 'Wali Kelas'],
        'status_kepegawaian' => 'PNS',
        'is_active' => true,
    ]);
});

test('guest is redirected to login when accessing profile page', function () {
    $response = $this->get(route('profile.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated gtk user can view profile page with 4 custom gtk tabs', function () {
    $response = $this->actingAs($this->guruUser)->get(route('profile.index'));

    $response->assertOk()
        ->assertViewIs('profile.index')
        ->assertSee('Pengaturan Profil')
        ->assertSee('Informasi Pribadi')
        ->assertSee('Klasifikasi & Pendidikan', false)
        ->assertSee('Kontak & Domisili', false)
        ->assertSee('Status Keaktifan')
        ->assertSee('Kredensial Akun Pengguna')
        ->assertSee('Budi Santoso')
        ->assertSee('guru_profile_test');
});

test('authenticated non-gtk user views standard profile navigation', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('profile.index'));

    $response->assertOk()
        ->assertViewIs('profile.index')
        ->assertSee('Informasi Pribadi')
        ->assertDontSee('Klasifikasi & Pendidikan', false)
        ->assertDontSee('Kontak & Domisili', false)
        ->assertSee('Status Keaktifan');
});

test('profile page properly populates existing gtk data in select fields', function () {
    $this->gtk->update([
        'status_kepegawaian' => 'PNS',
        'jenis_kelamin' => 'L',
        'agama' => 'Islam',
    ]);

    $response = $this->actingAs($this->guruUser)->get(route('profile.index'));

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toMatch('/value="PNS"[^>]*selected/i')
        ->and($content)->toMatch('/value="L"[^>]*selected/i')
        ->and($content)->toMatch('/value="Islam"[^>]*selected/i');
});

test('profile page renders unsaved changes confirmation modal and buttons', function () {
    $response = $this->actingAs($this->guruUser)->get(route('profile.index'));

    $response->assertOk()
        ->assertSee('modalConfirmUnsavedProfile')
        ->assertSee('Perubahan Belum Disimpan')
        ->assertSee('btnSaveUnsavedProfile')
        ->assertSee('btnDiscardUnsavedProfile')
        ->assertSee('btnCancelUnsavedProfile');
});

test('authenticated user can update personal information including gtk details', function () {
    $response = $this->actingAs($this->guruUser)->put(route('profile.update'), [
        'tab' => 'personal',
        'nama_lengkap' => 'Budi Santoso',
        'gelar_depan' => 'Drs.',
        'gelar_belakang' => 'M.Pd.',
        'jenis_kelamin' => 'L',
        'nik' => '7308010101850001',
        'tempat_lahir' => 'Watampone',
        'tanggal_lahir' => '1985-05-12',
        'agama' => 'Islam',
    ]);

    $response->assertRedirect(route('profile.index', ['tab' => 'personal']))
        ->assertSessionHas('success');

    $this->gtk->refresh();
    $this->guruUser->refresh();

    expect($this->gtk->nama_lengkap)->toBe('Budi Santoso')
        ->and($this->gtk->gelar_depan)->toBe('Drs.')
        ->and($this->gtk->gelar_belakang)->toBe('M.Pd.')
        ->and($this->gtk->jenis_kelamin)->toBe('L')
        ->and($this->gtk->nik)->toBe('7308010101850001')
        ->and($this->gtk->tempat_lahir)->toBe('Watampone')
        ->and($this->gtk->agama)->toBe('Islam')
        ->and($this->guruUser->name)->toBe('Budi Santoso');
});

test('authenticated gtk user can update employment classification and qualification', function () {
    $response = $this->actingAs($this->guruUser)->put(route('profile.update'), [
        'tab' => 'employment',
        'status_kepegawaian' => 'PPPK',
        'nip' => '198501012022211001',
        'nuptk' => '1234567890123456',
        'pendidikan_terakhir' => 'Sarjana / Diploma IV (S1/D4)',
        'jurusan_pendidikan' => 'Pendidikan Bahasa Inggris',
    ]);

    $response->assertRedirect(route('profile.index', ['tab' => 'employment']))
        ->assertSessionHas('success');

    $this->gtk->refresh();
    expect($this->gtk->status_kepegawaian)->toBe('PPPK')
        ->and($this->gtk->nip)->toBe('198501012022211001')
        ->and($this->gtk->nuptk)->toBe('1234567890123456')
        ->and($this->gtk->pendidikan_terakhir)->toBe('Sarjana / Diploma IV (S1/D4)')
        ->and($this->gtk->jurusan_pendidikan)->toBe('Pendidikan Bahasa Inggris');
});

test('authenticated gtk user can update contact and wilayah domisili', function () {
    $provinsi = Provinsi::factory()->create(['nama' => 'Sulawesi Selatan', 'status' => true]);
    $kabupaten = Kabupaten::factory()->create(['provinsi_id' => $provinsi->id, 'nama' => 'Kabupaten Bone', 'status' => true]);
    $kecamatan = Kecamatan::factory()->create(['kabupaten_id' => $kabupaten->id, 'nama' => 'Tanete Riattang', 'status' => true]);
    $kelurahan = Kelurahan::factory()->create(['kecamatan_id' => $kecamatan->id, 'nama' => 'Manurunge', 'status' => true]);

    $response = $this->actingAs($this->guruUser)->put(route('profile.update'), [
        'tab' => 'contact',
        'no_hp' => '085211223344',
        'email' => 'budi.contact@sekolah.sch.id',
        'provinsi_id' => $provinsi->id,
        'kabupaten_id' => $kabupaten->id,
        'kecamatan_id' => $kecamatan->id,
        'kelurahan_id' => $kelurahan->id,
        'alamat' => 'Jl. Ahmad Yani No. 12',
    ]);

    $response->assertRedirect(route('profile.index', ['tab' => 'contact']))
        ->assertSessionHas('success');

    $this->gtk->refresh();
    $this->guruUser->refresh();

    expect($this->gtk->no_hp)->toBe('085211223344')
        ->and($this->gtk->email)->toBe('budi.contact@sekolah.sch.id')
        ->and($this->gtk->provinsi_id)->toBe($provinsi->id)
        ->and($this->gtk->kabupaten_id)->toBe($kabupaten->id)
        ->and($this->gtk->kecamatan_id)->toBe($kecamatan->id)
        ->and($this->gtk->kelurahan_id)->toBe($kelurahan->id)
        ->and($this->gtk->alamat)->toBe('Jl. Ahmad Yani No. 12')
        ->and($this->guruUser->email)->toBe('budi.contact@sekolah.sch.id');
});

test('authenticated user can update username in personal tab', function () {
    $response = $this->actingAs($this->guruUser)->put(route('profile.update'), [
        'tab' => 'personal',
        'username' => 'budi_santoso_guru',
    ]);

    $response->assertRedirect(route('profile.index', ['tab' => 'personal']))
        ->assertSessionHas('success');

    $this->guruUser->refresh();
    expect($this->guruUser->username)->toBe('budi_santoso_guru');
});

test('user cannot update username to an existing taken username', function () {
    User::factory()->create([
        'username' => 'taken_username',
    ]);

    $response = $this->actingAs($this->guruUser)->put(route('profile.update'), [
        'tab' => 'personal',
        'username' => 'taken_username',
    ]);

    $response->assertSessionHasErrors(['username']);
});

test('authenticated user can update avatar photo via personal tab', function () {
    $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    $response = $this->actingAs($this->guruUser)->put(route('profile.update'), [
        'tab' => 'personal',
        'foto' => $base64Image,
    ]);

    $response->assertRedirect(route('profile.index', ['tab' => 'personal']))
        ->assertSessionHas('success');

    $this->guruUser->refresh();
    $this->gtk->refresh();

    expect($this->guruUser->avatar_path)->not->toBeNull()
        ->and($this->gtk->foto_path)->not->toBeNull();
});

test('authenticated user can remove avatar photo', function () {
    $response = $this->actingAs($this->guruUser)->put(route('profile.update'), [
        'tab' => 'personal',
        'remove_foto' => true,
    ]);

    $response->assertRedirect(route('profile.index', ['tab' => 'personal']))
        ->assertSessionHas('success');

    $this->guruUser->refresh();
    $this->gtk->refresh();

    expect($this->guruUser->avatar_path)->toBeNull()
        ->and($this->gtk->foto_path)->toBeNull();
});

test('authenticated user can change password successfully and is logged out to login page', function () {
    $response = $this->actingAs($this->guruUser)->put(route('profile.password.update'), [
        'current_password' => 'password_rahasia_456',
        'password' => 'password_baru_sangat_aman_789',
        'password_confirmation' => 'password_baru_sangat_aman_789',
    ]);

    $response->assertRedirect(route('login'))
        ->assertSessionHas('success');

    $this->assertGuest();

    $this->guruUser->refresh();
    expect(Hash::check('password_baru_sangat_aman_789', $this->guruUser->password))->toBeTrue();
});

test('user fails password change when current password is incorrect', function () {
    $response = $this->actingAs($this->guruUser)->put(route('profile.password.update'), [
        'current_password' => 'password_salah_total',
        'password' => 'password_baru_789',
        'password_confirmation' => 'password_baru_789',
    ]);

    $response->assertSessionHasErrors(['current_password']);
    $this->assertAuthenticatedAs($this->guruUser);
});

test('user fails password change when new password confirmation does not match', function () {
    $response = $this->actingAs($this->guruUser)->put(route('profile.password.update'), [
        'current_password' => 'password_rahasia_456',
        'password' => 'password_baru_789',
        'password_confirmation' => 'password_beda_123',
    ]);

    $response->assertSessionHasErrors(['password']);
});

test('user needsProfileCompletion returns true for non-superadmin user with default credentials and false for superadmin', function () {
    $incompleteAdmin = User::factory()->create([
        'user_type' => 'admin',
        'username' => 'new_default_admin',
        'password' => Hash::make('infora123'),
        'avatar_path' => null,
    ]);

    expect($incompleteAdmin->needsProfileCompletion())->toBeTrue();

    // Super Administrator selalu dikecualikan dari kewajiban lengkapi profil
    $incompleteSuperAdmin = User::factory()->create([
        'user_type' => 'super_admin',
        'username' => 'new_default_superadmin',
        'password' => Hash::make('infora123'),
        'avatar_path' => null,
    ]);

    expect($incompleteSuperAdmin->needsProfileCompletion())->toBeFalse();

    // User lengkap dengan password custom
    expect($this->guruUser->needsProfileCompletion())->toBeFalse();
});

test('superadmin dashboard does not display profile completion alert even with default credentials', function () {
    $incompleteUser = User::factory()->create([
        'user_type' => 'super_admin',
        'password' => Hash::make('infora123'),
        'avatar_path' => null,
    ]);

    $response = $this->actingAs($incompleteUser)->get(route('dashboard'));

    $response->assertOk()
        ->assertDontSee('Lengkapi Profil dan Kata Sandi');
});

test('admin dashboard hides profile completion alert for superadmin preview', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('dashboard.admin'));

    $response->assertOk()
        ->assertDontSee('Lengkapi Profil & Kata Sandi:');
});
