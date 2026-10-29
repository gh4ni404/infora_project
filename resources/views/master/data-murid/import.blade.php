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
        'Kolom <strong>Kelas / Rombel</strong> diisi dengan nama kelas yang sesuai (contoh: <code>X PPLG 1</code>) agar murid langsung dialokasikan ke kelas.',
        'Format NISN dan NIS akan otomatis dibersihkan dan disimpan sebagai teks numerik murni tanpa notasi ilmiah.',
        'Akun login pengguna otomatis dibuat untuk setiap murid baru dengan username dari NISN / NIS dan sandi default.',
        'Data yang sudah ada akan diperbarui otomatis (<em>Upsert</em>) jika ditemukan kecocokan NISN, NIS, atau Nama Lengkap.',
    ]"
/>
