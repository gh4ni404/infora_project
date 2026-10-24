<?php

use App\Models\Gtk;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_import_test',
        'email' => 'superadmin_import@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_import_test',
        'email' => 'guru_import@infora.test',
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $this->school = School::factory()->smk()->create([
        'name' => 'SMK Negeri 1 Watampone',
        'is_active' => true,
    ]);
});

test('guest cannot download template, export, or import gtk', function () {
    $this->get(route('master.data-gtk.template'))->assertRedirect(route('login'));
    $this->get(route('master.data-gtk.export'))->assertRedirect(route('login'));
    $this->post(route('master.data-gtk.import'))->assertRedirect(route('login'));
});

test('non super admin without permission cannot download gtk template', function () {
    $this->actingAs($this->regularUser)
        ->get(route('master.data-gtk.template'))
        ->assertForbidden();
});

test('non super admin without permission cannot export gtk', function () {
    $this->actingAs($this->regularUser)
        ->get(route('master.data-gtk.export'))
        ->assertForbidden();
});

test('non super admin without permission cannot import gtk', function () {
    $this->actingAs($this->regularUser)
        ->post(route('master.data-gtk.import'))
        ->assertForbidden();
});

test('super admin can download gtk import template with essential columns and guide sheet', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('master.data-gtk.template'));

    $response->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('Template_Import_GTK_INFORA.xlsx');

    $tempFile = tempnam(sys_get_temp_dir(), 'tpl_').'.xlsx';
    file_put_contents($tempFile, $response->streamedContent());

    $reader = new PhpOffice\PhpSpreadsheet\Reader\Xlsx;
    $spreadsheet = $reader->load($tempFile);

    expect($spreadsheet->getSheetCount())->toBe(2);

    $sheet0 = $spreadsheet->getSheet(0);
    expect($sheet0->getTitle())->toBe('Template GTK');
    expect($sheet0->getCell('A1')->getValue())->toBe('Nama Lengkap*');
    expect($sheet0->getCell('B1')->getValue())->toBe('Jenis Kelamin (L/P)*');
    expect($sheet0->getCell('C1')->getValue())->toBe('Status Kepegawaian*');
    expect($sheet0->getCell('D1')->getValue())->toBe('Peran / Jenis PTK*');
    expect($sheet0->getCell('E1')->getValue())->toBeNull();

    $sheet1 = $spreadsheet->getSheet(1);
    expect($sheet1->getTitle())->toBe('Panduan & Referensi');

    @unlink($tempFile);
});

