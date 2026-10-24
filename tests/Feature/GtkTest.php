<?php

use App\Models\Gtk;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Provinsi;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_gtk_test',
        'email' => 'superadmin_gtk@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_gtk_test',
        'email' => 'guru_gtk@infora.test',
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $this->schoolA = School::factory()->smk()->create([
        'name' => 'SMK Negeri 1 Watampone',
        'is_active' => true,
    ]);

    $this->schoolB = School::factory()->sma()->create([
        'name' => 'SMA Negeri 1 Bone',
        'is_active' => true,
    ]);
});

test('guest is redirected to login when accessing data gtk', function () {
    $response = $this->get(route('master.data-gtk'));

    $response->assertRedirect(route('login'));
});

test('non super admin cannot access data gtk', function () {
    $response = $this->actingAs($this->regularUser)->get(route('master.data-gtk'));

    $response->assertForbidden();
});

test('super admin can view data gtk page with kpi cards and modals', function () {
    $gtk = Gtk::factory()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Muhammad Syahril',
        'gelar_depan' => 'Drs.',
        'gelar_belakang' => 'M.Pd.',
        'nip' => '197001011995011001',
        'jenis_ptk' => ['Guru Mata Pelajaran', 'Wali Kelas'],
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('master.data-gtk', [
        'school_id' => $this->schoolA->id,
    ]));

    $response->assertOk();
    $response->assertSee('Data Pendidik & Tenaga Kependidikan');
    $response->assertSee('SMK Negeri 1 Watampone');
    $response->assertSee('Muhammad Syahril');
    $response->assertSee('197001011995011001');
    $response->assertSee('modalCreateGtk');
    $response->assertSee('modalEditGtk');
    $response->assertSee('modalShowGtk');
    $response->assertSee('modalDeleteGtk');
});

test('modal create gtk implements streamlined quick onboarding structure', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('master.data-gtk', [
        'school_id' => $this->schoolA->id,
    ]));

    $response->assertOk();
    $response->assertSee('modalCreateGtk');
    $response->assertSee('create_nama_lengkap');
    $response->assertSee('create_jenis_kelamin');
    $response->assertSee('create_status_kepegawaian');
    $response->assertSee('create_jenis_ptk');

    // Pastikan checkbox peranan lama tidak lagi dimuat di modal tambah
    $response->assertDontSee('create_ptk_', false);

    // Pastikan field-field identitas lanjutan & kredensial manual tidak dimuat di modal tambah
    $response->assertDontSee('id="create_nip"', false);
    $response->assertDontSee('id="create_nik"', false);
    $response->assertDontSee('id="create_nuptk"', false);
    $response->assertDontSee('id="create_no_hp"', false);
    $response->assertDontSee('id="create_email"', false);
    $response->assertDontSee('id="create_username"', false);
    $response->assertDontSee('id="create_password"', false);
    $response->assertDontSee('id="create_role_template"', false);
    $response->assertDontSee('id="create_provinsi_id"', false);
    $response->assertDontSee('id="create_foto"', false);
    $response->assertDontSee('id="create_alamat"', false);
    $response->assertDontSee('id="create_agama"', false);
    $response->assertDontSee('id="create_tanggal_lahir"', false);
});

