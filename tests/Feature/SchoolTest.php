<?php

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
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

test('super admin can view school list with empty state', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('master.data-sekolah.index'));

    $response->assertOk();
    $response->assertSee('Data Sekolah');
    $response->assertSee('Belum ada data sekolah yang terdaftar');
    $response->assertSee('modalCreateSchool', false);
    $response->assertSee('btnOpenCreateSchool', false);
});

test('super admin can view school list with schools data', function () {
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
});

test('super admin can search schools by name, npsn, or city', function () {
    School::factory()->create([
        'name' => 'SMA Negeri 1 Makassar',
        'npsn' => '40312345',
        'city' => 'Kota Makassar',
    ]);

    School::factory()->create([
        'name' => 'SMK Negeri 2 Gowa',
        'npsn' => '40398765',
        'city' => 'Kabupaten Gowa',
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-sekolah.index', ['search' => 'Gowa']));
    $response->assertOk();
    $response->assertSee('Kabupaten Gowa');
    $response->assertDontSee('Kota Makassar');

    $responseNpsn = $this->actingAs($this->superAdmin)->get(route('master.data-sekolah.index', ['search' => '40312345']));
    $responseNpsn->assertOk();
    $responseNpsn->assertSee('40312345');
    $responseNpsn->assertDontSee('40398765');
});

test('super admin can filter schools by school type', function () {
    School::factory()->create([
        'name' => 'SMA Terbuka 1',
        'npsn' => '11111111',
        'school_type' => 'SMA',
    ]);

    School::factory()->create([
        'name' => 'SMK Vokasi 2',
        'npsn' => '22222222',
        'school_type' => 'SMK',
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-sekolah.index', ['school_type' => 'SMK']));
    $response->assertOk();
    $response->assertSee('22222222');
    $response->assertDontSee('11111111');
});

test('school creation validates required fields', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('master.data-sekolah.store'), []);

    $response->assertSessionHasErrors(['name', 'npsn', 'school_type', 'status']);
});

test('school creation validates npsn must be 8 digits', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('master.data-sekolah.store'), [
        'name' => 'SMA Nusantara',
        'npsn' => '123',
        'school_type' => 'SMA',
        'status' => 'Negeri',
    ]);

    $response->assertSessionHasErrors(['npsn']);
});

test('super admin can create a new school', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('master.data-sekolah.store'), [
        'name' => 'sma negeri 5 makassar',
        'npsn' => '40312345',
        'school_type' => 'SMA',
        'status' => 'Negeri',
        'accreditation' => 'A',
        'address' => 'Jl. Taman Makam Pahlawan',
        'city' => 'Kota Makassar',
        'province' => 'Sulawesi Selatan',
        'email' => 'sman5@makassar.sch.id',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('master.data-sekolah.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('schools', [
        'npsn' => '40312345',
        'school_type' => 'SMA',
        'status' => 'Negeri',
        'city' => 'Kota Makassar',
        'is_active' => true,
    ]);

    $school = School::where('npsn', '40312345')->first();
    expect($school->name)->toBe('SMA Negeri 5 Makassar');
});

test('super admin can create school with base64 logo', function () {
    Storage::fake('public');

    // 1x1 transparent PNG base64
    $base64Png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-sekolah.store'), [
        'name' => 'SMK Bina Informatika',
        'npsn' => '40356789',
        'school_type' => 'SMK',
        'status' => 'Swasta',
        'logo' => $base64Png,
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('master.data-sekolah.index'));

    $school = School::where('npsn', '40356789')->first();
    expect($school)->not->toBeNull();
    expect($school->logo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($school->logo_path);
});

test('super admin can view school edit page', function () {
    $school = School::factory()->create([
        'name' => 'SMA Unggulan',
        'npsn' => '40399999',
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-sekolah.edit', $school));

    $response->assertOk();
    $response->assertSee('SMA Unggulan');
    $response->assertSee('40399999');
    $response->assertSee('Formulir Perubahan Data Sekolah');
});

test('super admin can update school information', function () {
    $school = School::factory()->create([
        'name' => 'SMA Awal',
        'npsn' => '40300001',
        'status' => 'Swasta',
    ]);

    $response = $this->actingAs($this->superAdmin)->put(route('master.data-sekolah.update', $school), [
        'name' => 'SMA Perubahan',
        'npsn' => '40300001',
        'school_type' => 'SMA',
        'status' => 'Negeri',
        'city' => 'Makassar Baru',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('master.data-sekolah.index'));
    $response->assertSessionHas('success');

    $school->refresh();
    expect($school->name)->toBe('SMA Perubahan');
    expect($school->status)->toBe('Negeri');
    expect($school->city)->toBe('Makassar Baru');
});

test('super admin can remove logo from school during update', function () {
    Storage::fake('public');

    $fakeLogoPath = 'logos/test_logo.png';
    Storage::disk('public')->put($fakeLogoPath, 'dummy content');

    $school = School::factory()->create([
        'logo_path' => $fakeLogoPath,
    ]);

    $response = $this->actingAs($this->superAdmin)->put(route('master.data-sekolah.update', $school), [
        'name' => $school->name,
        'npsn' => $school->npsn,
        'school_type' => $school->school_type,
        'status' => $school->status,
        'remove_logo' => '1',
    ]);

    $response->assertRedirect(route('master.data-sekolah.index'));

    $school->refresh();
    expect($school->logo_path)->toBeNull();
    Storage::disk('public')->assertMissing($fakeLogoPath);
});

test('super admin can delete school and its logo is removed from storage', function () {
    Storage::fake('public');

    $fakeLogoPath = 'logos/sekolah_delete.png';
    Storage::disk('public')->put($fakeLogoPath, 'dummy content');

    $school = School::factory()->create([
        'logo_path' => $fakeLogoPath,
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(route('master.data-sekolah.destroy', $school));

    $response->assertRedirect(route('master.data-sekolah.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('schools', [
        'id' => $school->id,
    ]);
    Storage::disk('public')->assertMissing($fakeLogoPath);
});

test('school views adhere to zero inline styles convention', function () {
    $school = School::factory()->create();

    $indexResponse = $this->actingAs($this->superAdmin)->get(route('master.data-sekolah.index'));
    $indexResponse->assertOk();
    expect($indexResponse->getContent())->not->toContain('style="');
    expect($indexResponse->getContent())->not->toContain('<style');

    $editResponse = $this->actingAs($this->superAdmin)->get(route('master.data-sekolah.edit', $school));
    $editResponse->assertOk();
    expect($editResponse->getContent())->not->toContain('style="');
    expect($editResponse->getContent())->not->toContain('<style');
});
