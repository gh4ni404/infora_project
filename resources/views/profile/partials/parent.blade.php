<div class="card-surface">
    <div class="card-header">
        <div>
            <h3 class="font-weight-bold font-size-base mb-1">Data Orang Tua / Wali Siswa</h3>
            <p class="brand-subtitle font-size-xs mb-0">Informasi identitas ayah, ibu kandung, atau wali serta kontak darurat peserta didik</p>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <x-form-input type="hidden" name="tab" value="parent" />

            <div class="form-section-label mb-2">Data Orang Tua Kandung & Wali</div>
            <p class="brand-subtitle font-size-xs mb-3">Data ini digunakan pihak sekolah untuk keperluan koordinasi akademik, komunikasi darurat, dan rapor.</p>

            <div class="form-grid-2col">
                <x-form-input
                    name="nama_ayah"
                    id="profile_nama_ayah"
                    label="Nama Ayah Kandung"
                    :value="old('nama_ayah', $murid->nama_ayah)"
                    data-transform="title-case"
                    placeholder="Nama lengkap ayah kandung"
                    maxlength="150"
                />

                <x-form-input
                    name="nama_ibu"
                    id="profile_nama_ibu"
                    label="Nama Ibu Kandung"
                    :value="old('nama_ibu', $murid->nama_ibu)"
                    data-transform="title-case"
                    placeholder="Nama lengkap ibu kandung"
                    maxlength="150"
                />

                <x-form-input
                    name="nama_wali"
                    id="profile_nama_wali"
                    label="Nama Wali (Opsional)"
                    :value="old('nama_wali', $murid->nama_wali)"
                    data-transform="title-case"
                    placeholder="Nama lengkap wali jika tinggal bersama wali"
                    maxlength="150"
                />

                <x-form-input
                    type="tel"
                    name="no_hp_ortu"
                    id="profile_no_hp_ortu"
                    label="No. HP / WhatsApp Orang Tua / Wali"
                    :value="old('no_hp_ortu', $murid->no_hp_ortu)"
                    placeholder="Contoh: 081298765432"
                    maxlength="30"
                    hint="Nomor penting untuk pengumuman sekolah dan panggilan darurat."
                />
            </div>

            <div class="mt-4 pt-3 border-top text-right">
                <button type="submit" class="btn-primary">
                    <x-icon name="check" size="16" />
                    <span>Simpan Data Orang Tua / Wali</span>
                </button>
            </div>
        </form>
    </div>
</div>