test('super admin can store new gtk using string jenis_ptk Guru and normalizes to standard array', function () {
    $postData = [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Guru Matematika Baru',
        'jenis_kelamin' => 'L',
        'jenis_ptk' => 'Guru',
        'status_kepegawaian' => 'PNS',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-gtk.store'), $postData);

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $gtk = Gtk::where('nama_lengkap', 'Guru Matematika Baru')->first();
    expect($gtk)->not->toBeNull();
    expect($gtk->jenis_ptk)->toEqual(['Guru Mata Pelajaran']);
    expect($gtk->user->user_type)->toBe('guru');
});

test('super admin can store new gtk using string jenis_ptk Staf and normalizes to standard array', function () {
    $postData = [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Staf Keuangan Baru',
        'jenis_kelamin' => 'P',
        'jenis_ptk' => 'Staf',
        'status_kepegawaian' => 'Honorer Sekolah',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-gtk.store'), $postData);

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $gtk = Gtk::where('nama_lengkap', 'Staf Keuangan Baru')->first();
    expect($gtk)->not->toBeNull();
    expect($gtk->jenis_ptk)->toEqual(['Tenaga Administrasi Sekolah (Staf TU)']);
    expect($gtk->user->user_type)->toBe('staf');
});

test('super admin can store new gtk and automatically create user account', function () {
    $provinsi = Provinsi::factory()->create(['nama' => 'Sulawesi Selatan']);
    $kabupaten = Kabupaten::factory()->create(['provinsi_id' => $provinsi->id, 'nama' => 'Kabupaten Bone']);
    $kecamatan = Kecamatan::factory()->create(['kabupaten_id' => $kabupaten->id, 'nama' => 'Tanete Riattang']);
    $kelurahan = Kelurahan::factory()->create(['kecamatan_id' => $kecamatan->id, 'nama' => 'Manurunge']);

    $postData = [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Ahmad Dahlan',
        'gelar_depan' => '',
        'gelar_belakang' => 'S.Pd.',
        'jenis_kelamin' => 'L',
        'nik' => '7308101234560001',
        'nip' => '198501012010011002',
        'nuptk' => '1234567890123456',
        'jenis_ptk' => ['Guru Mata Pelajaran', 'Wali Kelas'],
        'status_kepegawaian' => 'PNS',
        'tempat_lahir' => 'Bone',
        'tanggal_lahir' => '1985-01-01',
        'agama' => 'Islam',
        'no_hp' => '081234567890',
        'email' => 'ahmad.dahlan@sekolah.sch.id',
        'provinsi_id' => $provinsi->id,
        'kabupaten_id' => $kabupaten->id,
        'kecamatan_id' => $kecamatan->id,
        'kelurahan_id' => $kelurahan->id,
        'alamat' => 'Jl. Merdeka No. 10',
        'pendidikan_terakhir' => 'Sarjana / Diploma IV (S1/D4)',
        'jurusan_pendidikan' => 'Pendidikan Matematika',
        'is_active' => '1',
        'username' => 'ahmaddahlan',
        'password' => 'secret123',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-gtk.store'), $postData);

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('gtks', [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Ahmad Dahlan',
        'nip' => '198501012010011002',
        'provinsi_id' => $provinsi->id,
        'kabupaten_id' => $kabupaten->id,
        'kecamatan_id' => $kecamatan->id,
        'kelurahan_id' => $kelurahan->id,
        'alamat' => 'Jl. Merdeka No. 10',
        'is_active' => true,
    ]);

    $this->assertDatabaseHas('users', [
        'school_id' => $this->schoolA->id,
        'username' => 'ahmaddahlan',
        'email' => 'ahmad.dahlan@sekolah.sch.id',
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $createdGtk = Gtk::where('nip', '198501012010011002')->first();
    expect($createdGtk->user_id)->not->toBeNull();
    expect($createdGtk->jenis_ptk)->toContain('Guru Mata Pelajaran');
    expect(Hash::check('secret123', $createdGtk->user->password))->toBeTrue();
});

test('storing new gtk without password automatically uses nip as default password', function () {
    $postData = [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Guru NIP Test',
        'jenis_kelamin' => 'L',
        'nip' => '19880101 201201 1 003',
        'jenis_ptk' => ['Guru Mata Pelajaran'],
        'status_kepegawaian' => 'PNS',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-gtk.store'), $postData);

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $gtk = Gtk::where('nip', '19880101 201201 1 003')->first();
    expect($gtk)->not->toBeNull();
    expect($gtk->user)->not->toBeNull();
    expect(Hash::check('198801012012011003', $gtk->user->password))->toBeTrue();
});

test('storing new gtk without nip nuptk nik uses name slug as default username and password', function () {
    $postData = [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Staf Honorer Baru',
        'jenis_kelamin' => 'P',
        'jenis_ptk' => ['Tenaga Administrasi Sekolah (Staf TU)'],
        'status_kepegawaian' => 'Honorer Sekolah',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-gtk.store'), $postData);

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $gtk = Gtk::where('nama_lengkap', 'Staf Honorer Baru')->first();
    expect($gtk)->not->toBeNull();
    expect($gtk->user)->not->toBeNull();
    expect($gtk->user->username)->toBe('stafhonorerbaru');
    expect(Hash::check('stafhonorerbaru', $gtk->user->password))->toBeTrue();
});

test('super admin can view gtk details via show endpoint json', function () {
    $provinsi = Provinsi::factory()->create(['nama' => 'Sulawesi Selatan']);
    $kabupaten = Kabupaten::factory()->create(['provinsi_id' => $provinsi->id, 'nama' => 'Kabupaten Bone']);

    $gtk = Gtk::factory()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Nurul Hidayah',
        'nip' => '199002022015022001',
        'jenis_ptk' => ['Guru BK / Konselor', 'Wali Kelas'],
        'provinsi_id' => $provinsi->id,
        'kabupaten_id' => $kabupaten->id,
        'alamat' => 'Jl. Veteran No. 5',
    ]);

    $response = $this->actingAs($this->superAdmin)->getJson(route('master.data-gtk.show', $gtk));

    $response->assertOk();
    $response->assertJsonPath('nama_lengkap', 'Nurul Hidayah');
    $response->assertJsonPath('nip', '199002022015022001');
    $response->assertJsonPath('provinsi_nama', 'Sulawesi Selatan');
    $response->assertJsonPath('kabupaten_nama', 'Kabupaten Bone');
    expect($response->json('alamat_lengkap'))->toContain('Kabupaten Bone');
    expect($response->json('jenis_ptk_list'))->toContain('Guru BK / Konselor');
    expect($response->json('jenis_ptk_list'))->toContain('Wali Kelas');
});

test('master.data-gtk.index route alias can be accessed', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('master.data-gtk.index', [
        'school_id' => $this->schoolA->id,
    ]));

    $response->assertOk();
    $response->assertSee('Data Pendidik & Tenaga Kependidikan');
});

test('super admin can update existing gtk data and syncs user account', function () {
    $gtk = Gtk::factory()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Hasnah',
        'jenis_ptk' => ['Guru Mata Pelajaran'],
    ]);

    $user = User::factory()->create([
        'school_id' => $this->schoolA->id,
        'name' => 'Hasnah',
        'username' => 'hasnah_old',
        'user_type' => 'guru',
    ]);
    $gtk->update(['user_id' => $user->id]);

    $updateData = [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Hasnah Mahmud',
        'gelar_depan' => '',
        'gelar_belakang' => 'S.Pd., M.Pd.',
        'jenis_kelamin' => 'P',
        'jenis_ptk' => ['Guru Mata Pelajaran', 'Wali Kelas'],
        'status_kepegawaian' => 'PNS',
        'username' => 'hasnah_new',
        'is_active' => '1',
    ];

    $response = $this->actingAs($this->superAdmin)->put(route('master.data-gtk.update', $gtk), $updateData);

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('gtks', [
        'id' => $gtk->id,
        'nama_lengkap' => 'Hasnah Mahmud',
        'gelar_belakang' => 'S.Pd., M.Pd.',
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'username' => 'hasnah_new',
    ]);
});

test('modal edit gtk implements streamlined structure focused on employment and credentials', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('master.data-gtk', [
        'school_id' => $this->schoolA->id,
    ]));

    $response->assertOk();
    $response->assertSee('modalEditGtk');
    $response->assertSee('edit_nama_lengkap');
    $response->assertSee('edit_gelar_depan');
    $response->assertSee('edit_gelar_belakang');
    $response->assertSee('edit_jenis_kelamin');
    $response->assertSee('edit_status_kepegawaian');
    $response->assertSee('edit_nip');
    $response->assertSee('edit_nuptk');
    $response->assertSee('edit_nik');
    $response->assertSee('edit_account_username_badge');
    $response->assertSee('edit_account_menu_access_btn');
    $response->assertSee('edit_username');
    $response->assertSee('edit_password');
    $response->assertSee('edit_role_template');
    $response->assertSee('edit_is_active');

    // Pastikan field-field kependudukan/wilayah/foto yang dibuang tidak lagi dimuat di modal edit
    $response->assertDontSee('id="edit_provinsi_id"', false);
    $response->assertDontSee('id="edit_kabupaten_id"', false);
    $response->assertDontSee('id="edit_kecamatan_id"', false);
    $response->assertDontSee('id="edit_kelurahan_id"', false);
    $response->assertDontSee('id="edit_alamat"', false);
    $response->assertDontSee('id="edit_tempat_lahir"', false);
    $response->assertDontSee('id="edit_tanggal_lahir"', false);
    $response->assertDontSee('id="edit_agama"', false);
    $response->assertDontSee('id="edit_pendidikan_terakhir"', false);
    $response->assertDontSee('id="edit_jurusan_pendidikan"', false);
    $response->assertDontSee('id="edit_no_hp"', false);
    $response->assertDontSee('id="edit_email"', false);
    $response->assertDontSee('id="edit_foto"', false);
});

