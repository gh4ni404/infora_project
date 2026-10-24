<div class="card-surface">
    <div class="card-header">
        <div>
            <h3 class="font-weight-bold font-size-base mb-1">Klasifikasi Kepegawaian & Kualifikasi Pendidikan</h3>
            <p class="brand-subtitle font-size-xs mb-0">Status kepegawaian resmi, nomor registrasi kedinasan, dan kualifikasi pendidikan terakhir</p>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <x-form-input type="hidden" name="tab" value="employment" />

            <div class="form-section-label mb-2">Klasifikasi Kepegawaian & Pendidikan</div>

            <div class="form-grid-2col">
                <x-form-select
                    name="status_kepegawaian"
                    id="profile_status_kepegawaian"
                    label="Status Kepegawaian"
                    placeholder="Pilih Status Kepegawaian"
                    :options="collect($statusKepegawaianList ?? [])->mapWithKeys(fn($item) => [$item => $item])->all()"
                    :selected="old('status_kepegawaian', $gtk?->status_kepegawaian)"
                />

                <x-form-input
                    name="nip"
                    id="profile_nip"
                    label="NIP (Nomor Induk Pegawai)"
                    :value="old('nip', $gtk?->nip)"
                    placeholder="18 digit angka (khusus PNS/PPPK)"
                    maxlength="30"
                    hint="Nomor Induk Pegawai bagi ASN atau PPPK."
                />

                <x-form-input
                    name="nuptk"
                    id="profile_nuptk"
                    label="NUPTK"
                    :value="old('nuptk', $gtk?->nuptk)"
                    placeholder="16 digit angka pendidik"
                    maxlength="30"
                    hint="Nomor Unik Pendidik dan Tenaga Kependidikan."
                />

                <x-form-select
                    name="pendidikan_terakhir"
                    id="profile_pendidikan_terakhir"
                    label="Pendidikan Terakhir"
                    placeholder="Pilih Jenjang Pendidikan"
                    :options="collect($pendidikanList ?? [])->mapWithKeys(fn($item) => [$item => $item])->all()"
                    :value="old('pendidikan_terakhir', $gtk?->pendidikan_terakhir)"
                />

                <div class="col-span-full">
                    <x-form-input
                        name="jurusan_pendidikan"
                        id="profile_jurusan_pendidikan"
                        label="Jurusan / Program Studi"
                        :value="old('jurusan_pendidikan', $gtk?->jurusan_pendidikan)"
                        data-transform="title-case"
                        placeholder="Contoh: Pendidikan Matematika, Ilmu Komputer"
                        maxlength="100"
                    />
                </div>
            </div>

            <div class="mt-4 pt-3 border-top text-right">
                <button type="submit" class="btn-primary">
                    <x-icon name="check" size="16" />
                    <span>Simpan Klasifikasi & Pendidikan</span>
                </button>
            </div>
        </form>

        @if ($gtk)
            <div class="mt-4 pt-3 border-top">
                <div class="form-section-label mb-2">Penugasan & Jenis PTK yang Diemban</div>
                <p class="brand-subtitle font-size-xs mb-3">Daftar peranan dan tugas pokok yang tercatat dalam pangkalan data satuan pendidikan:</p>
                <div class="d-flex flex-wrap gap-2">
                    @forelse ($gtk->jenis_ptk ?? [] as $ptk)
                        <span class="badge badge-primary">{{ $ptk }}</span>
                    @empty
                        <span class="text-muted font-size-sm">Belum ada penugasan terdaftar.</span>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>
