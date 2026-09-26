<?php

use App\Models\Jurusan;
use App\Models\KalenderAkademik;
use App\Models\Menu;
use App\Models\Module;
use App\Models\School;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Models\UserMenuPermission;
use App\Support\SidebarCache;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->mainModule = Module::create([
        'name' => 'NAVIGASI UTAMA',
        'order' => 1,
        'is_active' => true,
    ]);

    $this->dashboardAdminMenu = Menu::create([
        'module_id' => $this->mainModule->id,
        'name' => 'Dashboard Admin',
        'route_name' => 'dashboard.admin',
        'order' => 1,
        'is_active' => true,
    ]);

    $this->superAdmin = User::factory()->create([
        'name' => 'Super Admin Tester',
        'username' => 'superadmin_dashboard',
        'email' => 'superadmin_dash@infora.test',
        'password' => Hash::make('password'),
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);
});

test('guest cannot access admin dashboard and is redirected to login', function () {
    $response = $this->get(route('dashboard.admin'));

    $response->assertRedirect(route('login'));
});

test('super admin can access admin dashboard and view school selector', function () {
    $school = School::factory()->create([
        'name' => 'SMK Negeri 1 Bone',
        'npsn' => '40301234',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('dashboard.admin'));

    $response->assertOk();
    $response->assertSee('Dashboard Administrator Sekolah');
    $response->assertSee('SMK Negeri 1 Bone');
    $response->assertSee('40301234');
});

test('super admin can switch previewed school using school_id query parameter', function () {
    $schoolA = School::factory()->create([
        'name' => 'SMK Negeri 1 Bone',
        'npsn' => '40301234',
        'is_active' => true,
    ]);

    $schoolB = School::factory()->create([
        'name' => 'SMA Negeri 2 Watampone',
        'npsn' => '40305678',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('dashboard.admin', ['school_id' => $schoolB->id]));

    $response->assertOk();
    $response->assertSee('SMA Negeri 2 Watampone');
    $response->assertSee('40305678');
});

test('admin sekolah can access dashboard.admin and view their school profile, stats, and calendar', function () {
    $school = School::factory()->create([
        'name' => 'SMK Negeri 3 Bone',
        'npsn' => '40309999',
        'principal_name' => 'Drs. H. Mulyadi, M.Pd.',
        'is_active' => true,
    ]);

    $adminUser = User::factory()->create([
        'name' => 'Admin SMK Negeri 3 Bone',
        'username' => '40309999',
        'email' => 'admin_smk3@infora.test',
        'password' => Hash::make('password'),
        'user_type' => 'admin',
        'school_id' => $school->id,
        'is_active' => true,
    ]);

    // Berikan izin menu dashboard.admin
    UserMenuPermission::create([
        'user_id' => $adminUser->id,
        'menu_id' => $this->dashboardAdminMenu->id,
        'can_view' => true,
    ]);

    // Data relasi sekolah
    Jurusan::create([
        'school_id' => $school->id,
        'kode' => 'RPL',
        'nama' => 'Rekayasa Perangkat Lunak',
        'is_active' => true,
    ]);

    $tahunAjaran = TahunAjaran::create([
        'school_id' => $school->id,
        'tahun' => '2026/2027',
        'is_active' => true,
    ]);

    Semester::create([
        'tahun_ajaran_id' => $tahunAjaran->id,
        'semester' => 'ganjil',
        'tanggal_mulai' => now()->startOfMonth(),
        'tanggal_selesai' => now()->addMonths(5)->endOfMonth(),
        'is_active' => true,
    ]);

    KalenderAkademik::create([
        'school_id' => $school->id,
        'judul_kegiatan' => 'Asesmen Sumatif Tengah Semester',
        'tanggal_mulai' => now()->addDays(5)->toDateString(),
        'tanggal_selesai' => now()->addDays(10)->toDateString(),
        'tahun_ajaran' => '2026/2027',
        'semester' => 'ganjil',
        'kategori' => 'Ujian/Asesmen',
        'warna' => 'indigo',
        'libur_kbm' => false,
    ]);

    $response = $this->actingAs($adminUser)->get(route('dashboard.admin'));

    $response->assertOk();
    $response->assertSee('SMK Negeri 3 Bone');
    $response->assertSee('40309999');
    $response->assertSee('Drs. H. Mulyadi, M.Pd.');
    $response->assertSee('Asesmen Sumatif Tengah Semester');
    $response->assertSee('2026/2027');
    $response->assertSee('Ujian/Asesmen');
});

test('admin sekolah visiting /dashboard is automatically redirected to /dashboard/admin', function () {
    $school = School::factory()->create([
        'name' => 'SMK Negeri 4 Bone',
        'npsn' => '40304444',
        'is_active' => true,
    ]);

    $adminUser = User::factory()->create([
        'name' => 'Admin SMK Negeri 4 Bone',
        'username' => '40304444',
        'email' => 'admin_smk4@infora.test',
        'password' => Hash::make('password'),
        'user_type' => 'admin',
        'school_id' => $school->id,
        'is_active' => true,
    ]);

    UserMenuPermission::create([
        'user_id' => $adminUser->id,
        'menu_id' => $this->dashboardAdminMenu->id,
        'can_view' => true,
    ]);

    $response = $this->actingAs($adminUser)->get(route('dashboard'));

    $response->assertRedirect(route('dashboard.admin'));
});

test('admin sekolah logging in is redirected to dashboard.admin', function () {
    $school = School::factory()->create([
        'name' => 'SMK Negeri 5 Bone',
        'npsn' => '40305555',
        'is_active' => true,
    ]);

    $adminUser = User::factory()->create([
        'name' => 'Admin SMK Negeri 5 Bone',
        'username' => 'admin5555',
        'email' => 'admin_smk5@infora.test',
        'password' => Hash::make('secret123'),
        'user_type' => 'admin',
        'school_id' => $school->id,
        'is_active' => true,
    ]);

    UserMenuPermission::create([
        'user_id' => $adminUser->id,
        'menu_id' => $this->dashboardAdminMenu->id,
        'can_view' => true,
    ]);

    $response = $this->post(route('login.attempt'), [
        'login' => 'admin5555',
        'password' => 'secret123',
    ]);

    $response->assertRedirect(route('dashboard.admin'));
    $this->assertAuthenticatedAs($adminUser);
});

test('admin dashboard renders empty state components when data is missing', function () {
    $school = School::factory()->create([
        'name' => 'SMK Negeri 6 Bone',
        'npsn' => '40306666',
        'is_active' => true,
    ]);

    $adminUser = User::factory()->create([
        'name' => 'Admin SMK Negeri 6 Bone',
        'username' => '40306666',
        'user_type' => 'admin',
        'school_id' => $school->id,
        'is_active' => true,
    ]);

    UserMenuPermission::create([
        'user_id' => $adminUser->id,
        'menu_id' => $this->dashboardAdminMenu->id,
        'can_view' => true,
    ]);

    $response = $this->actingAs($adminUser)->get(route('dashboard.admin'));

    $response->assertOk();
    $response->assertSee('Tidak ada agenda akademik mendatang dalam waktu dekat.');
    $response->assertSee('empty-state', false);
});

test('dashboard.admin route prioritizes exact menu match over parent dashboard prefix', function () {
    // Buat menu root "dashboard"
    Menu::create([
        'module_id' => $this->mainModule->id,
        'name' => 'Dashboard Platform',
        'route_name' => 'dashboard',
        'order' => 0,
        'is_active' => true,
    ]);

    $school = School::factory()->create(['is_active' => true]);

    $adminUser = User::factory()->create([
        'username' => 'admin_exact_test',
        'user_type' => 'admin',
        'school_id' => $school->id,
        'is_active' => true,
    ]);

    // Berikan izin HANYA untuk menu dashboard.admin, BUKAN untuk menu root dashboard
    UserMenuPermission::create([
        'user_id' => $adminUser->id,
        'menu_id' => $this->dashboardAdminMenu->id,
        'can_view' => true,
    ]);

    SidebarCache::flushGlobal();

    $response = $this->actingAs($adminUser)->get(route('dashboard.admin'));

    $response->assertOk();
    $response->assertSee('Dashboard Administrator Sekolah');
});