test('updating gtk preserves non-form fields such as alamat and tanggal lahir', function () {
    $gtk = Gtk::factory()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Guru Preserved Field',
        'alamat' => 'Jl. Pahlawan No. 45 Watampone',
        'tanggal_lahir' => '1985-05-15',
        'agama' => 'Islam',
        'jenis_ptk' => ['Guru Mata Pelajaran'],
    ]);

    $updateData = [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Guru Preserved Field Updated',
        'jenis_kelamin' => 'L',
        'jenis_ptk' => ['Guru Mata Pelajaran', 'Wali Kelas'],
        'status_kepegawaian' => 'PNS',
    ];

    $response = $this->actingAs($this->superAdmin)->put(route('master.data-gtk.update', $gtk), $updateData);

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $freshGtk = $gtk->fresh();
    expect($freshGtk->nama_lengkap)->toBe('Guru Preserved Field Updated');
    expect($freshGtk->alamat)->toBe('Jl. Pahlawan No. 45 Watampone');
    expect($freshGtk->tanggal_lahir->format('Y-m-d'))->toBe('1985-05-15');
    expect($freshGtk->agama)->toBe('Islam');
});

test('super admin can toggle gtk active status and syncs user status', function () {
    $gtk = Gtk::factory()->create([
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);
    $gtk->update(['user_id' => $user->id]);

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-gtk.toggle-status', $gtk));

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    expect($gtk->fresh()->is_active)->toBeFalse();
    expect($user->fresh()->is_active)->toBeFalse();
});

test('super admin can reset gtk password', function () {
    $gtk = Gtk::factory()->create([
        'school_id' => $this->schoolA->id,
        'nip' => '199201012020011003',
    ]);

    $user = User::factory()->create([
        'school_id' => $this->schoolA->id,
        'password' => Hash::make('customPassword999'),
    ]);
    $gtk->update(['user_id' => $user->id]);

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-gtk.reset-password', $gtk));

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    // Password should now match NIP
    expect(Hash::check('199201012020011003', $user->fresh()->password))->toBeTrue();
});

test('super admin can soft delete gtk and deletes associated user account', function () {
    $gtk = Gtk::factory()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Baharuddin',
    ]);

    $user = User::factory()->create([
        'school_id' => $this->schoolA->id,
        'is_active' => true,
    ]);
    $gtk->update(['user_id' => $user->id]);

    $response = $this->actingAs($this->superAdmin)->delete(route('master.data-gtk.destroy', $gtk));

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('gtks', ['id' => $gtk->id]);
    expect($user->fresh())->toBeNull();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('fails validation when storing gtk with missing required fields', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('master.data-gtk.store'), [
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => '',
        'jenis_kelamin' => 'INVALID',
    ]);

    $response->assertSessionHasErrors(['nama_lengkap', 'jenis_kelamin', 'jenis_ptk', 'status_kepegawaian']);
});