test('super admin can export gtk data as spreadsheet', function () {
    Gtk::factory()->create([
        'school_id' => $this->school->id,
        'nama_lengkap' => 'Ahmad Dahlan',
        'nip' => '198001012005011003',
        'jenis_ptk' => ['Guru Mapel'],
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->get(route('master.data-gtk.export', ['school_id' => $this->school->id]));

    $response->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('Data_GTK_');
});

test('super admin can export gtk data with active search filter', function () {
    Gtk::factory()->create([
        'school_id' => $this->school->id,
        'nama_lengkap' => 'Target Guru Dicari',
    ]);
    Gtk::factory()->create([
        'school_id' => $this->school->id,
        'nama_lengkap' => 'Guru Lainnya',
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->get(route('master.data-gtk.export', [
            'school_id' => $this->school->id,
            'search' => 'Dicari',
        ]));

    $response->assertOk();
});

test('super admin can import valid essential gtk spreadsheet and create user account', function () {
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Nama Lengkap*');
    $sheet->setCellValue('B1', 'Jenis Kelamin (L/P)*');
    $sheet->setCellValue('C1', 'Status Kepegawaian*');
    $sheet->setCellValue('D1', 'Jenis PTK (pisahkan koma jika jamak)*');
    $sheet->setCellValue('E1', 'NIP');
    $sheet->setCellValue('F1', 'NIK');
    $sheet->setCellValue('G1', 'No HP / WhatsApp');
    $sheet->setCellValue('H1', 'Email');

    // Row 2 data (essential onboarding)
    $sheet->setCellValue('A2', 'Rahmat Hidayat');
    $sheet->setCellValue('B2', 'L');
    $sheet->setCellValue('C2', 'PNS');
    $sheet->setCellValue('D2', 'Guru Mata Pelajaran, Wali Kelas');
    $sheet->setCellValue('E2', '198012122008011005');
    $sheet->setCellValue('F2', '7308011212800005');
    $sheet->setCellValue('G2', '081299887766');
    $sheet->setCellValue('H2', 'rahmat.hidayat@sekolah.sch.id');

    $tempPath = tempnam(sys_get_temp_dir(), 'gtk_test_').'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);

    $uploadedFile = new UploadedFile(
        $tempPath,
        'gtk_import.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    $response = $this->actingAs($this->superAdmin)
        ->post(route('master.data-gtk.import'), [
            'school_id' => $this->school->id,
            'file' => $uploadedFile,
        ]);

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->school->id]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('gtks', [
        'school_id' => $this->school->id,
        'nama_lengkap' => 'Rahmat Hidayat',
        'nip' => '198012122008011005',
        'nik' => '7308011212800005',
        'status_kepegawaian' => 'PNS',
    ]);

    // Verifikasi akun User dibuat otomatis dengan NIP sebagai username & password default
    $this->assertDatabaseHas('users', [
        'username' => '198012122008011005',
        'school_id' => $this->school->id,
        'user_type' => 'guru',
    ]);

    @unlink($tempPath);
});

test('super admin can import spreadsheet with only essential columns and auto-generates credentials from name slug', function () {
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Nama Lengkap*');
    $sheet->setCellValue('B1', 'Jenis Kelamin (L/P)*');
    $sheet->setCellValue('C1', 'Status Kepegawaian*');
    $sheet->setCellValue('D1', 'Jenis PTK (pisahkan koma jika jamak)*');

    // Row 2 data (only 4 essential columns, no NIP or NIK)
    $sheet->setCellValue('A2', 'Dewi Sartika');
    $sheet->setCellValue('B2', 'P');
    $sheet->setCellValue('C2', 'Honorer Sekolah');
    $sheet->setCellValue('D2', 'Guru Kelas');

    $tempPath = tempnam(sys_get_temp_dir(), 'gtk_essential_test_').'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);

    $uploadedFile = new UploadedFile(
        $tempPath,
        'gtk_essential_import.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    $response = $this->actingAs($this->superAdmin)
        ->post(route('master.data-gtk.import'), [
            'school_id' => $this->school->id,
            'file' => $uploadedFile,
        ]);

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->school->id]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('gtks', [
        'school_id' => $this->school->id,
        'nama_lengkap' => 'Dewi Sartika',
        'jenis_kelamin' => 'P',
        'status_kepegawaian' => 'Honorer Sekolah',
    ]);

    // Verifikasi akun User dibuat otomatis dengan slug nama lengkap
    $user = User::where('username', 'dewisartika')
        ->where('school_id', $this->school->id)
        ->first();

    expect($user)->not->toBeNull();
    expect($user->user_type)->toBe('guru');
    expect(Hash::check('dewisartika', $user->password))->toBeTrue();
    expect($user->needsProfileCompletion())->toBeTrue();

    @unlink($tempPath);
});

test('super admin can import legacy 17-column gtk spreadsheet without errors', function () {
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Nama Lengkap*');
    $sheet->setCellValue('B1', 'Gelar Depan');
    $sheet->setCellValue('C1', 'Gelar Belakang');
    $sheet->setCellValue('D1', 'Jenis Kelamin (L/P)*');
    $sheet->setCellValue('E1', 'Status Kepegawaian*');
    $sheet->setCellValue('F1', 'Jenis PTK*');
    $sheet->setCellValue('G1', 'NIK');
    $sheet->setCellValue('H1', 'NIP');
    $sheet->setCellValue('I1', 'NUPTK');
    $sheet->setCellValue('J1', 'Tempat Lahir');
    $sheet->setCellValue('K1', 'Tanggal Lahir');
    $sheet->setCellValue('L1', 'Agama');
    $sheet->setCellValue('M1', 'Pendidikan Terakhir');
    $sheet->setCellValue('N1', 'Jurusan Pendidikan');
    $sheet->setCellValue('O1', 'No HP/WhatsApp');
    $sheet->setCellValue('P1', 'Email');
    $sheet->setCellValue('Q1', 'Alamat Domisili');

    // Row 2 data
    $sheet->setCellValue('A2', 'Legacy Teacher');
    $sheet->setCellValue('B2', 'Dr.');
    $sheet->setCellValue('C2', 'M.Pd.');
    $sheet->setCellValue('D2', 'L');
    $sheet->setCellValue('E2', 'PNS');
    $sheet->setCellValue('F2', 'Guru Mapel');
    $sheet->setCellValue('G2', '7308019988770001');
    $sheet->setCellValue('H2', '198201012009011009');
    $sheet->setCellValue('I2', '9876543210123456');
    $sheet->setCellValue('J2', 'Bone');
    $sheet->setCellValue('K2', '1982-01-01');
    $sheet->setCellValue('L2', 'Islam');
    $sheet->setCellValue('M2', 'S2');
    $sheet->setCellValue('N2', 'Pendidikan Bahasa');
    $sheet->setCellValue('O2', '081299112233');
    $sheet->setCellValue('P2', 'legacy@sekolah.sch.id');
    $sheet->setCellValue('Q2', 'Jl. Pendidikan No. 1');

    $tempPath = tempnam(sys_get_temp_dir(), 'gtk_legacy_test_').'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);

    $uploadedFile = new UploadedFile(
        $tempPath,
        'gtk_legacy_import.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    $response = $this->actingAs($this->superAdmin)
        ->post(route('master.data-gtk.import'), [
            'school_id' => $this->school->id,
            'file' => $uploadedFile,
        ]);

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->school->id]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('gtks', [
        'school_id' => $this->school->id,
        'nama_lengkap' => 'Legacy Teacher',
        'gelar_depan' => 'Dr.',
        'gelar_belakang' => 'M.Pd.',
        'nip' => '198201012009011009',
        'nik' => '7308019988770001',
    ]);

    @unlink($tempPath);
});

test('importing gtk with existing nik or nip updates the existing record', function () {
    $existing = Gtk::factory()->create([
        'school_id' => $this->school->id,
        'nama_lengkap' => 'Nama Lama',
        'nik' => '7308019999990001',
        'nip' => '198501012010011001',
        'status_kepegawaian' => 'Honorer',
    ]);

    // Spreadsheet with updated data for the same NIK
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Nama Lengkap*');
    $sheet->setCellValue('D1', 'Jenis Kelamin (L/P)*');
    $sheet->setCellValue('E1', 'Status Kepegawaian*');
    $sheet->setCellValue('F1', 'Jenis PTK*');
    $sheet->setCellValue('G1', 'NIK');
    $sheet->setCellValue('H1', 'NIP');

    $sheet->setCellValue('A2', 'Nama Baru Terupdate');
    $sheet->setCellValue('D2', 'L');
    $sheet->setCellValue('E2', 'PNS');
    $sheet->setCellValue('F2', 'Guru Mapel');
    $sheet->setCellValue('G2', '7308019999990001');
    $sheet->setCellValue('H2', '198501012010011001');

    $tempPath = tempnam(sys_get_temp_dir(), 'gtk_test_').'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);

    $uploadedFile = new UploadedFile(
        $tempPath,
        'gtk_update.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    $response = $this->actingAs($this->superAdmin)
        ->post(route('master.data-gtk.import'), [
            'school_id' => $this->school->id,
            'file' => $uploadedFile,
        ]);

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->school->id]));
    $response->assertSessionHas('success');

    // Pastikan tidak ada duplikasi data, record lama diupdate
    expect(Gtk::where('school_id', $this->school->id)->where('nik', '7308019999990001')->count())->toBe(1);

    $existing->refresh();
    expect($existing->nama_lengkap)->toBe('Nama Baru Terupdate');
    expect($existing->status_kepegawaian)->toBe('PNS');

    @unlink($tempPath);
});

test('importing gtk with same full name without nik or nip updates the existing record', function () {
    $existing = Gtk::factory()->create([
        'school_id' => $this->school->id,
        'nama_lengkap' => 'Budi Santoso',
        'nik' => null,
        'nip' => null,
        'status_kepegawaian' => 'Honorer Sekolah',
    ]);

    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Nama Lengkap*');
    $sheet->setCellValue('B1', 'Jenis Kelamin (L/P)*');
    $sheet->setCellValue('C1', 'Status Kepegawaian*');
    $sheet->setCellValue('D1', 'Jenis PTK (pisahkan koma jika jamak)*');

    $sheet->setCellValue('A2', 'Budi Santoso');
    $sheet->setCellValue('B2', 'L');
    $sheet->setCellValue('C2', 'PNS');
    $sheet->setCellValue('D2', 'Guru Mapel');

    $tempPath = tempnam(sys_get_temp_dir(), 'gtk_test_name_upsert_').'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);

    $uploadedFile = new UploadedFile(
        $tempPath,
        'gtk_name_update.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    $response = $this->actingAs($this->superAdmin)
        ->post(route('master.data-gtk.import'), [
            'school_id' => $this->school->id,
            'file' => $uploadedFile,
        ]);

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->school->id]));
    $response->assertSessionHas('success');

    expect(Gtk::where('school_id', $this->school->id)->where('nama_lengkap', 'Budi Santoso')->count())->toBe(1);

    $existing->refresh();
    expect($existing->status_kepegawaian)->toBe('PNS');

    @unlink($tempPath);
});

