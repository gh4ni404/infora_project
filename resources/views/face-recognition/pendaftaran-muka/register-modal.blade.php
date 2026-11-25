{{-- Modal Pendaftaran Sampel Wajah Murid --}}
<x-modal
    id="modalRegisterFace"
    size="lg"
    title="Daftarkan Wajah Murid"
    subtitle="Ambil foto melalui kamera atau unggah gambar untuk mengekstrak vektor wajah 128-dimensi"
    action="{{ route('pendaftaran_muka.store') }}"
    form-id="formRegisterFace">

    <x-form-input type="hidden" name="school_id" id="register_school_id" :value="$selectedSchoolId" />
    <x-form-input type="hidden" name="foto_base64" id="register_foto_base64" />
    <x-form-input type="hidden" name="face_descriptor" id="register_face_descriptor" />
    <x-form-input type="hidden" name="confidence" id="register_confidence" />

    <div class="form-grid-2col">
        <div class="form-group">
            <label for="register_murid_id" class="form-label">
                Peserta Didik <span class="text-danger">*</span>
            </label>
            <x-searchable-select
                name="murid_id"
                id="register_murid_id"
                placeholder="Pilih Peserta Didik"
                search-placeholder="Cari nama, NISN, atau kelas..."
                :options="$muridOptions"
                :value="old('murid_id')"
                required
            />
            @error('murid_id')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <x-form-select
            name="label"
            id="register_label"
            label="Label Pose / Kondisi Wajah"
            :selected="old('label', 'Tampak Depan')"
            :options="[
                'Tampak Depan' => 'Tampak Depan (Utama)',
                'Agak Kiri' => 'Agak Kiri',
                'Agak Kanan' => 'Agak Kanan',
                'Berkacamata' => 'Berkacamata',
                'Ekspresi Senyum' => 'Ekspresi Senyum',
            ]"
            required
        />
    </div>

    <div class="form-section-divider"></div>

    <div class="flex items-center justify-between gap-3 flex-wrap">
        <x-tab-pills label="Metode Pengambilan Foto Wajah">
            <x-tab-pill id="tabModeCamera" :active="true" icon="camera">
                Kamera / Webcam
            </x-tab-pill>
            <x-tab-pill id="tabModeUpload" :active="false" icon="upload-image">
                Unggah Foto
            </x-tab-pill>
        </x-tab-pills>

        <span class="text-xs text-slate-500" id="faceDetectionFeedback">
            Arahkan 1 wajah murid ke kamera lalu ambil foto.
        </span>
    </div>

    {{-- Peringatan Akses Kamera Non-HTTPS --}}
    <div id="faceCameraInsecureAlert" class="hidden">
        <x-alert type="warning" title="Kamera Tidak Dapat Diakses" :dismissible="false">
            Browser memerlukan koneksi <strong>HTTPS</strong> atau <code>localhost</code> dan izin kamera aktif.
            Anda tetap dapat langsung mendaftarkan wajah melalui tab <strong>Unggah Foto</strong>.
        </x-alert>
    </div>

    {{-- Stage Kamera / Pratinjau Foto & Deteksi Wajah --}}
    <div class="face-stage">
        <div class="face-stage-badge" id="faceStageBadge">
            <x-icon name="scan-face" size="14" />
            <span id="faceStageBadgeText">Kamera Siap</span>
        </div>

        <video id="faceCameraVideo" class="face-stage-video hidden" playsinline muted></video>
        <canvas id="faceOverlayCanvas" class="face-stage-overlay"></canvas>
        <img id="faceSnapshotImg" src="" alt="Pratinjau Sampel Wajah" class="face-stage-snapshot hidden">

        <div class="face-stage-placeholder" id="faceStagePlaceholder">
            <x-icon name="scan-face" size="42" />
            <span class="text-sm font-medium" id="faceStagePlaceholderText">
                Klik "Aktifkan Kamera" untuk mulai memindai wajah.
            </span>
        </div>
    </div>

    {{-- Kontrol Mode Kamera --}}
    <div id="panelCameraActions" class="face-stage-controls">
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" class="btn-secondary" id="btnStartFaceCamera">
                <x-icon name="camera" size="16" />
                <span>Aktifkan Kamera</span>
            </button>
            <button type="button" class="btn-primary" id="btnCaptureFaceCamera" disabled>
                <x-icon name="scan-face" size="16" />
                <span>Ambil & Ekstrak Wajah</span>
            </button>
            <button type="button" class="btn-secondary hidden" id="btnRetakeFaceCamera">
                <x-icon name="refresh-cw" size="16" />
                <span>Ambil Ulang</span>
            </button>
        </div>
        <span class="text-xs text-slate-500">Pastikan pencahayaan cukup &amp; hanya ada 1 wajah.</span>
    </div>

    {{-- Kontrol Mode Unggah Foto --}}
    <div id="panelUploadActions" class="hidden">
        <input type="file" id="faceUploadInput" accept="image/png,image/jpeg,image/webp" class="hidden">
        <div class="dropzone-upload" id="faceUploadDropzone">
            <div class="dropzone-upload-content">
                <x-icon name="upload-image" size="28" />
                <span class="image-upload-prompt">Klik atau seret foto wajah murid ke sini</span>
                <span class="form-hint">Format: JPG, PNG, WebP. Foto otomatis dioptimalkan &amp; dipindai wajahnya.</span>
            </div>
        </div>
    </div>

    <x-form-checkbox
        name="sync_murid_foto"
        id="register_sync_murid_foto"
        label="Gunakan foto ini sebagai pasfoto profil murid (jika murid belum memiliki pasfoto)"
        value="1"
        :checked="old('sync_murid_foto', '1') === '1'"
    />

    <x-form-error name="foto_base64" />
    <x-form-error name="face_descriptor" />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelRegisterFace">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitRegisterFace" disabled>
            <x-icon name="save" size="16" />
            <span>Simpan Sampel Wajah</span>
        </button>
    </x-slot:footer>
</x-modal>
