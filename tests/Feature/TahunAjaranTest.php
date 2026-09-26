<?php

use App\Models\Menu;
use App\Models\Module;
use App\Models\School;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Models\UserMenuPermission;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_ta_tester',
        'email' => 'superadmin_ta@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_ta_tester',
        'email' => 'guru_ta@infora.test',
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $this->school = School::first() ?? School::factory()->create([
        'name' => 'SMK Negeri 8 Bone',
        'npsn' => '69944581',
        'school_type' => 'SMK',
        'status' => 'Negeri',
    ]);
});

test('guest is redirected to login when accessing tahun ajaran page', function () {
    $response = $this->get(route('konfigurasi-sekolah.tahun-ajaran'));

    $response->assertRedirect(route('login'));
});

test('non super admin cannot access tahun ajaran page', function () {
    $response = $this->actingAs($this->regularUser)->get(route('konfigurasi-sekolah.tahun-ajaran'));

    $response->assertForbidden();
});

test('super admin can view tahun ajaran master detail page', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('konfigurasi-sekolah.tahun-ajaran'));

    $response->assertOk();
    $response->assertSee('Tahun Ajaran dan Semester', false);
    $response->assertSee('cardTahunAjaranMaster', false);
    $response->assertSee('cardSemesterDetail', false);
    $response->assertSee('modalCreateTahunAjaran', false);
    $response->assertSee('modalEditTahunAjaran', false);
    $response->assertSee('modalCreateSemester', false);
    $response->assertSee('modalEditSemester', false);
    $response->assertSee('InforaModals.setup', false);
    $response->assertSee('InforaModals.bindOpenButton', false);
    $response->assertSee('InforaModals.bindEditButtons', false);
    $response->assertSee('InforaModals.reopenOnError', false);
});

test('validation fails when storing tahun ajaran with invalid format', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('konfigurasi-sekolah.tahun-ajaran.store'), [
        'school_id' => $this->school->id,
        'tahun' => '2026-2027', // Salah format, harus 2026/2027
    ]);

    $response->assertSessionHasErrors('tahun');
});

test('super admin can create new tahun ajaran', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('konfigurasi-sekolah.tahun-ajaran.store'), [
        'school_id' => $this->school->id,
        'tahun' => '2026/2027',
        'is_active' => true,
        'keterangan' => 'Tahun ajaran baru operasional',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tahun_ajaran', [
        'school_id' => $this->school->id,
        'tahun' => '2026/2027',
        'is_active' => true,
    ]);
});

test('super admin can update existing tahun ajaran', function () {
    $tahunAjaran = TahunAjaran::create([
        'school_id' => $this->school->id,
        'tahun' => '2025/2026',
        'is_active' => false,
        'keterangan' => 'Tahun lama',
    ]);

    $response = $this->actingAs($this->superAdmin)->put(route('konfigurasi-sekolah.tahun-ajaran.update', $tahunAjaran), [
        'school_id' => $this->school->id,
        'tahun' => '2025/2026',
        'is_active' => false,
        'keterangan' => 'Catatan revisi',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tahun_ajaran', [
        'id' => $tahunAjaran->id,
        'keterangan' => 'Catatan revisi',
    ]);
});

