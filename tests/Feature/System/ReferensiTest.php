<?php

use App\Models\Referensi;
use App\Models\School;
use App\Models\User;

beforeEach(function () {
    $this->schoolTypeCat = Referensi::create([
        'referensi_id' => null,
        'kode' => 'school_type',
        'nama' => 'Tipe Sekolah',
        'is_active' => true,
        'is_system' => true,
    ]);

    $this->smaItem = Referensi::create([
        'referensi_id' => $this->schoolTypeCat->id,
        'kode' => 'SMA',
        'nama' => 'SMA (Sekolah Menengah Atas)',
        'is_active' => true,
        'is_system' => true,
        'extra_attributes' => ['is_madrasah' => false],
    ]);

    $this->maItem = Referensi::create([
        'referensi_id' => $this->schoolTypeCat->id,
        'kode' => 'MA',
        'nama' => 'MA (Madrasah Aliyah)',
        'is_active' => true,
        'is_system' => true,
        'extra_attributes' => ['is_madrasah' => true],
    ]);

    $this->akreditasiCat = Referensi::create([
        'referensi_id' => null,
        'kode' => 'accreditation',
        'nama' => 'Akreditasi Sekolah',
        'is_active' => true,
        'is_system' => true,
    ]);

    Referensi::clearCache();

    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_referensi_tester',
        'email' => 'superadmin_referensi@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_referensi_tester',
        'email' => 'guru_referensi@infora.test',
        'user_type' => 'guru',
        'is_active' => true,
    ]);
});

test('guest is redirected to login when accessing system.referensi', function () {
    $response = $this->get(route('system.referensi.index'));

    $response->assertRedirect(route('login'));
});

test('non super admin cannot access system.referensi', function () {
    $response = $this->actingAs($this->regularUser)->get(route('system.referensi.index'));

    $response->assertForbidden();
});

test('super admin can view referensi page with master-detail layout', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('system.referensi.index'));

    $response->assertOk();
    $response->assertSee('Data Referensi Sistem');
    $response->assertSee('Tipe Sekolah');
    $response->assertSee('Akreditasi Sekolah');
    $response->assertSee('grid-split-sidebar', false);
    $response->assertSee('modalCreateKategori', false);
    $response->assertSee('modalCreateItem', false);
});

test('super admin can create a new custom category', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('system.referensi.kategori.store'), [
        'nama' => 'Status Kepegawaian',
        'kode' => 'employment_status',
        'is_active' => true,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('referensi', [
        'referensi_id' => null,
        'kode' => 'employment_status',
        'nama' => 'Status Kepegawaian',
        'is_system' => false,
    ]);
});

test('super admin cannot delete system category', function () {
    $schoolTypeCat = Referensi::categories()->where('kode', 'school_type')->first();

    $response = $this->actingAs($this->superAdmin)->delete(route('system.referensi.kategori.destroy', $schoolTypeCat));

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('referensi', [
        'id' => $schoolTypeCat->id,
    ]);
});

test('super admin can create school_type item with is_madrasah flag', function () {
    $schoolTypeCat = Referensi::categories()->where('kode', 'school_type')->first();

    $response = $this->actingAs($this->superAdmin)->post(route('system.referensi.item.store'), [
        'referensi_id' => $schoolTypeCat->id,
        'kode' => 'PDF',
        'nama' => 'Pendidikan Diniyah Formal',
        'is_madrasah' => true,
        'is_active' => true,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $newItem = Referensi::where('referensi_id', $schoolTypeCat->id)->where('kode', 'PDF')->first();
    expect($newItem)->not->toBeNull()
        ->and($newItem->extra_attributes)->toBe(['is_madrasah' => true])
        ->and(Referensi::isMadrasah('PDF'))->toBeTrue();
});

test('super admin cannot delete system item', function () {
    $smaItem = Referensi::where('kode', 'SMA')->whereNotNull('referensi_id')->first();

    $response = $this->actingAs($this->superAdmin)->delete(route('system.referensi.item.destroy', $smaItem));

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('referensi', [
        'id' => $smaItem->id,
    ]);
});

test('super admin cannot delete custom item that is currently used by a school', function () {
    $schoolTypeCat = Referensi::categories()->where('kode', 'school_type')->first();

    // Buat item kustom
    $customItem = Referensi::create([
        'referensi_id' => $schoolTypeCat->id,
        'kode' => 'STM',
        'nama' => 'Sekolah Teknologi Menengah',
        'is_active' => true,
        'is_system' => false,
    ]);

    // Sekolah menggunakannya
    School::factory()->create([
        'school_type' => 'STM',
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(route('system.referensi.item.destroy', $customItem));

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('referensi', [
        'id' => $customItem->id,
    ]);
});

test('super admin can delete custom item when unused', function () {
    $schoolTypeCat = Referensi::categories()->where('kode', 'school_type')->first();

    $customItem = Referensi::create([
        'referensi_id' => $schoolTypeCat->id,
        'kode' => 'UNUSED',
        'nama' => 'Unused Item',
        'is_active' => true,
        'is_system' => false,
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(route('system.referensi.item.destroy', $customItem));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('referensi', [
        'id' => $customItem->id,
    ]);
});

test('super admin can toggle active status of a reference item', function () {
    $item = Referensi::whereNotNull('referensi_id')->first();
    $initialState = (bool) $item->is_active;

    $response = $this->actingAs($this->superAdmin)->patch(route('system.referensi.toggle-status', $item));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $item->refresh();
    expect($item->is_active)->toBe(! $initialState);
});

test('school isMadrasah dynamically checks referensi extra_attributes', function () {
    $schoolMA = School::factory()->create([
        'school_type' => 'MA',
    ]);

    $schoolSMA = School::factory()->create([
        'school_type' => 'SMA',
    ]);

    expect($schoolMA->isMadrasah())->toBeTrue()
        ->and($schoolSMA->isMadrasah())->toBeFalse();
});
