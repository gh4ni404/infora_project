{{-- Kartu Utama: Informasi Pribadi, Avatar & Kredensial Username --}}
<div class="card-surface">
    <div class="card-header">
        <div>
            <h3 class="font-weight-bold font-size-base mb-1">Informasi Pribadi & Kredensial Akun</h3>
            <p class="brand-subtitle font-size-xs mb-0">Kelola foto avatar, identitas diri, data kependudukan, dan nama pengguna (username) akun Anda</p>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="tab" value="personal">

            {{-- Seksi Foto Avatar Profil --}}
            <div class="form-section-label mb-2">Foto Avatar Profil</div>
            <p class="brand-subtitle font-size-xs mb-3">Foto resmi ini digunakan pada navigasi akun, kartu identitas, dan riwayat aktivitas sistem.</p>

            <div class="mb-4">
                <x-image-upload
                    name="foto"
                    id="profile_avatar"
                    shape="circle"
                    :value="$user->avatar_url"
                    remove-name="remove_foto"
                    placeholder="Klik atau seret pasfoto baru"
                    hint="Format berkas: PNG, JPG, JPEG, WebP. Maks 1MB."
                />
            </div>

            <div class="form-section-divider mt-4 mb-4"></div>

            {{-- Seksi Identitas & Biodata Pribadi --}}
            <div class="form-section-label mb-2">Identitas & Biodata Pribadi</div>

            <div class="form-grid-2col">
                <x-form-input
                    name="nama_lengkap"
                    id="profile_nama_lengkap"
                    label="Nama Lengkap"
                    :value="old('nama_lengkap', $gtk?->nama_lengkap ?? $user->name)"
                    required
                    data-transform="title-case"
                    placeholder="Nama lengkap tanpa gelar"
                    hint="Nama resmi sesuai kartu identitas / dokumen kependudukan."
                />

                @if (! $gtk)
                    <x-form-input
                        type="email"
                        name="email"
                        id="profile_email"
                        label="Alamat Email"
                        :value="old('email', $user->email)"
                        placeholder="Contoh: admin@sekolah.sch.id"
                        maxlength="100"
                        hint="Digunakan untuk notifikasi sistem dan proses pemulihan akses."
                    />
                @endif

                @if ($gtk)
                    <div class="form-grid-2col">
                        <x-form-input
                            name="gelar_depan"
                            id="profile_gelar_depan"
                            label="Gelar Depan"
                            :value="old('gelar_depan', $gtk->gelar_depan)"
                            placeholder="Contoh: Dr., Drs."
                            maxlength="50"
                        />
                        <x-form-input
                            name="gelar_belakang"
                            id="profile_gelar_belakang"
                            label="Gelar Belakang"
                            :value="old('gelar_belakang', $gtk->gelar_belakang)"
                            placeholder="Contoh: S.Pd., M.Kom."
                            maxlength="50"
                        />
                    </div>

                    <x-form-select
                        name="jenis_kelamin"
                        id="profile_jenis_kelamin"
                        label="Jenis Kelamin"
                        placeholder="Pilih Jenis Kelamin"
                        :options="['L' => 'Laki-laki', 'P' => 'Perempuan']"
                        :selected="old('jenis_kelamin', $gtk->jenis_kelamin)"
                        required
                    />

                    <x-form-input
                        name="nik"
                        id="profile_nik"
                        label="NIK (Nomor Induk Kependudukan)"
                        :value="old('nik', $gtk->nik)"
                        placeholder="16 digit angka sesuai KTP"
                        maxlength="20"
                    />

                    <x-form-input
                        name="tempat_lahir"
                        id="profile_tempat_lahir"
                        label="Tempat Lahir"
                        :value="old('tempat_lahir', $gtk->tempat_lahir)"
                        data-transform="title-case"
                        placeholder="Contoh: Watampone"
                        maxlength="100"
                    />

                    <x-form-input
                        type="date"
                        name="tanggal_lahir"
                        id="profile_tanggal_lahir"
                        label="Tanggal Lahir"
                        :value="old('tanggal_lahir', $gtk->tanggal_lahir?->format('Y-m-d'))"
                    />

                    <x-form-select
                        name="agama"
                        id="profile_agama"
                        label="Agama"
                        placeholder="Pilih Agama"
                        :options="[
                            'Islam' => 'Islam',
                            'Kristen Protestan' => 'Kristen Protestan',
                            'Katolik' => 'Katolik',
                            'Hindu' => 'Hindu',
                            'Buddha' => 'Buddha',
                            'Khonghucu' => 'Khonghucu',
                        ]"
                        :selected="old('agama', $gtk->agama)"
                    />
                @endif
            </div>

            <div class="form-section-divider mt-4 mb-4"></div>

            {{-- Seksi Kredensial Akun: Nama Pengguna (Username) --}}
            <div class="form-section-label mb-2">Kredensial Akun Pengguna</div>
            <p class="brand-subtitle font-size-xs mb-3">Username digunakan sebagai tanda pengenal akun Anda untuk masuk (login) ke dalam sistem.</p>

            <div class="form-grid-2col">
                <x-form-input
                    name="username"
                    id="profile_username"
                    label="Username Login"
                    :value="old('username', $user->username)"
                    required
                    placeholder="Masukkan username akun"
                    maxlength="50"
                    hint="Minimal 3 karakter. Hanya huruf, angka, tanda strip (-), dan garis bawah (_)."
                />
            </div>

            <div class="mt-4 pt-3 border-top text-right">
                <button type="submit" class="btn-primary">
                    <x-icon name="check" size="16" />
                    <span>Simpan Informasi Pribadi</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Kartu Kedua: Keamanan & Pembaruan Kata Sandi --}}