test('super admin can create semester under tahun ajaran', function () {
    $tahunAjaran = TahunAjaran::create([
        'school_id' => $this->school->id,
        'tahun' => '2026/2027',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('konfigurasi-sekolah.tahun-ajaran.semester.store', $tahunAjaran), [
        'semester' => 'ganjil',
        'tanggal_mulai' => '2026-07-15',
        'tanggal_selesai' => '2026-12-20',
        'is_active' => true,
        'keterangan' => 'KBM Reguler Ganjil',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('semester', [
        'tahun_ajaran_id' => $tahunAjaran->id,
        'semester' => 'ganjil',
        'is_active' => true,
    ]);
});

test('cannot create duplicate semester type under same tahun ajaran', function () {
    $tahunAjaran = TahunAjaran::create([
        'school_id' => $this->school->id,
        'tahun' => '2026/2027',
        'is_active' => true,
    ]);

    $tahunAjaran->semesters()->create([
        'semester' => 'ganjil',
        'tanggal_mulai' => '2026-07-15',
        'tanggal_selesai' => '2026-12-20',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('konfigurasi-sekolah.tahun-ajaran.semester.store', $tahunAjaran), [
        'semester' => 'ganjil',
        'tanggal_mulai' => '2026-07-15',
        'tanggal_selesai' => '2026-12-20',
    ]);

    $response->assertSessionHas('error');
});

test('super admin can activate a semester and atomic switch occurs', function () {
    $ta1 = TahunAjaran::create([
        'school_id' => $this->school->id,
        'tahun' => '2025/2026',
        'is_active' => true,
    ]);
    $semLama = $ta1->semesters()->create([
        'semester' => 'genap',
        'tanggal_mulai' => '2026-01-05',
        'tanggal_selesai' => '2026-06-25',
        'is_active' => true,
    ]);

    $ta2 = TahunAjaran::create([
        'school_id' => $this->school->id,
        'tahun' => '2026/2027',
        'is_active' => false,
    ]);
    $semBaru = $ta2->semesters()->create([
        'semester' => 'ganjil',
        'tanggal_mulai' => '2026-07-15',
        'tanggal_selesai' => '2026-12-20',
        'is_active' => false,
    ]);

    $response = $this->actingAs($this->superAdmin)->post(
        route('konfigurasi-sekolah.tahun-ajaran.semester.activate', [$ta2, $semBaru])
    );

    $response->assertRedirect();

    // Verifikasi semester baru aktif dan semester lama nonaktif
    expect($semBaru->fresh()->is_active)->toBeTrue();
    expect($semLama->fresh()->is_active)->toBeFalse();

    // Verifikasi tahun ajaran baru aktif dan tahun ajaran lama nonaktif
    expect($ta2->fresh()->is_active)->toBeTrue();
    expect($ta1->fresh()->is_active)->toBeFalse();

    // Verifikasi Helper Eloquent
    $activeSemFromHelper = Semester::activeFor($this->school->id);
    expect($activeSemFromHelper?->id)->toBe($semBaru->id);

    $activeTaFromHelper = TahunAjaran::activeFor($this->school->id);
    expect($activeTaFromHelper?->id)->toBe($ta2->id);
});

test('deleting active tahun ajaran is prohibited', function () {
    $ta = TahunAjaran::create([
        'school_id' => $this->school->id,
        'tahun' => '2026/2027',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(
        route('konfigurasi-sekolah.tahun-ajaran.destroy', $ta)
    );

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('tahun_ajaran', ['id' => $ta->id]);
});

test('deleting active semester is prohibited', function () {
    $ta = TahunAjaran::create([
        'school_id' => $this->school->id,
        'tahun' => '2026/2027',
        'is_active' => true,
    ]);
    $sem = $ta->semesters()->create([
        'semester' => 'ganjil',
        'tanggal_mulai' => '2026-07-15',
        'tanggal_selesai' => '2026-12-20',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->delete(
        route('konfigurasi-sekolah.tahun-ajaran.semester.destroy', [$ta, $sem])
    );

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('semester', ['id' => $sem->id]);
});

test('deleting non active semester and tahun ajaran succeeds', function () {
    $ta = TahunAjaran::create([
        'school_id' => $this->school->id,
        'tahun' => '2024/2025',
        'is_active' => false,
    ]);
    $sem = $ta->semesters()->create([
        'semester' => 'genap',
        'tanggal_mulai' => '2025-01-05',
        'tanggal_selesai' => '2025-06-25',
        'is_active' => false,
    ]);

    // Hapus semester
    $responseSem = $this->actingAs($this->superAdmin)->delete(
        route('konfigurasi-sekolah.tahun-ajaran.semester.destroy', [$ta, $sem])
    );
    $responseSem->assertRedirect();
    $this->assertDatabaseMissing('semester', ['id' => $sem->id]);

    // Hapus tahun ajaran
    $responseTa = $this->actingAs($this->superAdmin)->delete(
        route('konfigurasi-sekolah.tahun-ajaran.destroy', $ta)
    );
    $responseTa->assertRedirect();
    $this->assertDatabaseMissing('tahun_ajaran', ['id' => $ta->id]);
});

test('school admin can access tahun ajaran page and only sees their own school data without school switcher', function () {
    $module = Module::firstOrCreate(['name' => 'KONFIGURASI'], ['order' => 1, 'is_active' => true]);
    $menu = Menu::firstOrCreate(
        ['route_name' => 'konfigurasi-sekolah.tahun-ajaran'],
        ['module_id' => $module->id, 'name' => 'Tahun Ajaran', 'order' => 1, 'is_active' => true]
    );

    $schoolA = School::factory()->create([
        'name' => 'SMK Negeri 8 Bone',
        'npsn' => '69944581',
        'is_active' => true,
    ]);

    $schoolB = School::factory()->create([
        'name' => 'Mas Pompanua',
        'npsn' => '12345678',
        'is_active' => true,
    ]);

    $adminUser = User::factory()->create([
        'name' => 'Admin SMK Negeri 8 Bone',
        'username' => '69944581',
        'email' => 'admin_smk8_test@infora.test',
        'user_type' => 'admin',
        'school_id' => $schoolA->id,
        'is_active' => true,
    ]);

    UserMenuPermission::create([
        'user_id' => $adminUser->id,
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_create' => true,
        'can_edit' => true,
        'can_delete' => true,
    ]);

    // Data untuk School A
    $taSchoolA = TahunAjaran::create([
        'school_id' => $schoolA->id,
        'tahun' => '2026/2027',
        'is_active' => true,
    ]);

    // Data untuk School B
    $taSchoolB = TahunAjaran::create([
        'school_id' => $schoolB->id,
        'tahun' => '2025/2026',
        'is_active' => true,
    ]);

    $response = $this->actingAs($adminUser)->get(route('konfigurasi-sekolah.tahun-ajaran'));

    $response->assertOk();
    // Harus melihat data sekolah sendiri
    $response->assertSee('SMK Negeri 8 Bone');
    $response->assertSee('2026/2027');

    // Dilarang melihat data sekolah lain
    $response->assertDontSee('Mas Pompanua');
    $response->assertDontSee('2025/2026');

    // Dilarang menampilkan dropdown selector sekolah untuk non-superadmin
    $response->assertDontSee('id="schoolFilterForm"', false);
});

test('school admin cannot manipulate tahun ajaran or semester belonging to another school', function () {
    $module = Module::firstOrCreate(['name' => 'KONFIGURASI'], ['order' => 1, 'is_active' => true]);
    $menu = Menu::firstOrCreate(
        ['route_name' => 'konfigurasi-sekolah.tahun-ajaran'],
        ['module_id' => $module->id, 'name' => 'Tahun Ajaran', 'order' => 1, 'is_active' => true]
    );

    $schoolA = School::factory()->create(['name' => 'Sekolah A', 'npsn' => '11111111', 'is_active' => true]);
    $schoolB = School::factory()->create(['name' => 'Sekolah B', 'npsn' => '22222222', 'is_active' => true]);

    $adminUser = User::factory()->create([
        'username' => 'admin_school_a',
        'email' => 'admin_a@infora.test',
        'user_type' => 'admin',
        'school_id' => $schoolA->id,
        'is_active' => true,
    ]);

    UserMenuPermission::create([
        'user_id' => $adminUser->id,
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_create' => true,
        'can_edit' => true,
        'can_delete' => true,
    ]);

    $taSchoolB = TahunAjaran::create([
        'school_id' => $schoolB->id,
        'tahun' => '2025/2026',
        'is_active' => false,
    ]);

    $semSchoolB = $taSchoolB->semesters()->create([
        'semester' => 'ganjil',
        'tanggal_mulai' => '2025-07-15',
        'tanggal_selesai' => '2025-12-20',
        'is_active' => false,
    ]);

    // Update tahun ajaran sekolah lain dilarang
    $responseUpdate = $this->actingAs($adminUser)->put(
        route('konfigurasi-sekolah.tahun-ajaran.update', $taSchoolB),
        ['school_id' => $schoolB->id, 'tahun' => '2025/2026']
    );
    $responseUpdate->assertForbidden();

    // Hapus tahun ajaran sekolah lain dilarang
    $responseDelete = $this->actingAs($adminUser)->delete(
        route('konfigurasi-sekolah.tahun-ajaran.destroy', $taSchoolB)
    );
    $responseDelete->assertForbidden();

    // Tambah semester pada tahun ajaran sekolah lain dilarang
    $responseStoreSem = $this->actingAs($adminUser)->post(
        route('konfigurasi-sekolah.tahun-ajaran.semester.store', $taSchoolB),
        ['semester' => 'genap', 'tanggal_mulai' => '2026-01-05', 'tanggal_selesai' => '2026-06-25']
    );
    $responseStoreSem->assertForbidden();

    // Hapus semester sekolah lain dilarang
    $responseDelSem = $this->actingAs($adminUser)->delete(
        route('konfigurasi-sekolah.tahun-ajaran.semester.destroy', [$taSchoolB, $semSchoolB])
    );
    $responseDelSem->assertForbidden();
});

test('super admin sees school switcher when multiple active schools exist and can switch previewed school', function () {
    $schoolA = School::factory()->create(['name' => 'SMA A Watampone', 'npsn' => '33333333', 'is_active' => true]);
    $schoolB = School::factory()->create(['name' => 'SMA B Watampone', 'npsn' => '44444444', 'is_active' => true]);

    $taA = TahunAjaran::create(['school_id' => $schoolA->id, 'tahun' => '2026/2027', 'is_active' => true]);
    $taB = TahunAjaran::create(['school_id' => $schoolB->id, 'tahun' => '2024/2025', 'is_active' => true]);

    $response = $this->actingAs($this->superAdmin)->get(route('konfigurasi-sekolah.tahun-ajaran'));
    $response->assertOk();
    $response->assertSee('id="schoolFilterForm"', false);

    // Switch ke School B
    $responseSwitch = $this->actingAs($this->superAdmin)->get(
        route('konfigurasi-sekolah.tahun-ajaran', ['school_id' => $schoolB->id])
    );
    $responseSwitch->assertOk();
    $responseSwitch->assertSee('SMA B Watampone');
    $responseSwitch->assertSee('2024/2025');
});