test('school selector filters gtk list correctly', function () {
    $gtkA = Gtk::factory()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Guru Sekolah A',
    ]);

    $gtkB = Gtk::factory()->create([
        'school_id' => $this->schoolB->id,
        'nama_lengkap' => 'Guru Sekolah B',
    ]);

    $responseA = $this->actingAs($this->superAdmin)->get(route('master.data-gtk', [
        'school_id' => $this->schoolA->id,
    ]));

    $responseA->assertOk();
    $responseA->assertSee('Guru Sekolah A');
    $responseA->assertDontSee('Guru Sekolah B');
});

test('search and filter query parameters filter results correctly', function () {
    Gtk::factory()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Zainal Abidin',
        'jenis_ptk' => ['Guru BK / Konselor'],
        'status_kepegawaian' => 'PNS',
        'is_active' => true,
    ]);

    Gtk::factory()->create([
        'school_id' => $this->schoolA->id,
        'nama_lengkap' => 'Hendra Setiawan',
        'jenis_ptk' => ['Tenaga Administrasi Sekolah (Staf TU)'],
        'status_kepegawaian' => 'Honorer Sekolah',
        'is_active' => false,
    ]);

    // Filter search
    $response = $this->actingAs($this->superAdmin)->get(route('master.data-gtk', [
        'school_id' => $this->schoolA->id,
        'search' => 'Zainal',
    ]));
    $response->assertSee('Zainal Abidin');
    $response->assertDontSee('Hendra Setiawan');

    // Filter jenis_ptk
    $responseJenis = $this->actingAs($this->superAdmin)->get(route('master.data-gtk', [
        'school_id' => $this->schoolA->id,
        'jenis_ptk' => 'Tenaga Administrasi Sekolah (Staf TU)',
    ]));
    $responseJenis->assertSee('Hendra Setiawan');
    $responseJenis->assertDontSee('Zainal Abidin');

    // Filter status aktif
    $responseStatus = $this->actingAs($this->superAdmin)->get(route('master.data-gtk', [
        'school_id' => $this->schoolA->id,
        'status' => 'nonaktif',
    ]));
    $responseStatus->assertSee('Hendra Setiawan');
    $responseStatus->assertDontSee('Zainal Abidin');
});

test('redirect after failed validation on storing gtk renders data-gtk page without array to string conversion', function () {
    $response = $this->from(route('master.data-gtk', ['school_id' => $this->schoolA->id]))
        ->actingAs($this->superAdmin)
        ->post(route('master.data-gtk.store'), [
            'school_id' => $this->schoolA->id,
            'nama_lengkap' => '',
            'jenis_ptk' => ['Guru Mata Pelajaran'],
        ]);

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->schoolA->id]));
    $response->assertSessionHasErrors(['nama_lengkap']);

    $indexResponse = $this->actingAs($this->superAdmin)->get(route('master.data-gtk', ['school_id' => $this->schoolA->id]));
    $indexResponse->assertOk();
});
