{{-- Modal Dialog Import Data GTK --}}
<x-modal
    id="modalImportGtk"
    title="Import Data GTK dari Excel"
    subtitle="Unggah berkas spreadsheet untuk pendaftaran atau pembaruan massal data guru dan pegawai"
    action="{{ route('master.data-gtk.import') }}"
    size="md"
    form-id="formImportGtk"
    enctype="multipart/form-data">

    <input type="hidden" name="school_id" id="import_school_id" value="{{ $selectedSchoolId }}">

    {{-- Kartu Unduh Format Template --}}
    <div class="card-surface p-3 mb-3 border">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="font-semibold text-sm">Unduh Template Berkas</div>
                <div class="text-xs text-muted">Format standar berkas Excel dilengkapi baris contoh data dan panduan.</div>
            </div>
            <a href="{{ route('master.data-gtk.template') }}" class="btn-secondary whitespace-nowrap">
                <x-icon name="download" size="14" />
                <span>Format Template</span>
            </a>
        </div>
    </div>

    {{-- Form Input Berkas Spreadsheet --}}
    <div class="form-group mb-3">
        <label for="import_file" class="form-label font-semibold">Pilih Berkas Excel / CSV <span class="text-danger">*</span></label>
        <input
            type="file"
            name="file"
            id="import_file"
            class="form-control"
            accept=".xlsx,.xls,.csv"
            required
        >
        <div class="form-hint text-xs mt-1">Mendukung berkas berekstensi <strong>.xlsx</strong>, <strong>.xls</strong>, atau <strong>.csv</strong> (Maksimal 5 MB).</div>
        @error('file')
            <div class="form-error text-danger text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Panduan Pengisian Format Spreadsheet --}}
    <div class="form-section-divider my-3"></div>
    <div class="text-xs text-muted">
        <div class="font-semibold text-slate-700 dark:text-slate-300 mb-1">Panduan Pengisian:</div>
        <ul class="list-disc pl-4 space-y-1">
            <li>Kolom <strong>Nama Lengkap</strong>, <strong>Jenis Kelamin (L/P)</strong>, <strong>Status Kepegawaian</strong>, dan <strong>Jenis PTK</strong> wajib diisi.</li>
            <li>Untuk penugasan tugas jamak pada <strong>Jenis PTK</strong>, pisahkan dengan koma (contoh: <em>Guru Mapel, Wali Kelas</em>).</li>
            <li>Format tanggal lahir harus menggunakan standar <code>YYYY-MM-DD</code> (contoh: <code>1985-04-12</code>).</li>
            <li>Data dengan <strong>NIK</strong> atau <strong>NIP</strong> yang sudah terdaftar akan diperbarui otomatis (<em>Upsert</em>).</li>
            <li>Akun login pengguna beserta kata sandi default akan digenerate otomatis oleh sistem.</li>
        </ul>
    </div>

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelImportGtk">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitImportGtk">
            <x-icon name="upload" size="16" />
            <span>Unggah & Impor</span>
        </button>
    </x-slot:footer>
</x-modal>