test('import gtk validates file type and presence', function () {
    $response = $this->actingAs($this->superAdmin)
        ->post(route('master.data-gtk.import'), [
            'school_id' => $this->school->id,
            // tanpa file
        ]);

    $response->assertSessionHasErrors('file');

    $invalidFile = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

    $responseInvalid = $this->actingAs($this->superAdmin)
        ->post(route('master.data-gtk.import'), [
            'school_id' => $this->school->id,
            'file' => $invalidFile,
        ]);

    $responseInvalid->assertSessionHasErrors('file');
});

test('super admin importing gtk with multiple schools targets the selected school in payload and not the first school', function () {
    $schoolA = School::factory()->create([
        'name' => 'MAS Pompanua',
        'is_active' => true,
    ]);
    $schoolB = School::factory()->create([
        'name' => 'SMAN 20 Bone',
        'is_active' => true,
    ]);

    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Nama Lengkap*');
    $sheet->setCellValue('B1', 'Jenis Kelamin (L/P)*');
    $sheet->setCellValue('C1', 'Status Kepegawaian*');
    $sheet->setCellValue('D1', 'Jenis PTK (pisahkan koma jika jamak)*');

    $sheet->setCellValue('A2', 'Guru Khusus SMAN 20');
    $sheet->setCellValue('B2', 'L');
    $sheet->setCellValue('C2', 'PNS');
    $sheet->setCellValue('D2', 'Guru Mapel');

    $tempPath = tempnam(sys_get_temp_dir(), 'gtk_multischool_').'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);

    $uploadedFile = new UploadedFile(
        $tempPath,
        'gtk_multischool.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    // Super admin submits to import route with school_id = School B (SMAN 20 Bone)
    $response = $this->actingAs($this->superAdmin)
        ->post(route('master.data-gtk.import'), [
            'school_id' => $schoolB->id,
            'file' => $uploadedFile,
        ]);

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $schoolB->id]));
    $response->assertSessionHas('success');

    // Pastikan data tersimpan di SMAN 20 Bone (School B), BUKAN di MAS Pompanua (School A)
    $this->assertDatabaseHas('gtks', [
        'school_id' => $schoolB->id,
        'nama_lengkap' => 'Guru Khusus Sman 20',
    ]);
    $this->assertDatabaseMissing('gtks', [
        'school_id' => $schoolA->id,
        'nama_lengkap' => 'Guru Khusus Sman 20',
    ]);

    @unlink($tempPath);
});

