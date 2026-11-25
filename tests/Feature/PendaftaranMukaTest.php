<?php

use App\Models\Kelas;
use App\Models\Menu;
use App\Models\Module;
use App\Models\Murid;
use App\Models\MuridFaceProfile;
use App\Models\School;
use App\Models\Tingkatan;
use App\Models\User;
use App\Models\UserMenuPermission;
use Illuminate\Support\Facades\Storage;

function samplePngBase64(): string
{
    return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
}

function sampleDescriptor(int $length = 128): array
{
    return array_fill(0, $length, 0.123456);
}

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_face_test',
        'email' => 'superadmin_face@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->schoolA = School::factory()->smk()->create([
        'name' => 'SMK Negeri 1 Bone',
        'is_active' => true,
    ]);

    $this->schoolB = School::factory()->sma()->create([
        'name' => 'SMA Negeri 2 Makassar',
        'is_active' => true,
    ]);

    $this->adminSekolahA = User::factory()->create([
        'username' => 'admin_face_a',
        'email' => 'admin_face_a@infora.test',
        'user_type' => 'admin',
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_face_test',
        'email' => 'guru_face@infora.test',
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
        'nama' => 'X RPL 1',
        'is_active' => true,
    ]);
});

test('guest is redirected to login when accessing pendaftaran muka', function () {
    $response = $this->get(route('pendaftaran_muka'));

    $response->assertRedirect(route('login'));
});

test('unauthorized non-superadmin user without menu permission is forbidden', function () {
    $response = $this->actingAs($this->regularUser)->get(route('pendaftaran_muka'));

    $response->assertForbidden();
});

test('user with view permission can access pendaftaran muka but cannot store without create permission', function () {
    $module = Module::create([
        'name' => 'FACE RECOGNITION',
        'order' => 6,
        'is_active' => true,
    ]);

    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Pendaftaran Muka',
        'route_name' => 'pendaftaran_muka',
        'icon' => 'sparkles',
        'order' => 1,
        'is_active' => true,
    ]);

    UserMenuPermission::create([
        'user_id' => $this->adminSekolahA->id,
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_create' => false,
        'can_edit' => false,
        'can_delete' => false,
    ]);

    $murid = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Andi Pratama',
        'is_active' => true,
    ]);

    $this->actingAs($this->adminSekolahA)
        ->get(route('pendaftaran_muka'))
        ->assertOk()
        ->assertSee('Andi Pratama');

    $this->actingAs($this->adminSekolahA)
        ->post(route('pendaftaran_muka.store'), [
            'school_id' => $this->schoolA->id,
            'murid_id' => $murid->id,
            'label' => 'Tampak Depan',
            'foto_base64' => samplePngBase64(),
            'face_descriptor' => sampleDescriptor(),
        ])
        ->assertForbidden();
});

test('super admin can view pendaftaran muka page with kpi stats and modals', function () {
    $muridTerdaftar = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'kelas_id' => $this->kelasA->id,
        'nama_lengkap' => 'Siti Aisyah',
        'nisn' => '0081111111',
        'is_active' => true,
    ]);

    MuridFaceProfile::factory()->create([
        'school_id' => $this->schoolA->id,
        'murid_id' => $muridTerdaftar->id,
        'label' => 'Tampak Depan',
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('pendaftaran_muka', [
        'school_id' => $this->schoolA->id,
    ]));

    $response->assertOk();
    $response->assertSee('Pendaftaran Muka Murid');
    $response->assertSee('SMK Negeri 1 Bone');
    $response->assertSee('Siti Aisyah');
    $response->assertSee('X RPL 1');
    $response->assertSee('1/5 Aktif');
    $response->assertSee('modalRegisterFace');
    $response->assertSee('modalManageFaces');
    $response->assertSee('modalDeleteFaceProfile');
});

test('tenancy isolation and active status filter apply on pendaftaran muka index', function () {
    Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Murid Aktif Sekolah A',
        'is_active' => true,
    ]);

    Murid::factory()->nonaktif()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Murid Nonaktif Sekolah A',
    ]);

    Murid::factory()->create([
        'school_id' => $this->schoolB->id,
        'nama_lengkap' => 'Murid Sekolah B',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('pendaftaran_muka', [
        'school_id' => $this->schoolA->id,
    ]));

    $response->assertOk();
    $response->assertSee('Murid Aktif Sekolah A');
    $response->assertDontSee('Murid Nonaktif Sekolah A');
    $response->assertDontSee('Murid Sekolah B');
});

