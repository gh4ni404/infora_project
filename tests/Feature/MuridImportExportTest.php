<?php

use App\Models\Kelas;
use App\Models\Murid;
use App\Models\Referensi;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_murid_import_test',
        'email' => 'superadmin_murid_import@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_murid_import_test',
        'email' => 'guru_murid_import@infora.test',
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $this->school = School::factory()->smk()->create([
        'name' => 'SMK Negeri 1 Watampone',
        'is_active' => true,
    ]);

    $this->kelas = Kelas::factory()->create([
        'school_id' => $this->school->id,
        'nama' => 'X PPLG 1',
        'is_active' => true,
    ]);
});

test('guest cannot download template, export, or import murid', function () {
    $this->get(route('master.data-murid.template'))->assertRedirect(route('login'));
    $this->get(route('master.data-murid.export'))->assertRedirect(route('login'));
    $this->post(route('master.data-murid.import'))->assertRedirect(route('login'));
});

test('non super admin without permission cannot download murid template', function () {
    $this->actingAs($this->regularUser)
        ->get(route('master.data-murid.template'))
        ->assertForbidden();
});

test('non super admin without permission cannot export murid', function () {
    $this->actingAs($this->regularUser)
        ->get(route('master.data-murid.export'))
        ->assertForbidden();
});

test('non super admin without permission cannot import murid', function () {
    $this->actingAs($this->regularUser)
        ->post(route('master.data-murid.import'))
        ->assertForbidden();
});

test('super admin can download murid import template with standard columns and guide sheet', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('master.data-murid.template', ['school_id' => $this->school->id]));

    $response->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('Template_Import_Data_Murid_INFORA.xlsx');

    $tempFile = tempnam(sys_get_temp_dir(), 'tpl_murid_').'.xlsx';
    file_put_contents($tempFile, $response->streamedContent());

    $reader = new XlsxReader;
    $spreadsheet = $reader->load($tempFile);

    expect($spreadsheet->getSheetCount())->toBe(2);

    $sheet0 = $spreadsheet->getSheet(0);
    expect($sheet0->getTitle())->toBe('Template Murid');
    expect($sheet0->getCell('A1')->getValue())->toBe('Nama Lengkap*');
    expect($sheet0->getCell('B1')->getValue())->toBe('Jenis Kelamin (L/P)*');
    expect($sheet0->getCell('C1')->getValue())->toBe('NISN');
    expect($sheet0->getCell('D1')->getValue())->toBe('NIS');
    expect($sheet0->getCell('E1')->getValue())->toBe('Angkatan');
    expect($sheet0->getCell('F1')->getValue())->toBe('Email');
    expect($sheet0->getCell('G1')->getValue())->toBeNull();

    $sheet1 = $spreadsheet->getSheet(1);
    expect($sheet1->getTitle())->toBe('Panduan & Referensi');

    @unlink($tempFile);
});

