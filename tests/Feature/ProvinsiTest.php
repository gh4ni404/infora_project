<?php

use App\Models\Provinsi;
use App\Models\School;
use App\Models\SubMenu;
use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_provinsi_tester',
        'email' => 'superadmin_provinsi@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_provinsi_tester',
        'email' => 'guru_provinsi@infora.test',
        'user_type' => 'guru',
        'is_active' => true,
    ]);
});

test('guest is redirected to login when accessing provinsi management', function () {
    $response = $this->get(route('wilayah.provinsi'));

    $response->assertRedirect(route('login'));
});

test('non super admin cannot access provinsi management', function () {
    $response = $this->actingAs($this->regularUser)->get(route('wilayah.provinsi'));

    $response->assertForbidden();
});

test('super admin can view provinsi list and see seeded data', function () {
    Provinsi::create([
        'kode' => '11',
        'nama' => 'Aceh',
        'status' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('wilayah.provinsi'));

    $response->assertOk();
    $response->assertSee('Wilayah Provinsi');
    $response->assertSee('Aceh');
    $response->assertSee('11');
    $response->assertSee('modalCreateProvinsi', false);
    $response->assertSee('modalEditProvinsi', false);
    $response->assertSee('modalDeleteProvinsi', false);
});

test('super admin can search provinsi by kode or nama', function () {
    Provinsi::create(['kode' => '13', 'nama' => 'Sumatera Barat', 'status' => true]);
    Provinsi::create(['kode' => '64', 'nama' => 'Kalimantan Timur', 'status' => true]);

    $responseSearchName = $this->actingAs($this->superAdmin)->get(route('wilayah.provinsi', ['search' => 'Sumatera']));
    $responseSearchName->assertOk();
    $responseSearchName->assertSee('Sumatera Barat');
    $responseSearchName->assertDontSee('Kalimantan Timur');

    $responseSearchCode = $this->actingAs($this->superAdmin)->get(route('wilayah.provinsi', ['search' => '64']));
    $responseSearchCode->assertOk();
    $responseSearchCode->assertSee('Kalimantan Timur');
    $responseSearchCode->assertDontSee('Sumatera Barat');
});

test('super admin can filter provinsi by active status', function () {
    $aktif = Provinsi::create(['kode' => '11', 'nama' => 'Provinsi Khusus Aktif', 'status' => true]);
    $nonaktif = Provinsi::create(['kode' => '99', 'nama' => 'Provinsi Khusus Nonaktif', 'status' => false]);

    $responseAktif = $this->actingAs($this->superAdmin)->get(route('wilayah.provinsi', ['status' => '1']));
    $responseAktif->assertOk();
    $responseAktif->assertSee($aktif->nama);
    $responseAktif->assertDontSee($nonaktif->nama);

    $responseNonaktif = $this->actingAs($this->superAdmin)->get(route('wilayah.provinsi', ['status' => '0']));
    $responseNonaktif->assertOk();
    $responseNonaktif->assertSee($nonaktif->nama);
    $responseNonaktif->assertDontSee($aktif->nama);
});

test('super admin can create a new province with valid 2-digit code and name', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('wilayah.provinsi.store'), [
        'kode' => '88',
        'nama' => 'provinsi baru nusantara',
        'status' => '1',
    ]);

    $response->assertRedirect(route('wilayah.provinsi'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('provinsi', [
        'kode' => '88',
        'nama' => 'Provinsi Baru Nusantara', // Title Case verified
        'status' => 1,
    ]);
});

test('provinsi name preserves acronyms in title case format', function () {
    $this->actingAs($this->superAdmin)->post(route('wilayah.provinsi.store'), [
        'kode' => '89',
        'nama' => 'dki wilayah baru',
        'status' => '1',
    ]);

    $created = Provinsi::where('kode', '89')->first();
    expect($created)->not->toBeNull();
    expect($created->nama)->toBe('DKI Wilayah Baru');
});

test('validates 2-digit code format and uniqueness on creation', function () {
    Provinsi::create(['kode' => '73', 'nama' => 'Sulawesi Selatan', 'status' => true]);

    // Duplicate code
    $responseDuplicate = $this->actingAs($this->superAdmin)->post(route('wilayah.provinsi.store'), [
        'kode' => '73',
        'nama' => 'Sulawesi Selatan Cadangan',
    ]);
    $responseDuplicate->assertSessionHasErrors(['kode']);

    // Non-2 digit code
    $responseInvalid = $this->actingAs($this->superAdmin)->post(route('wilayah.provinsi.store'), [
        'kode' => '123',
        'nama' => 'Provinsi Kode Tiga Digit',
    ]);
    $responseInvalid->assertSessionHasErrors(['kode']);
});

test('super admin can update an existing province', function () {
    $provinsi = Provinsi::create([
        'kode' => '98',
        'nama' => 'Provinsi Sebelum Edit',
        'status' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->put(route('wilayah.provinsi.update', $provinsi), [
        'kode' => '98',
        'nama' => 'provinsi setelah update edit',
        'status' => '0',
    ]);

    $response->assertRedirect(route('wilayah.provinsi'));
    $response->assertSessionHas('success');

    $provinsi->refresh();
    expect($provinsi->nama)->toBe('Provinsi Setelah Update Edit');
    expect($provinsi->status)->toBeFalse();
});

test('super admin can delete an unlinked province', function () {
    $provinsi = Provinsi::create([
        'kode' => '87',
        'nama' => 'Provinsi Siap Hapus',
        'status' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(route('wilayah.provinsi.destroy', $provinsi));

    $response->assertRedirect(route('wilayah.provinsi'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('provinsi', [
        'id' => $provinsi->id,
    ]);
});

test('prevents deleting a province linked to active school records', function () {
    $provinsi = Provinsi::create([
        'kode' => '73',
        'nama' => 'Sulawesi Selatan',
        'status' => true,
    ]);

    // Create a school in this province
    School::factory()->create([
        'name' => 'SMK Negeri 1 Bone',
        'npsn' => '40301111',
        'school_type' => 'SMK',
        'status' => 'Negeri',
        'province' => 'Sulawesi Selatan',
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(route('wilayah.provinsi.destroy', $provinsi));

    $response->assertRedirect(route('wilayah.provinsi'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('provinsi', [
        'id' => $provinsi->id,
    ]);
});

test('sidebar submenu wilayah.provinsi resolves to route successfully', function () {
    $submenu = SubMenu::factory()->create([
        'route_name' => 'wilayah.provinsi',
        'name' => 'Provinsi',
    ]);

    expect($submenu->route_url)->toBe(route('wilayah.provinsi'));
});

test('super admin can change per_page limit and display all data when selecting semua', function () {
    for ($i = 10; $i < 45; $i++) {
        Provinsi::firstOrCreate(
            ['kode' => (string) $i],
            ['nama' => "Provinsi Tes {$i}", 'status' => true]
        );
    }

    // Default 15 items per page
    $responseDefault = $this->actingAs($this->superAdmin)->get(route('wilayah.provinsi'));
    $responseDefault->assertOk();
    $responseDefault->assertSee('Selanjutnya');

    // 30 items per page
    $response30 = $this->actingAs($this->superAdmin)->get(route('wilayah.provinsi', ['per_page' => '30']));
    $response30->assertOk();
    $response30->assertSee('Selanjutnya');

    // Semua items per page
    $responseSemua = $this->actingAs($this->superAdmin)->get(route('wilayah.provinsi', ['per_page' => 'semua']));
    $responseSemua->assertOk();
    $responseSemua->assertSee('Menampilkan');
    $responseSemua->assertSee('Provinsi Tes 44');
});

test('super admin can navigate to next page and see subsequent records', function () {
    for ($i = 10; $i < 30; $i++) {
        Provinsi::firstOrCreate(
            ['kode' => (string) $i],
            ['nama' => "Provinsi Urutan {$i}", 'status' => true]
        );
    }

    $responsePage1 = $this->actingAs($this->superAdmin)->get(route('wilayah.provinsi', ['page' => '1']));
    $responsePage1->assertOk();
    $responsePage1->assertSee('Selanjutnya');
    $responsePage1->assertSee('Provinsi Urutan 10');
    $responsePage1->assertDontSee('Provinsi Urutan 29');

    $responsePage2 = $this->actingAs($this->superAdmin)->get(route('wilayah.provinsi', ['page' => '2']));
    $responsePage2->assertOk();
    $responsePage2->assertSee('Sebelumnya');
    $responsePage2->assertSee('Provinsi Urutan 29');
    $responsePage2->assertDontSee('Provinsi Urutan 10');
});