test('search, kelas, and face_status filters work properly', function () {
    $muridDenganWajah = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'kelas_id' => $this->kelasA->id,
        'nama_lengkap' => 'Budi Hartono',
        'nisn' => '0079999991',
        'is_active' => true,
    ]);

    MuridFaceProfile::factory()->create([
        'school_id' => $this->schoolA->id,
        'murid_id' => $muridDenganWajah->id,
    ]);

    $muridTanpaWajah = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'kelas_id' => null,
        'nama_lengkap' => 'Citra Dewi',
        'nisn' => '0079999992',
        'is_active' => true,
    ]);

    $this->actingAs($this->superAdmin)
        ->get(route('pendaftaran_muka', [
            'school_id' => $this->schoolA->id,
            'face_status' => 'terdaftar',
        ]))
        ->assertOk()
        ->assertViewHas('murids', fn ($murids) => $murids->pluck('nama_lengkap')->all() === ['Budi Hartono']);

    $this->actingAs($this->superAdmin)
        ->get(route('pendaftaran_muka', [
            'school_id' => $this->schoolA->id,
            'face_status' => 'belum',
            'kelas_id' => 'tanpa_kelas',
            'search' => 'Citra',
        ]))
        ->assertOk()
        ->assertViewHas('murids', fn ($murids) => $murids->pluck('nama_lengkap')->all() === ['Citra Dewi']);
});

test('super admin can store face profile with json descriptor and sync empty murid avatar', function () {
    Storage::fake('public');

    $murid = Murid::factory()->denganUser()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Rizky Ramadhan',
        'foto_path' => null,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('pendaftaran_muka.store'), [
        'school_id' => $this->schoolA->id,
        'murid_id' => $murid->id,
        'label' => 'tampak depan',
        'foto_base64' => samplePngBase64(),
        'face_descriptor' => json_encode(sampleDescriptor()),
        'confidence' => 0.9725,
        'sync_murid_foto' => 1,
    ]);

    $response->assertRedirect(route('pendaftaran_muka', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $profile = MuridFaceProfile::where('murid_id', $murid->id)->first();
    expect($profile)->not->toBeNull()
        ->and($profile->label)->toBe('Tampak Depan')
        ->and($profile->face_descriptor)->toHaveCount(128)
        ->and($profile->confidence)->toBe(0.9725)
        ->and($profile->is_active)->toBeTrue()
        ->and($profile->registered_by)->toBe($this->superAdmin->id);

    Storage::disk('public')->assertExists($profile->foto_path);

    $murid->refresh();
    $murid->user->refresh();

    expect($murid->foto_path)->not->toBeNull()
        ->and($murid->foto_path)->not->toBe($profile->foto_path)
        ->and($murid->user->avatar_path)->toBe($murid->foto_path);

    Storage::disk('public')->assertExists($murid->foto_path);
});

test('store does not overwrite existing murid foto_path', function () {
    Storage::fake('public');

    $murid = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'foto_path' => 'photos/avatar/existing_murid.jpg',
        'is_active' => true,
    ]);

    $this->actingAs($this->superAdmin)->post(route('pendaftaran_muka.store'), [
        'school_id' => $this->schoolA->id,
        'murid_id' => $murid->id,
        'label' => 'Agak Kiri',
        'foto_base64' => samplePngBase64(),
        'face_descriptor' => sampleDescriptor(),
        'sync_murid_foto' => 1,
    ])->assertRedirect();

    expect($murid->fresh()->foto_path)->toBe('photos/avatar/existing_murid.jpg');
});

test('store validates descriptor length, base64 image, cross-school murid, and max sample quota', function () {
    Storage::fake('public');

    $muridA = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);

    $muridB = Murid::factory()->create([
        'school_id' => $this->schoolB->id,
        'is_active' => true,
    ]);

    // 1. Descriptor bukan 128 elemen dan foto base64 tidak valid
    $this->actingAs($this->superAdmin)->post(route('pendaftaran_muka.store'), [
        'school_id' => $this->schoolA->id,
        'murid_id' => $muridA->id,
        'label' => 'Tampak Depan',
        'foto_base64' => 'not-an-image',
        'face_descriptor' => sampleDescriptor(64),
    ])->assertSessionHasErrors(['foto_base64', 'face_descriptor']);

    // 2. Murid lintas sekolah ditolak
    $this->actingAs($this->superAdmin)->post(route('pendaftaran_muka.store'), [
        'school_id' => $this->schoolA->id,
        'murid_id' => $muridB->id,
        'label' => 'Tampak Depan',
        'foto_base64' => samplePngBase64(),
        'face_descriptor' => sampleDescriptor(),
    ])->assertSessionHasErrors(['murid_id']);

    // 3. Kuota maksimal 5 sampel per murid
    MuridFaceProfile::factory()->count(MuridFaceProfile::MAX_SAMPLES_PER_MURID)->create([
        'school_id' => $this->schoolA->id,
        'murid_id' => $muridA->id,
    ]);

    $this->actingAs($this->superAdmin)->post(route('pendaftaran_muka.store'), [
        'school_id' => $this->schoolA->id,
        'murid_id' => $muridA->id,
        'label' => 'Sampel Ke-6',
        'foto_base64' => samplePngBase64(),
        'face_descriptor' => sampleDescriptor(),
    ])->assertSessionHasErrors(['murid_id']);
});

