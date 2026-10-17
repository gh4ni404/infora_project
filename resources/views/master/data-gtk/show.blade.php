{{-- Modal Detail / Pratinjau Profil GTK --}}
<x-modal
    id="modalShowGtk"
    title="Profil Pendidik & Tenaga Kependidikan"
    subtitle="Rincian lengkap data biodata, kepegawaian, kontak, dan kredensial sistem"
    :with-form="false">

    <div class="user-profile-header mb-4 p-3 bg-surface border rounded-xl flex items-center gap-3">
        <div class="user-avatar-circle avatar-lg" id="showGtkAvatarContainer">
            <img id="showGtkAvatarImg" src="" alt="Avatar" class="hidden">
            <span id="showGtkAvatarInitials">GTK</span>
        </div>
        <div>
            <h3 class="m-0 font-bold" id="showGtkNamaBergelar">-</h3>
            <div class="text-muted text-xs mt-1" id="showGtkSchoolName">-</div>
            <div class="flex flex-wrap gap-1 mt-2">
                <span class="badge badge-primary" id="showGtkJenisPtk">-</span>
                <span class="badge badge-cyan" id="showGtkStatusKepegawaian">-</span>
                <span class="badge badge-success" id="showGtkStatusAktif">Aktif</span>
            </div>
        </div>
    </div>

    {{-- Detail Grid: 2 Kolom --}}
    <div class="form-grid-2col">
        {{-- Kolom Kiri: Biodata Diri & Kepegawaian --}}
        <div>
            <div class="form-section-label">Identitas & Kepegawaian</div>
            <div class="table-responsive">
                <table class="table-borderless w-full text-sm">
                    <tbody>
                        <tr>
                            <td class="text-muted py-1 w-2/5">NIP</td>
                            <td class="font-weight-semibold py-1" id="showGtkNip">-</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">NUPTK</td>
                            <td class="font-weight-semibold py-1" id="showGtkNuptk">-</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">NIK</td>
                            <td class="font-weight-semibold py-1" id="showGtkNik">-</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">Jenis Kelamin</td>
                            <td class="font-weight-semibold py-1" id="showGtkJenisKelamin">-</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">TTL</td>
                            <td class="font-weight-semibold py-1" id="showGtkTtl">-</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">Agama</td>
                            <td class="font-weight-semibold py-1" id="showGtkAgama">-</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">Penugasan PTK</td>
                            <td class="font-weight-semibold py-1">
                                <div class="flex flex-wrap gap-1" id="showGtkRolesList">-</div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">Pendidikan</td>
                            <td class="font-weight-semibold py-1" id="showGtkPendidikan">-</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">Jurusan / Prodi</td>
                            <td class="font-weight-semibold py-1" id="showGtkJurusan">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kolom Kanan: Kontak & Akun Pengguna --}}
        <div>
            <div class="form-section-label">Kontak & Domisili</div>
            <div class="table-responsive mb-3">
                <table class="table-borderless w-full text-sm">
                    <tbody>
                        <tr>
                            <td class="text-muted py-1 w-2/5">No. HP / WA</td>
                            <td class="font-weight-semibold py-1" id="showGtkNoHp">-</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">Email</td>
                            <td class="font-weight-semibold py-1" id="showGtkEmail">-</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">Wilayah</td>
                            <td class="font-weight-semibold py-1" id="showGtkWilayah">-</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">Alamat</td>
                            <td class="font-weight-semibold py-1" id="showGtkAlamat">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="form-section-label">Akun Pengguna Sistem</div>
            <div class="table-responsive">
                <table class="table-borderless w-full text-sm">
                    <tbody>
                        <tr>
                            <td class="text-muted py-1 w-2/5">Username</td>
                            <td class="font-weight-semibold py-1">
                                <span class="badge badge-purple" id="showGtkUsername">-</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">Tipe Pengguna</td>
                            <td class="font-weight-semibold py-1" id="showGtkUserType">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCloseShowGtk">Tutup</button>
    </x-slot:footer>
</x-modal>
