{{-- Seksi Input Progres Pengembangan Fitur (Opsional, untuk halaman Under Development) --}}
<div class="form-section-divider"></div>
<div class="form-section-label">Progres Pengembangan (Jika Fitur Belum Aktif)</div>

<div class="form-grid-2col">
    <x-form-input
        type="number"
        name="dev_progress"
        id="{{ $idPrefix }}_dev_progress"
        label="Persentase Progres (%)"
        :value="old('dev_progress', 0)"
        min="0"
        max="100"
        hint="Rentang 0 - 100%. Otomatis 100% saat rute sudah terdaftar."
    />

    <x-form-input
        name="dev_version"
        id="{{ $idPrefix }}_dev_version"
        label="Target Versi"
        :value="old('dev_version')"
        placeholder="Contoh: v0.9-alpha"
        maxlength="30"
    />
</div>

<x-form-input
    name="dev_stage"
    id="{{ $idPrefix }}_dev_stage"
    label="Tahap Pengerjaan"
    :value="old('dev_stage')"
    placeholder="Contoh: Tahap Integrasi Data Sistem"
    maxlength="120"
/>

<x-form-input
    name="dev_note"
    id="{{ $idPrefix }}_dev_note"
    label="Catatan Progres"
    :value="old('dev_note')"
    placeholder="Contoh: Proses sinkronisasi komponen antarmuka"
    maxlength="255"
/>