test('show returns murid face profiles json and protects cross-school IDOR', function () {
    $muridA = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'kelas_id' => $this->kelasA->id,
        'nama_lengkap' => 'Fajar Nugroho',
    ]);

    MuridFaceProfile::factory()->create([
        'school_id' => $this->schoolA->id,
        'murid_id' => $muridA->id,
        'label' => 'Tampak Depan',
        'confidence' => 0.955,
    ]);

    $this->actingAs($this->superAdmin)
        ->getJson(route('pendaftaran_muka.show', $muridA))
        ->assertOk()
        ->assertJsonPath('nama_lengkap', 'Fajar Nugroho')
        ->assertJsonPath('active_count', 1)
        ->assertJsonCount(1, 'profiles')
        ->assertJsonPath('profiles.0.label', 'Tampak Depan')
        ->assertJsonPath('profiles.0.confidence_percent', '95.5%');

    // Cross-school IDOR oleh admin sekolah A terhadap murid sekolah B
    $module = Module::create(['name' => 'FACE RECOGNITION', 'order' => 6, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Pendaftaran Muka',
        'route_name' => 'pendaftaran_muka',
        'icon' => 'sparkles',
        'order' => 1,
        'is_active' => true,
    ]);
    UserMenuPermission::create([
        'user_id' => $this->adminSekolahA->id,
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_create' => true,
        'can_edit' => true,
        'can_delete' => true,
    ]);

    $muridB = Murid::factory()->create([
        'school_id' => $this->schoolB->id,
        'nama_lengkap' => 'Murid Sekolah Lain',
    ]);

    $this->actingAs($this->adminSekolahA)
        ->getJson(route('pendaftaran_muka.show', $muridB))
        ->assertForbidden();
});

test('toggle status and destroy work properly and enforce tenant isolation', function () {
    Storage::fake('public');

    $murid = Murid::factory()->create([
        'school_id' => $this->schoolA->id,
        'foto_path' => null,
        'is_active' => true,
    ]);

    // Upload lewat store agar file fisik face_murid dan avatar terpisah tersimpan di disk fake
    $this->actingAs($this->superAdmin)->post(route('pendaftaran_muka.store'), [
        'school_id' => $this->schoolA->id,
        'murid_id' => $murid->id,
        'label' => 'Tampak Depan',
        'foto_base64' => samplePngBase64(),
        'face_descriptor' => sampleDescriptor(),
        'sync_murid_foto' => 1,
    ])->assertRedirect();

    $profile = MuridFaceProfile::where('murid_id', $murid->id)->firstOrFail();
    $murid->refresh();
    $avatarPath = $murid->foto_path;

    // 1. Toggle status
    $this->actingAs($this->superAdmin)
        ->patch(route('pendaftaran_muka.toggle-status', $profile))
        ->assertRedirect(route('pendaftaran_muka', ['school_id' => $this->schoolA->id]));

    expect($profile->fresh()->is_active)->toBeFalse();

    $this->actingAs($this->superAdmin)
        ->patch(route('pendaftaran_muka.toggle-status', $profile))
        ->assertRedirect();

    expect($profile->fresh()->is_active)->toBeTrue();

    // 2. Delete sample removes face file but preserves murid profile photo
    $faceFilePath = $profile->foto_path;
    $this->actingAs($this->superAdmin)
        ->delete(route('pendaftaran_muka.destroy', $profile))
        ->assertRedirect(route('pendaftaran_muka', ['school_id' => $this->schoolA->id]));

    $this->assertDatabaseMissing('murid_face_profiles', ['id' => $profile->id]);
    Storage::disk('public')->assertMissing($faceFilePath);
    Storage::disk('public')->assertExists($avatarPath);

    // 3. Cross-school IDOR protection for toggle & destroy
    $module = Module::create(['name' => 'FACE RECOGNITION', 'order' => 6, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Pendaftaran Muka',
        'route_name' => 'pendaftaran_muka',
        'icon' => 'sparkles',
        'order' => 1,
        'is_active' => true,
    ]);
    UserMenuPermission::create([
        'user_id' => $this->adminSekolahA->id,
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_create' => true,
        'can_edit' => true,
        'can_delete' => true,
    ]);

    $profileB = MuridFaceProfile::factory()->create([
        'school_id' => $this->schoolB->id,
    ]);

    $this->actingAs($this->adminSekolahA)
        ->patch(route('pendaftaran_muka.toggle-status', $profileB))
        ->assertForbidden();

    $this->actingAs($this->adminSekolahA)
        ->delete(route('pendaftaran_muka.destroy', $profileB))
        ->assertForbidden();
});
