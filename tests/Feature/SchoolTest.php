<?php

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Menu;
use App\Models\Module;
use App\Models\Provinsi;
use App\Models\Referensi;
use App\Models\School;
use App\Models\User;
use App\Models\UserMenuPermission;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $schoolTypeCat = Referensi::create([
        'nama' => 'Jenis Sekolah',
        'kode' => 'school_type',
        'is_active' => true,
        'is_system' => true,
    ]);

    foreach (['SMA', 'SMK', 'MA'] as $type) {
        Referensi::create([
            'referensi_id' => $schoolTypeCat->id,
            'nama' => $type,
            'kode' => $type,
            'is_active' => true,
            'is_system' => true,
        ]);
    }

    $accreditationCat = Referensi::create([
        'nama' => 'Akreditasi',
        'kode' => 'accreditation',
        'is_active' => true,
        'is_system' => true,
    ]);

    foreach (['A', 'B', 'C', 'Belum'] as $acc) {
        Referensi::create([
            'referensi_id' => $accreditationCat->id,
            'nama' => $acc,
            'kode' => $acc,
            'is_active' => true,
            'is_system' => true,
        ]);
    }

    Referensi::clearCache();

    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_school_tester',
        'email' => 'superadmin_school@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_school_tester',
        'email' => 'guru_school@infora.test',
        'user_type' => 'guru',
        'is_active' => true,
    ]);
});

test('guest is redirected to login when accessing school management', function () {
    $response = $this->get(route('master.data-sekolah.index'));

    $response->assertRedirect(route('login'));
});

test('non super admin cannot access school management', function () {
    $response = $this->actingAs($this->regularUser)->get(route('master.data-sekolah.index'));

    $response->assertForbidden();
});

test('super admin can view school profile empty state when uninitialized', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('master.data-sekolah.index'));

    $response->assertOk();
    $response->assertSee('Profil Sekolah');
    $response->assertSee('Profil sekolah belum terdaftar');
    $response->assertDontSee('btnOpenCreateSchool', false);
    $response->assertDontSee('modalCreateSchool', false);
});