test('super admin can import spreadsheet using simplified Guru and Staf categories', function () {
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'Nama Lengkap');
    $sheet->setCellValue('B1', 'Jenis Kelamin');
    $sheet->setCellValue('C1', 'Status Kepegawaian');
    $sheet->setCellValue('D1', 'Peran / Jenis PTK');

    // Baris Guru
    $sheet->setCellValue('A2', 'Guru Bahasa Indonesia');
    $sheet->setCellValue('B2', 'P');
    $sheet->setCellValue('C2', 'PNS');
    $sheet->setCellValue('D2', 'Guru');

    // Baris Staf
    $sheet->setCellValue('A3', 'Staf Administrasi TU');
    $sheet->setCellValue('B3', 'L');
    $sheet->setCellValue('C3', 'Honorer Sekolah');
    $sheet->setCellValue('D3', 'Staf');

    $tempPath = tempnam(sys_get_temp_dir(), 'gtk_simplified_').'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);

    $uploadedFile = new UploadedFile(
        $tempPath,
        'gtk_simplified.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    $response = $this->actingAs($this->superAdmin)
        ->post(route('master.data-gtk.import'), [
            'school_id' => $this->school->id,
            'file' => $uploadedFile,
        ]);

    $response->assertRedirect(route('master.data-gtk', ['school_id' => $this->school->id]));
    $response->assertSessionHas('success');

    $guru = Gtk::where('nama_lengkap', 'Guru Bahasa Indonesia')->first();
    expect($guru)->not->toBeNull();
    expect($guru->jenis_ptk)->toEqual(['Guru Mata Pelajaran']);
    expect($guru->user->user_type)->toBe('guru');

    $staf = Gtk::where('nama_lengkap', 'Staf Administrasi TU')->first();
    expect($staf)->not->toBeNull();
    expect($staf->jenis_ptk)->toEqual(['Tenaga Administrasi Sekolah (Staf TU)']);
    expect($staf->user->user_type)->toBe('staf');

    @unlink($tempPath);
});
