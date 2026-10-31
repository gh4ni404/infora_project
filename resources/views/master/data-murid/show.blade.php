{{-- Modal Pratinjau Detail Profil Murid --}}
<x-modal
    id="modalShowMurid"
    size="lg"
    title="Detail Profil Murid"
    subtitle="Pratinjau lengkap informasi identitas, akademik rombel, orang tua, dan akun peserta didik"
    :with-form="false">

    {{-- Header Banner Profil Murid --}}
    <div class="card-surface border rounded-2xl p-5 mb-5 bg-gradient-to-r from-slate-50 to-blue-50/40">
        <div class="flex items-start gap-4">
            <div class="user-avatar-circle avatar-lg" id="showMuridAvatarWrap">
                <img id="showMuridAvatarImg" src="" alt="Foto Murid" class="hidden">
                <span id="showMuridInitials">MD</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-lg font-bold text-slate-800 leading-snug" id="showMuridNamaLengkap">-</h3>
                    <span id="showMuridStatusAktif" class="badge badge-success">Aktif</span>
                </div>
                <div class="text-xs text-slate-500 font-medium mt-0.5" id="showMuridSchoolName">-</div>
                <div class="flex items-center gap-2 mt-2 flex-wrap">
                    <span class="badge badge-primary" id="showMuridKelasNama">-</span>
                    <span class="badge badge-neutral" id="showMuridJk">-</span>
                    <span class="badge badge-cyan" id="showMuridAngkatan">-</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Rincian Tabular Data Murid --}}
    <div class="space-y-5 text-sm">
        {{-- Kelompok 1: Identitas & Akademik --}}
        <div>
            <div class="form-section-label">Identitas & Akademik Murid</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2 bg-slate-50/50 p-3.5 rounded-xl border border-slate-200/70">
                <div>
                    <span class="text-xs text-slate-500 block">NISN</span>
                    <span class="font-semibold text-slate-800 font-mono" id="showMuridNisn">-</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">{{ $selectedSchool?->isMadrasah() ? 'NISM (Madrasah)' : 'Nomor Induk Murid (NIS)' }}</span>
                    <span class="font-semibold text-slate-800 font-mono" id="showMuridNis">-</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">NIK (Nomor Induk Kependudukan)</span>
                    <span class="font-semibold text-slate-800 font-mono" id="showMuridNik">-</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Tempat, Tanggal Lahir</span>
                    <span class="font-semibold text-slate-800" id="showMuridTtl">-</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Agama</span>
                    <span class="font-semibold text-slate-800" id="showMuridAgama">-</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Tingkatan & Jurusan</span>
                    <span class="font-semibold text-slate-800" id="showMuridTingkatanJurusan">-</span>
                </div>
            </div>
        </div>

        {{-- Kelompok 2: Kontak & Domisili --}}
        <div>
            <div class="form-section-label">Kontak & Domisili Administratif</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2 bg-slate-50/50 p-3.5 rounded-xl border border-slate-200/70">
                <div>
                    <span class="text-xs text-slate-500 block">No. HP / WhatsApp Murid</span>
                    <span class="font-semibold text-slate-800" id="showMuridNoHp">-</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Email Murid</span>
                    <span class="font-semibold text-slate-800" id="showMuridEmail">-</span>
                </div>
                <div class="col-span-full">
                    <span class="text-xs text-slate-500 block">Wilayah Kemendagri</span>
                    <span class="font-semibold text-slate-800" id="showMuridWilayah">-</span>
                </div>
                <div class="col-span-full">
                    <span class="text-xs text-slate-500 block">Alamat Jalan / Dusun</span>
                    <span class="font-medium text-slate-700" id="showMuridAlamat">-</span>
                </div>
            </div>
        </div>

        {{-- Kelompok 3: Data Orang Tua / Wali --}}
        <div>
            <div class="form-section-label">Data Orang Tua / Wali Murid</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2 bg-slate-50/50 p-3.5 rounded-xl border border-slate-200/70">
                <div>
                    <span class="text-xs text-slate-500 block">Nama Ayah</span>
                    <span class="font-semibold text-slate-800" id="showMuridAyah">-</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Nama Ibu</span>
                    <span class="font-semibold text-slate-800" id="showMuridIbu">-</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Nama Wali</span>
                    <span class="font-semibold text-slate-800" id="showMuridWali">-</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">No. HP / WhatsApp Orang Tua / Wali</span>
                    <span class="font-semibold text-slate-800 text-brand" id="showMuridNoHpOrtu">-</span>
                </div>
            </div>
        </div>

        {{-- Kelompok 4: Kredensial Akun Pengguna --}}
        <div>
            <div class="form-section-label">Akun Pengguna Sistem (User Login)</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2 bg-slate-50/50 p-3.5 rounded-xl border border-slate-200/70">
                <div>
                    <span class="text-xs text-slate-500 block">Username Login</span>
                    <span class="font-mono font-semibold text-slate-800" id="showMuridUsername">-</span>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">Tipe Pengguna</span>
                    <span class="badge badge-role-murid">Murid (Peserta Didik)</span>
                </div>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCloseShowMurid">Tutup</button>
    </x-slot:footer>
</x-modal>
