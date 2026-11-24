{{-- Modal Dialog Import Data Murid --}}
<x-modal
    name="import"
    id="modalImportMurid"
    title="Import Data Murid dari Excel"
    :subtitle="'Unggah berkas spreadsheet data peserta didik untuk ' . ($selectedSchool?->name ?? 'unit sekolah')"
    action="{{ route('master.data-murid.import', array_filter(['school_id' => $selectedSchoolId])) }}"
    form-id="formImportMurid"
    cancel-btn-id="btnCancelImportMurid"
    confirm-btn-id="btnSubmitImportMurid"
    template-url="{{ route('master.data-murid.template', array_filter(['school_id' => $selectedSchoolId])) }}"
    :school-id="$selectedSchoolId"
    :guidelines="[
        'Gunakan format template resmi (.xlsx) yang dapat diunduh melalui tombol <strong>Unduh Format Template</strong>.',
        'Data esensial wajib: <strong>Nama Lengkap</strong> dan <strong>Jenis Kelamin (L/P)</strong>.',
        $selectedSchool?->isMadrasah()
            ? 'Khusus madrasah (Kemenag), kolom <strong>NISM</strong> wajib diisi tepat <strong>18 digit angka</strong> (12 digit NSM, 2 digit tahun masuk, 4 digit nomor urut).'
            : 'Kolom <strong>NIS</strong> bersifat opsional untuk sekolah umum (maksimal 30 karakter).',
        'Format template terdiri atas 6 kolom esensial (Nama Lengkap, Jenis Kelamin, NISN, ' . ($selectedSchool?->isMadrasah() ? 'NISM' : 'NIS') . ', Angkatan, Email).',
        'Penempatan rombel / kelas dapat dipetakan secara terpisah melalui menu <strong>Rombel</strong>.',
        'Format NISN dan ' . ($selectedSchool?->isMadrasah() ? 'NISM' : 'NIS') . ' akan otomatis dibersihkan dan disimpan sebagai teks numerik murni tanpa notasi ilmiah.',
        'Akun login pengguna otomatis dibuat untuk setiap murid baru dengan username dari NISN / ' . ($selectedSchool?->isMadrasah() ? 'NISM' : 'NIS') . ' dan sandi default.',
        'Data yang sudah ada akan diperbarui otomatis (<em>Upsert</em>) jika ditemukan kecocokan nomor induk atau nama lengkap.',
    ]"
/>
