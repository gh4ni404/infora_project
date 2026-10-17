<?php

use App\Models\Gtk;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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

test('super admin can download gtk import template', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('master.data-gtk.template'));

    $response->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('Template_Import_GTK_INFORA.xlsx');
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

test('super admin can import valid gtk spreadsheet and create user account', function () {
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
    $sheet->setCellValue('A2', 'Rahmat Hidayat');
    $sheet->setCellValue('B2', 'Dr.');
    $sheet->setCellValue('C2', 'M.Pd.');
    $sheet->setCellValue('D2', 'L');
    $sheet->setCellValue('E2', 'PNS');
    $sheet->setCellValue('F2', 'Guru Mapel, Wali Kelas');
    $sheet->setCellValue('G2', '7308011212800005');
    $sheet->setCellValue('H2', '198012122008011005');
    $sheet->setCellValue('I2', '9876543210123456');
    $sheet->setCellValue('J2', 'Bone');
    $sheet->setCellValue('K2', '1980-12-12');
    $sheet->setCellValue('L2', 'Islam');
    $sheet->setCellValue('M2', 'S2');
    $sheet->setCellValue('N2', 'Manajemen Pendidikan');
    $sheet->setCellValue('O2', '081299887766');
    $sheet->setCellValue('P2', 'rahmat.hidayat@sekolah.sch.id');
    $sheet->setCellValue('Q2', 'Jl. Jenderal Sudirman No. 45');

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