test('super admin can export murid data as spreadsheet', function () {
    Murid::factory()->create([
        'school_id' => $this->school->id,
        'nama_lengkap' => 'Muhammad Fikri',
        'nisn' => '0089999999',
        'nis' => '24001',
        'kelas_id' => $this->kelas->id,
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->get(route('master.data-murid.export', ['school_id' => $this->school->id]));

    $response->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('Data_Murid_');
});

test('super admin can import murid from excel file and automatically create user accounts', function () {
    // Buat berkas excel sementara dengan 6 kolom esensial
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Nama Lengkap*');
    $sheet->setCellValue('B1', 'Jenis Kelamin (L/P)*');
    $sheet->setCellValue('C1', 'NISN');
    $sheet->setCellValue('D1', 'NIS');
    $sheet->setCellValue('E1', 'Angkatan');
    $sheet->setCellValue('F1', 'Email');

    // Baris 2: Murid 1
    $sheet->setCellValue('A2', 'Rizky Ramadhan');
    $sheet->setCellValue('B2', 'L');
    $sheet->setCellValue('C2', '0081230001');
    $sheet->setCellValue('D2', '24010');
    $sheet->setCellValue('E2', '2026');
    $sheet->setCellValue('F2', 'rizky@infora.test');

    // Baris 3: Murid 2
    $sheet->setCellValue('A3', 'Nurul Aini');
    $sheet->setCellValue('B3', 'P');
    $sheet->setCellValue('C3', '0081230002');
    $sheet->setCellValue('D3', '24011');
    $sheet->setCellValue('E3', '2026');
    $sheet->setCellValue('F3', 'nurul@infora.test');

    $tempPath = tempnam(sys_get_temp_dir(), 'import_murid_').'.xlsx';
    $writer = new XlsxWriter($spreadsheet);
    $writer->save($tempPath);

    $uploadedFile = new UploadedFile(
        $tempPath,
        'Import_Murid_Test.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-murid.import'), [
        'school_id' => $this->school->id,
        'file' => $uploadedFile,
    ]);

    $response->assertRedirect(route('master.data-murid.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('murids', [
        'school_id' => $this->school->id,
        'nama_lengkap' => 'Rizky Ramadhan',
        'nisn' => '0081230001',
        'nis' => '24010',
        'email' => 'rizky@infora.test',
        'kelas_id' => null,
    ]);

    $this->assertDatabaseHas('murids', [
        'school_id' => $this->school->id,
        'nama_lengkap' => 'Nurul Aini',
        'nisn' => '0081230002',
        'nis' => '24011',
        'email' => 'nurul@infora.test',
        'kelas_id' => null,
    ]);

    // Pastikan akun user login terbuat
    $this->assertDatabaseHas('users', [
        'username' => '0081230001',
        'user_type' => 'murid',
    ]);

    $this->assertDatabaseHas('users', [
        'username' => '0081230002',
        'user_type' => 'murid',
    ]);

    @unlink($tempPath);
});

test('import murid rejects invalid file formats', function () {
    $tempText = tempnam(sys_get_temp_dir(), 'text_').'.txt';
    file_put_contents($tempText, 'invalid file format');

    $uploadedFile = new UploadedFile($tempText, 'test.txt', 'text/plain', null, true);

    $response = $this->actingAs($this->superAdmin)->post(route('master.data-murid.import'), [
        'school_id' => $this->school->id,
        'file' => $uploadedFile,
    ]);

    $response->assertSessionHasErrors(['file']);
    @unlink($tempText);
});

test('super admin downloading template for madrasah gets NISM column header and 18-digit samples', function () {
    $schoolTypeCat = Referensi::firstOrCreate(
        ['kode' => 'school_type'],
        ['nama' => 'Jenis Sekolah', 'is_active' => true, 'is_system' => true]
    );

    Referensi::firstOrCreate(
        ['kode' => 'MA'],
        [
            'referensi_id' => $schoolTypeCat->id,
            'nama' => 'MA',
            'extra_attributes' => ['is_madrasah' => true],
            'is_active' => true,
            'is_system' => true,
        ]
    );
    Referensi::clearCache();

    $this->school->update([
        'name' => 'MAN 1 Watampone',
        'school_type' => 'MA',
    ]);
    $madrasah = $this->school->fresh();

    $response = $this->actingAs($this->superAdmin)
        ->get(route('master.data-murid.template'));

    $response->assertOk();

    $tempFile = tempnam(sys_get_temp_dir(), 'tpl_ma_').'.xlsx';
    file_put_contents($tempFile, $response->streamedContent());

    $reader = new XlsxReader;
    $spreadsheet = $reader->load($tempFile);

    $sheet0 = $spreadsheet->getSheet(0);
    expect($sheet0->getCell('D1')->getValue())->toBe('NISM*');
    expect((string) $sheet0->getCell('D2')->getValue())->toHaveLength(18);

    @unlink($tempFile);
});

test('madrasah import rejects invalid nism with 6 digits and accepts valid 18-digit nism', function () {
    $schoolTypeCat = Referensi::firstOrCreate(
        ['kode' => 'school_type'],
        ['nama' => 'Jenis Sekolah', 'is_active' => true, 'is_system' => true]
    );

    Referensi::firstOrCreate(
        ['kode' => 'MA'],
        [
            'referensi_id' => $schoolTypeCat->id,
            'nama' => 'MA',
            'extra_attributes' => ['is_madrasah' => true],
            'is_active' => true,
            'is_system' => true,
        ]
    );
    Referensi::clearCache();

    $this->school->update([
        'name' => 'MAN 1 Watampone',
        'school_type' => 'MA',
    ]);
    $madrasah = $this->school->fresh();

    // Berkas 1: Berisi NISM 6 digit (tidak valid untuk madrasah)
    $spreadsheetInvalid = new Spreadsheet;
    $sheetInvalid = $spreadsheetInvalid->getActiveSheet();
    $sheetInvalid->setCellValue('A1', 'Nama Lengkap*');
    $sheetInvalid->setCellValue('B1', 'Jenis Kelamin (L/P)*');
    $sheetInvalid->setCellValue('C1', 'NISN');
    $sheetInvalid->setCellValue('D1', 'NISM*');
    $sheetInvalid->setCellValue('E1', 'Angkatan');
    $sheetInvalid->setCellValue('F1', 'Email');

    $sheetInvalid->setCellValue('A2', 'Siswa Gagal Validasi');
    $sheetInvalid->setCellValue('B2', 'L');
    $sheetInvalid->setCellValue('C2', '0089990001');
    $sheetInvalid->setCellValue('D2', '24001'); // Hanya 5-6 digit!
    $sheetInvalid->setCellValue('E2', '2026');

    $tempInvalid = tempnam(sys_get_temp_dir(), 'invalid_ma_').'.xlsx';
    (new XlsxWriter($spreadsheetInvalid))->save($tempInvalid);

    $responseInvalid = $this->actingAs($this->superAdmin)->post(route('master.data-murid.import'), [
        'school_id' => $madrasah->id,
        'file' => new UploadedFile($tempInvalid, 'invalid.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
    ]);

    // Harus dialihkan kembali dengan pesan error karena tidak ada baris yang valid
    $responseInvalid->assertSessionHas('error');
    $this->assertDatabaseMissing('murids', [
        'school_id' => $madrasah->id,
        'nama_lengkap' => 'Siswa Gagal Validasi',
    ]);
    @unlink($tempInvalid);

    // Berkas 2: Berisi NISM 18 digit yang valid
    $validNism = '131273710001260001';
    $spreadsheetValid = new Spreadsheet;
    $sheetValid = $spreadsheetValid->getActiveSheet();
    $sheetValid->setCellValue('A1', 'Nama Lengkap*');
    $sheetValid->setCellValue('B1', 'Jenis Kelamin (L/P)*');
    $sheetValid->setCellValue('C1', 'NISN');
    $sheetValid->setCellValue('D1', 'NISM*');
    $sheetValid->setCellValue('E1', 'Angkatan');
    $sheetValid->setCellValue('F1', 'Email');

    $sheetValid->setCellValue('A2', 'Siswa Sukses MA');
    $sheetValid->setCellValue('B2', 'L');
    $sheetValid->setCellValue('C2', '0089990002');
    $sheetValid->setCellValue('D2', $validNism);
    $sheetValid->setCellValue('E2', '2026');

    $tempValid = tempnam(sys_get_temp_dir(), 'valid_ma_').'.xlsx';
    (new XlsxWriter($spreadsheetValid))->save($tempValid);

    $responseValid = $this->actingAs($this->superAdmin)->post(route('master.data-murid.import'), [
        'school_id' => $madrasah->id,
        'file' => new UploadedFile($tempValid, 'valid.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
    ]);

    $responseValid->assertSessionHas('success');
    $this->assertDatabaseHas('murids', [
        'school_id' => $madrasah->id,
        'nama_lengkap' => 'Siswa Sukses MA',
        'nis' => $validNism,
    ]);
    @unlink($tempValid);
});
