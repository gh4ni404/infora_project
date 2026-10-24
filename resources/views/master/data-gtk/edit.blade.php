{{-- Modal Ubah Data GTK Component (Streamlined: Kepegawaian, Jenis PTK & Kredensial Akun) --}}
<x-modal
    id="modalEditGtk"
    size="lg"
    title="Ubah Data Pendidik & Tenaga Kependidikan"
    subtitle="Perbarui identitas kepegawaian, penugasan PTK, atau kredensial akun pengguna"
    method="PUT"
    form-id="formEditGtk">

    <x-form-input type="hidden" name="school_id" id="edit_school_id" :value="$selectedSchoolId" />

    {{-- Seksi 1: Identitas Pokok & Klasifikasi Kepegawaian --}}
    <div class="form-section-label">Identitas Pokok & Klasifikasi Kepegawaian</div>
    <div class="form-grid-2col">
        <div class="col-span-full">
            <x-form-input
                name="nama_lengkap"
                id="edit_nama_lengkap"
                label="Nama Lengkap"
                data-transform="title-case"
                placeholder="Contoh: Budi Santoso (tanpa gelar)"
                required
            />
        </div>

        <x-form-input
            name="gelar_depan"
            id="edit_gelar_depan"
            label="Gelar Depan"
            placeholder="Contoh: Dr., Drs."
            maxlength="50"
        />

        <x-form-input
            name="gelar_belakang"
            id="edit_gelar_belakang"
            label="Gelar Belakang"
            placeholder="Contoh: S.Pd., M.Kom."
            maxlength="50"
        />

        <x-form-select
            name="jenis_kelamin"
            id="edit_jenis_kelamin"
            label="Jenis Kelamin"
            placeholder="Pilih Jenis Kelamin"
            :options="['L' => 'Laki-laki', 'P' => 'Perempuan']"
            required
        />

        <x-form-select
            name="status_kepegawaian"
            id="edit_status_kepegawaian"
            label="Status Kepegawaian"
            placeholder="Pilih Status"
            :options="collect($statusKepegawaianList)->mapWithKeys(fn($item) => [$item => $item])->all()"
            required
        />

        <x-form-input
            name="nip"
            id="edit_nip"
            label="NIP (Nomor Induk Pegawai)"
            placeholder="18 digit angka (khusus PNS/PPPK)"
            maxlength="30"
        />

        <x-form-input
            name="nuptk"
            id="edit_nuptk"
            label="NUPTK"
            placeholder="16 digit angka pendidik"
            maxlength="30"
        />

        <div class="col-span-full">
            <x-form-input
                name="nik"
                id="edit_nik"
                label="NIK (Nomor Induk Kependudukan)"
                placeholder="16 digit angka sesuai KTP"
                maxlength="20"
            />
        </div>
    </div>

    {{-- Seksi 2: Penugasan PTK Majemuk --}}
    <div class="form-section-divider"></div>
    <div class="form-section-label">Jenis PTK & Penugasan (Dapat Memilih Lebih dari Satu)</div>
    <div class="form-hint mb-3">Centang seluruh tugas atau peranan yang diemban oleh GTK ini di satuan pendidikan.</div>

    @error('jenis_ptk')
        <x-alert type="danger" class="mb-3">{{ $message }}</x-alert>
    @enderror

    <div class="form-grid-2col">
        {{-- Kelompok Penugasan Pendidik (Guru) --}}
        <div class="card-surface border rounded-xl overflow-hidden flex flex-col ptk-role-card">
            <div class="flex items-center justify-between px-3.5 py-2.5 bg-slate-50 border-b border-slate-200/80 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="badge badge-primary">Pendidik</span>
                    <span class="text-xs font-semibold text-slate-800">Tugas Guru & Pengajar</span>
                </div>
                <span class="text-xs text-muted font-normal">{{ count($jenisPtkPendidik) }} Peran</span>
            </div>
            <div class="p-3 overflow-y-auto flex-1 space-y-1.5 ptk-role-scroll">
                @foreach ($jenisPtkPendidik as $ptk)
                    <label class="form-check cursor-pointer hover:bg-slate-50 p-1.5 rounded transition-colors" for="edit_ptk_{{ Str::slug($ptk) }}">
                        <input
                            type="checkbox"
                            name="jenis_ptk[]"
                            value="{{ $ptk }}"
                            id="edit_ptk_{{ Str::slug($ptk) }}"
                            class="form-check-input edit-jenis-ptk-cb"
                        >
                        <span class="form-check-label text-sm text-slate-700 select-none">{{ $ptk }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Kelompok Penugasan Tenaga Kependidikan (Tendik) --}}
        <div class="card-surface border rounded-xl overflow-hidden flex flex-col ptk-role-card">
            <div class="flex items-center justify-between px-3.5 py-2.5 bg-slate-50 border-b border-slate-200/80 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="badge badge-purple">Tendik</span>
                    <span class="text-xs font-semibold text-slate-800">Tugas Tata Usaha & Staf</span>
                </div>
                <span class="text-xs text-muted font-normal">{{ count($jenisPtkTendik) }} Peran</span>
            </div>
            <div class="p-3 overflow-y-auto flex-1 space-y-1.5 ptk-role-scroll">
                @foreach ($jenisPtkTendik as $ptk)
                    <label class="form-check cursor-pointer hover:bg-slate-50 p-1.5 rounded transition-colors" for="edit_ptk_{{ Str::slug($ptk) }}">
                        <input
                            type="checkbox"
                            name="jenis_ptk[]"
                            value="{{ $ptk }}"
                            id="edit_ptk_{{ Str::slug($ptk) }}"
                            class="form-check-input edit-jenis-ptk-cb"
                        >
                        <span class="form-check-label text-sm text-slate-700 select-none">{{ $ptk }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Seksi 3: Kredensial Akun Pengguna & Hak Akses Sistem --}}
    <div class="form-section-divider"></div>
    <div class="form-section-label">Kredensial Akun Pengguna & Hak Akses Sistem</div>

    <div class="card-surface p-3 mb-3 border bg-slate-50 rounded-xl">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <span class="badge badge-purple" id="edit_account_username_badge">
                    <x-icon name="user" size="14" />
                    <span id="edit_account_username_text">-</span>
                </span>
                <span class="badge badge-neutral" id="edit_account_role_badge">
                    <span id="edit_account_role_text">-</span>
                </span>
                <span class="badge badge-success" id="edit_account_status_badge">
                    <span id="edit_account_status_text">Aktif</span>
                </span>
            </div>
            <a href="#" id="edit_account_menu_access_btn" class="btn-link text-xs font-semibold inline-flex items-center gap-1" target="_blank">
                <span>Atur Izin Menu Granular</span>
                <x-icon name="arrow-right" size="12" />
            </a>
        </div>
    </div>

    <div class="form-grid-2col">
        <x-form-input
            name="username"
            id="edit_username"
            label="Username Login"
            placeholder="Username saat ini"
            maxlength="50"
        />

        <x-form-input
            type="password"
            name="password"
            id="edit_password"
            label="Ganti Kata Sandi (Opsional)"
            placeholder="Kosongkan jika tidak ingin mengubah kata sandi"
            hint="Hanya diisi jika ingin memperbarui kata sandi pengguna ini."
        />

        <div class="col-span-full">
            <x-form-select
                name="role_template"
                id="edit_role_template"
                label="Ubah Template Peran & Hak Akses"
                placeholder="Biarkan jika tidak ingin mereset peran"
                :options="$roleTemplates->mapWithKeys(fn($r) => [$r->role_key => $r->role_name . ' (' . ucfirst($r->role_category) . ')'])->all()"
                hint="Memilih template baru akan memperbarui seluruh matriks izin menu GTK ini."
            />
        </div>
    </div>

    <div class="mt-3">
        <x-form-checkbox
            name="is_active"
            id="edit_is_active"
            label="GTK aktif dan akun pengguna dapat mengakses sistem"
            value="1"
        />
    </div>

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditGtk">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitEditGtk">Perbarui Data</button>
    </x-slot:footer>
</x-modal>