<div class="card-surface mt-4" id="cardProfileSecurity">
    <div class="card-header">
        <div>
            <h3 class="font-weight-bold font-size-base mb-1">Keamanan & Ubah Kata Sandi</h3>
            <p class="brand-subtitle font-size-xs mb-0">Pastikan akun Anda terlindungi dengan menggunakan kata sandi yang kuat dan unik</p>
        </div>
    </div>

    <div class="card-body">
        <div class="alert alert-info mb-4">
            <div class="d-flex align-items-center gap-2">
                <x-icon name="info" size="18" />
                <div class="font-size-xs">
                    <strong>Perhatian Sesi:</strong> Demi keamanan akun, setelah kata sandi berhasil diperbarui, sistem akan secara otomatis mengakhiri sesi aktif (<em>logout</em>) dan meminta Anda untuk masuk kembali menggunakan kata sandi baru.
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.password.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <x-form-input
                    type="password"
                    name="current_password"
                    id="profile_current_password"
                    label="Kata Sandi Saat Ini"
                    required
                    placeholder="Masukkan kata sandi lama Anda"
                    hint="Wajib diisi untuk memverifikasi kepemilikan akun."
                />
            </div>

            <div class="form-grid-2col">
                <x-form-input
                    type="password"
                    name="password"
                    id="profile_new_password"
                    label="Kata Sandi Baru"
                    required
                    minlength="8"
                    placeholder="Minimal 8 karakter"
                    hint="Gunakan kombinasi huruf, angka, atau simbol agar lebih aman."
                />

                <x-form-input
                    type="password"
                    name="password_confirmation"
                    id="profile_password_confirmation"
                    label="Konfirmasi Kata Sandi Baru"
                    required
                    minlength="8"
                    placeholder="Ulangi kata sandi baru Anda"
                    hint="Pastikan sama persis dengan kata sandi baru di samping."
                />
            </div>

            <div class="mt-4 pt-3 border-top text-right">
                <button type="submit" class="btn-primary">
                    <x-icon name="lock" size="16" />
                    <span>Perbarui Kata Sandi</span>
                </button>
            </div>
        </form>
    </div>
</div>