test('super admin can view single school profile data', function () {
    School::factory()->create([
        'name' => 'SMA Negeri 1 Makassar',
        'npsn' => '40312345',
        'school_type' => 'SMA',
        'status' => 'Negeri',
        'accreditation' => 'A',
        'city' => 'Kota Makassar',
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-sekolah.index'));

    $response->assertOk();
    $response->assertSee('SMA Negeri 1 Makassar');
    $response->assertSee('40312345');
    $response->assertSee('Kota Makassar');
    $response->assertSee('modalEditSchool', false);
});

test('edit route redirects to school profile index', function () {
    $school = School::factory()->create([
        'name' => 'SMA Negeri 1 Makassar',
        'npsn' => '40312345',
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-sekolah.edit', $school));

    $response->assertRedirect(route('master.data-sekolah.index'));
});

test('super admin can update school information', function () {
    $school = School::factory()->create([
        'name' => 'SMA Lama',
        'npsn' => '40312345',
        'school_type' => 'SMA',
        'status' => 'Negeri',
    ]);

    $response = $this->actingAs($this->superAdmin)->put(route('master.data-sekolah.update', $school), [
        'name' => 'SMA Baru Diperbarui',
        'npsn' => '40399999',
        'school_type' => 'SMK',
        'status' => 'Swasta',
        'accreditation' => 'B',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('master.data-sekolah.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('schools', [
        'id' => $school->id,
        'name' => 'SMA Baru Diperbarui',
        'npsn' => '40399999',
        'school_type' => 'SMK',
        'status' => 'Swasta',
        'accreditation' => 'B',
    ]);
});

test('super admin can remove logo from school during update', function () {
    Storage::fake('public');
    Storage::disk('public')->put('logos/test_old.png', 'fake-image-content');

    $school = School::factory()->create([
        'name' => 'SMK Logo Lama',
        'npsn' => '40312345',
        'logo_path' => 'logos/test_old.png',
    ]);

    $response = $this->actingAs($this->superAdmin)->put(route('master.data-sekolah.update', $school), [
        'name' => 'SMK Logo Dihapus',
        'npsn' => '40312345',
        'school_type' => 'SMK',
        'status' => 'Negeri',
        'accreditation' => 'A',
        'remove_logo' => '1',
    ]);

    $response->assertRedirect(route('master.data-sekolah.index'));
    $school->refresh();

    expect($school->logo_path)->toBeNull();
    Storage::disk('public')->assertMissing('logos/test_old.png');
});

test('school views adhere to zero inline styles convention', function () {
    School::factory()->create();

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-sekolah.index'));

    $response->assertOk();
    expect($response->getContent())->not->toContain('style="')
        ->and($response->getContent())->not->toContain('<style>');
});

test('super admin can update school with new wilayah foreign keys', function () {
    $provinsi = Provinsi::create(['kode' => '73', 'nama' => 'Sulawesi Selatan', 'status_aktif' => true]);
    $kabupaten = Kabupaten::create(['provinsi_id' => $provinsi->id, 'kode' => '7371', 'tipe' => 'Kota', 'nama' => 'Makassar', 'status_aktif' => true]);
    $kecamatan = Kecamatan::create(['kabupaten_id' => $kabupaten->id, 'kode' => '7371010', 'nama' => 'Mariso', 'status_aktif' => true]);
    $kelurahan = Kelurahan::create(['kecamatan_id' => $kecamatan->id, 'kode' => '7371010001', 'tipe' => 'Kelurahan', 'nama' => 'Lette', 'status_aktif' => true]);

    $school = School::factory()->create(['npsn' => '40312345']);

    $response = $this->actingAs($this->superAdmin)->put(route('master.data-sekolah.update', $school), [
        'name' => 'SMK Wilayah Updated',
        'npsn' => '40312345',
        'school_type' => 'SMK',
        'status' => 'Negeri',
        'accreditation' => 'A',
        'provinsi_id' => $provinsi->id,
        'kabupaten_id' => $kabupaten->id,
        'kecamatan_id' => $kecamatan->id,
        'kelurahan_id' => $kelurahan->id,
    ]);

    $response->assertRedirect(route('master.data-sekolah.index'));

    $this->assertDatabaseHas('schools', [
        'id' => $school->id,
        'provinsi_id' => $provinsi->id,
        'kabupaten_id' => $kabupaten->id,
        'kecamatan_id' => $kecamatan->id,
        'kelurahan_id' => $kelurahan->id,
    ]);
});

test('super admin can sync single admin account for the active school', function () {
    $school = School::factory()->create(['npsn' => '40355566']);

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-sekolah.sync-single-admin', $school));

    $response->assertRedirect(route('master.data-sekolah.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'school_id' => $school->id,
        'username' => '40355566',
        'user_type' => 'admin',
    ]);
});

test('school index view displays admin account status badge', function () {
    $school = School::factory()->create(['npsn' => '40377788']);
    User::factory()->create([
        'school_id' => $school->id,
        'username' => '40377788',
        'user_type' => 'admin',
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-sekolah.index'));

    $response->assertOk();
    $response->assertSee('40377788');
});

test('non super admin with permission can view and update their own school without resetting permissions', function () {
    $school = School::factory()->create(['name' => 'SMA Negeri 1 Bone', 'npsn' => '40301111']);
    $schoolAdmin = User::factory()->create([
        'school_id' => $school->id,
        'username' => '40301111',
        'user_type' => 'admin',
        'is_active' => true,
    ]);

    $module = Module::create(['name' => 'ADMINISTRASI', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Data Sekolah',
        'route_name' => 'master.data-sekolah.index',
        'order' => 1,
        'is_active' => true,
    ]);

    UserMenuPermission::create([
        'user_id' => $schoolAdmin->id,
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_edit' => true,
    ]);

    $this->actingAs($schoolAdmin)->get(route('master.data-sekolah.index'))
        ->assertOk()
        ->assertSee('SMA Negeri 1 Bone');

    $this->actingAs($schoolAdmin)->put(route('master.data-sekolah.update', $school), [
        'name' => 'SMA Negeri 1 Bone Updated',
        'npsn' => '40301111',
        'school_type' => 'SMA',
        'status' => 'Negeri',
        'accreditation' => 'A',
        'is_active' => '1',
    ])->assertRedirect(route('master.data-sekolah.index'));

    expect(UserMenuPermission::where('user_id', $schoolAdmin->id)->where('menu_id', $menu->id)->exists())->toBeTrue();
});

test('validation returns informative message when school_type or accreditation reference data is invalid on update', function () {
    $school = School::factory()->create(['npsn' => '40312345']);

    $invalidResponse = $this->actingAs($this->superAdmin)->put(route('master.data-sekolah.update', $school), [
        'name' => 'SMK Validasi Referensi',
        'npsn' => '40312345',
        'school_type' => 'INVALID_TYPE',
        'status' => 'Negeri',
        'accreditation' => 'INVALID_ACC',
    ]);

    $invalidResponse->assertSessionHasErrors(['school_type', 'accreditation']);
});

test('super admin can delete school data', function () {
    $school = School::factory()->create([
        'name' => 'SMK Negeri 1 Dihapus',
        'npsn' => '40399999',
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->delete(route('master.data-sekolah.destroy', $school));

    $response->assertRedirect(route('master.data-sekolah.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('schools', ['id' => $school->id]);
});

test('non super admin cannot delete school data even with menu delete permission', function () {
    $school = School::factory()->create([
        'name' => 'SMA Negeri 1 Dilindungi',
        'npsn' => '40388888',
    ]);

    $schoolAdmin = User::factory()->create([
        'school_id' => $school->id,
        'user_type' => 'admin',
        'is_active' => true,
    ]);

    $module = Module::create(['name' => 'ADMINISTRASI', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Data Sekolah',
        'route_name' => 'master.data-sekolah.index',
        'order' => 1,
        'is_active' => true,
    ]);

    UserMenuPermission::create([
        'user_id' => $schoolAdmin->id,
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_edit' => true,
        'can_delete' => true,
    ]);

    $this->actingAs($schoolAdmin)
        ->delete(route('master.data-sekolah.destroy', $school))
        ->assertForbidden();

    $this->assertDatabaseHas('schools', ['id' => $school->id]);
});
