{{-- Modal Dialog Import Data GTK --}}
<x-modal
    name="import"
    id="modalImportGtk"
    title="Import Data GTK dari Excel"
    :subtitle="'Unggah berkas spreadsheet data guru dan pegawai untuk ' . ($selectedSchool?->name ?? 'unit sekolah')"
    action="{{ route('master.data-gtk.import', array_filter(['school_id' => $selectedSchoolId])) }}"
    form-id="formImportGtk"
    cancel-btn-id="btnCancelImportGtk"
    confirm-btn-id="btnSubmitImportGtk"
    template-url="{{ route('master.data-gtk.template') }}"
    :school-id="$selectedSchoolId"
    :guidelines="[
        'Template import dibuat sangat ringkas, hanya membutuhkan 4 data pokok: <strong>Nama Lengkap</strong>, <strong>Jenis Kelamin (L/P)</strong>, <strong>Status Kepegawaian</strong>, dan <strong>Peran / Jenis PTK</strong>.',
        'Kolom <strong>Peran / Jenis PTK</strong> cukup diisi <code>Guru</code> atau <code>Staf</code> (dapat juga diisi penugasan spesifik seperti <code>Guru Mata Pelajaran</code>, <code>Wali Kelas</code>, dll).',
        'Akun login pengguna dibuat otomatis menggunakan nama guru (tanpa spasi) sebagai <em>username</em> dan kata sandi awal (contoh: <code>dewisartika</code>).',
        'Data yang sudah ada di sekolah ini akan diperbarui otomatis (<em>Upsert</em>) jika ditemukan kecocokan NIP, NIK, atau Nama Lengkap.',
        'Data lanjutan (NIP, NIK, NUPTK, alamat domisili Kemendagri, pendidikan, tanggal lahir, kontak, dan pasfoto) dapat dilengkapi mandiri oleh guru via menu <strong>Pengaturan Profil</strong>.',
    ]"
/>
