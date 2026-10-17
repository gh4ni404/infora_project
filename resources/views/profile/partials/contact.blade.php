<div class="card-surface">
    <div class="card-header">
        <div>
            <h3 class="font-weight-bold font-size-base mb-1">Kontak & Alamat Domisili</h3>
            <p class="brand-subtitle font-size-xs mb-0">Informasi saluran komunikasi aktif, alamat surat elektronik, dan lokasi domisili tempat tinggal</p>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="tab" value="contact">

            <div class="form-section-label mb-2">Saluran Komunikasi</div>

            <div class="form-grid-2col">
                <x-form-input
                    name="no_hp"
                    id="profile_no_hp"
                    label="Nomor WhatsApp / HP"
                    :value="old('no_hp', $gtk?->no_hp)"
                    placeholder="Contoh: 081234567890"
                    maxlength="30"
                    hint="Nomor aktif untuk koordinasi kedinasan."
                />

                <x-form-input
                    type="email"
                    name="email"
                    id="profile_email"
                    label="Alamat Email Pribadi / Dinas"
                    :value="old('email', $user->email)"
                    placeholder="Contoh: user@sekolah.sch.id"
                    maxlength="100"
                    hint="Digunakan untuk notifikasi sistem dan proses pemulihan akses."
                />
            </div>

            @if ($gtk)
                <div class="form-section-label mt-4 mb-2">Wilayah Domisili Tempat Tinggal</div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="profile_provinsi_id" class="form-label">Provinsi</label>
                        <x-searchable-select
                            name="provinsi_id"
                            id="profile_provinsi_id"
                            placeholder="Pilih Provinsi"
                            search-placeholder="Cari provinsi..."
                            :options="$provinsiList ?? []"
                            :value="old('provinsi_id', $gtk->provinsi_id)"
                        />
                        @error('provinsi_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="profile_kabupaten_id" class="form-label">Kabupaten / Kota</label>
                        <x-searchable-select
                            name="kabupaten_id"
                            id="profile_kabupaten_id"
                            placeholder="Pilih Kabupaten/Kota"
                            search-placeholder="Cari kabupaten/kota..."
                            :disabled="!old('provinsi_id', $gtk->provinsi_id)"
                            :value="old('kabupaten_id', $gtk->kabupaten_id)"
                        />
                        @error('kabupaten_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="profile_kecamatan_id" class="form-label">Kecamatan</label>
                        <x-searchable-select
                            name="kecamatan_id"
                            id="profile_kecamatan_id"
                            placeholder="Pilih Kecamatan"
                            search-placeholder="Cari kecamatan..."
                            :disabled="!old('kabupaten_id', $gtk->kabupaten_id)"
                            :value="old('kecamatan_id', $gtk->kecamatan_id)"
                        />
                        @error('kecamatan_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="profile_kelurahan_id" class="form-label">Kelurahan / Desa</label>
                        <x-searchable-select
                            name="kelurahan_id"
                            id="profile_kelurahan_id"
                            placeholder="Pilih Kelurahan/Desa"
                            search-placeholder="Cari kelurahan/desa..."
                            :disabled="!old('kecamatan_id', $gtk->kecamatan_id)"
                            :value="old('kelurahan_id', $gtk->kelurahan_id)"
                        />
                        @error('kelurahan_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <x-form-textarea
                        name="alamat"
                        id="profile_alamat"
                        label="Alamat Tempat Tinggal Lengkap"
                        :value="old('alamat', $gtk->alamat)"
                        placeholder="Alamat jalan, RT/RW, nomor rumah, dan detail tempat tinggal..."
                        rows="3"
                    />
                </div>
            @endif

            <div class="mt-4 pt-3 border-top text-right">
                <button type="submit" class="btn-primary">
                    <x-icon name="check" size="16" />
                    <span>Simpan Kontak & Alamat</span>
                </button>
            </div>
        </form>
    </div>
</div>
