@extends('layouts.app')

@section('content')
    <x-page-header title="Data Murid (Peserta Didik)">
        <x-slot:subtitle>
            Kelola master data murid, penempatan rombongan belajar, data orang tua/wali, dan akun login pengguna
            @if ($selectedSchool)
                &ndash; <strong class="text-brand">{{ $selectedSchool->name }}</strong>
                <span class="badge {{ $selectedSchool->school_type === 'SMK' ? 'badge-primary' : 'badge-cyan' }}">
                    {{ $selectedSchool->school_type }}
                </span>
            @endif
        </x-slot:subtitle>

        <x-slot:actions>
            {{-- Selector Unit Sekolah untuk Super Admin --}}
            @if ($schools->count() > 1)
                <form method="GET" action="{{ route('master.data-murid') }}" id="schoolFilterForm">
                    <x-form-select
                        name="school_id"
                        id="school_id"
                        :selected="$selectedSchoolId"
                        :options="$schools->mapWithKeys(fn($school) => [$school->id => $school->name . ' (' . $school->school_type . ')'])"
                        onchange="this.form.submit()"
                        title="Pilih Unit Sekolah"
                    />
                </form>
            @endif

            <x-crud-button
                action="create"
                id="btnOpenImportMurid"
                icon="upload"
                title="Import data murid dari berkas Excel atau CSV"
            />

            <x-crud-button
                action="create"
                tag="link"
                :href="route('master.data-murid.export', request()->query())"
                icon="download"
                title="Export data murid ke berkas Excel"
                data-no-progress
                download
            />

            <x-crud-button
                action="create"
                id="btnOpenCreateMurid"
                title="Tambah Murid"
            />
        </x-slot:actions>
    </x-page-header>

    {{-- Flash Alerts --}}
    <x-alert type="success" />
    <x-alert type="error" />
    <x-alert type="validation" />

    {{-- Kartu Metrik Ringkasan (KPI Cards) --}}
    <x-stats-grid>
        <x-stat-card icon="users" color="primary" label="Total Murid" :value="number_format($totalCount, 0, ',', '.')" />
        <x-stat-card icon="user" color="cyan" label="Laki-laki (L)" :value="number_format($lakiCount, 0, ',', '.')" />
        <x-stat-card icon="user" color="purple" label="Perempuan (P)" :value="number_format($perempuanCount, 0, ',', '.')" />
        <x-stat-card icon="check-circle" color="success" label="Murid Aktif" :value="number_format($activeCount, 0, ',', '.')" />
        <x-stat-card icon="alert-circle" color="amber" label="Belum Masuk Kelas" :value="number_format($tanpaKelasCount, 0, ',', '.')" />
    </x-stats-grid>

    {{-- Tabel Data Murid --}}
    <x-data-table
        route="{{ route('master.data-murid') }}"
        search-placeholder="Cari nama lengkap, NISN, NIS, NIK, atau no. HP..."
        :per-page="$perPageInput">

        <x-slot:filters>
            <x-form-input type="hidden" name="school_id" :value="$selectedSchoolId" />

            {{-- Filter Kelas --}}
            @php
                $kelasOptions = ['' => 'Semua Rombel', 'tanpa_kelas' => 'Tanpa Rombel (Belum Ada Kelas)'];
                foreach ($kelasList as $k) {
                    $kelasOptions[(string) $k->id] = $k->nama . ($k->tingkatan ? ' (' . $k->tingkatan->nama . ')' : '');
                }
            @endphp
            <x-form-select
                name="kelas_id"
                id="filterKelasId"
                class="form-select-sm"
                placeholder="Semua Rombel"
                onchange="this.form.submit()"
                :selected="request('kelas_id')"
                :options="$kelasOptions"
            />

            {{-- Filter Tingkatan --}}
            <x-form-select
                name="tingkatan_id"
                id="filterTingkatanId"
                class="form-select-sm"
                placeholder="Semua Tingkat"
                onchange="this.form.submit()"
                :selected="request('tingkatan_id')"
                :options="collect($tingkatanList)->mapWithKeys(fn($item) => [(string) $item->id => $item->nama])->prepend('Semua Tingkat', '')->all()"
            />

            {{-- Filter Jenis Kelamin --}}
            <x-form-select
                name="jenis_kelamin"
                id="filterJenisKelamin"
                class="form-select-sm"
                placeholder="Semua L/P"
                onchange="this.form.submit()"
                :selected="request('jenis_kelamin')"
                :options="['' => 'Semua L/P', 'L' => 'Laki-laki', 'P' => 'Perempuan']"
            />

            {{-- Filter Status Siswa --}}
            <x-form-select
                name="status"
                id="filterStatus"
                class="form-select-sm"
                placeholder="Semua Status Siswa"
                onchange="this.form.submit()"
                :selected="request('status')"
                :options="[
                    '' => 'Semua Status Siswa',
                    'aktif' => 'Aktif',
                    'lulus' => 'Lulus',
                    'mutasi' => 'Mutasi / Pindah',
                    'keluar' => 'Keluar',
                    'dropout' => 'Drop Out',
                ]"
            />

            {{-- Filter Keaktifan Akun --}}
            <x-form-select
                name="keaktifan"
                id="filterKeaktifan"
                class="form-select-sm"
                placeholder="Semua Keaktifan"
                onchange="this.form.submit()"
                :selected="request('keaktifan')"
                :options="[
                    '' => 'Semua Keaktifan',
                    'aktif' => 'Status Aktif',
                    'nonaktif' => 'Status Nonaktif',
                ]"
            />
        </x-slot:filters>

        <thead>
            <tr>
                <th class="w-12 text-center">No</th>
                <th>Peserta Didik</th>
                <th>Rombel & Angkatan</th>
                <th class="text-center w-16">L/P</th>
                <th>Kontak Siswa & Ortu</th>
                <th class="text-center w-28">Status</th>
                <th class="text-center w-24">Keaktifan</th>
                <th class="text-right w-36">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($murids as $index => $murid)
                <tr>
                    {{-- No Urut --}}
                    <td class="text-center font-mono text-xs text-slate-500">
                        {{ $murids->firstItem() + $index }}
                    </td>

                    {{-- Profil Murid --}}
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand flex items-center justify-center font-bold text-sm shrink-0 border border-brand/20">
                                {{ strtoupper(substr($murid->nama_lengkap, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-slate-900 truncate">
                                    {{ $murid->nama_lengkap }}
                                </div>
                                <div class="text-xs text-slate-500 font-mono mt-0.5">
                                    @if ($murid->nisn)
                                        <span title="NISN">NISN: {{ $murid->nisn }}</span>
                                    @endif
                                    @if ($murid->nisn && $murid->nis)
                                        &bull;
                                    @endif
                                    @if ($murid->nis)
                                        <span title="NIS">NIS: {{ $murid->nis }}</span>
                                    @endif
                                    @if (! $murid->nisn && ! $murid->nis)
                                        <span class="text-slate-400 italic">Belum ada nomor induk</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Rombel & Angkatan --}}
                    <td>
                        @if ($murid->kelas)
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="badge badge-primary font-medium">
                                    {{ $murid->kelas->nama }}
                                </span>
                                @if ($murid->angkatan)
                                    <span class="badge badge-neutral text-xs">
                                        {{ $murid->angkatan }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500 mt-1">
                                {{ $murid->kelas->tingkatan ? $murid->kelas->tingkatan->nama : '' }}
                                @if ($murid->kelas->jurusan)
                                    &bull; {{ $murid->kelas->jurusan->kode }}
                                @endif
                            </div>
                        @else
                            <span class="badge badge-neutral text-slate-500">
                                Tanpa Rombel
                            </span>
                            @if ($murid->angkatan)
                                <div class="text-xs text-slate-500 mt-1">Angkatan {{ $murid->angkatan }}</div>
                            @endif
                        @endif
                    </td>

                    {{-- Jenis Kelamin --}}
                    <td class="text-center">
                        <span class="badge {{ $murid->jenis_kelamin === 'L' ? 'badge-cyan' : 'badge-purple' }}">
                            {{ $murid->jenis_kelamin }}
                        </span>
                    </td>

                    {{-- Kontak Siswa & Ortu --}}
                    <td>
                        <div class="text-xs space-y-0.5">
                            @if ($murid->no_hp)
                                <div class="text-slate-700 font-mono" title="No. HP Siswa">
                                    {{ $murid->no_hp }}
                                </div>
                            @endif
                            @if ($murid->no_hp_ortu)
                                <div class="text-slate-500 font-mono text-xs" title="No. HP / WA Orang Tua">
                                    <span class="text-slate-400">Ortu:</span> {{ $murid->no_hp_ortu }}
                                </div>
                            @endif
                            @if (! $murid->no_hp && ! $murid->no_hp_ortu)
                                <span class="text-slate-400 italic text-xs">-</span>
                            @endif
                        </div>
                    </td>

                    {{-- Status Siswa --}}
                    <td class="text-center">
                        @php
                            $statusBadgeClass = match($murid->status) {
                                'aktif' => 'badge-success',
                                'lulus' => 'badge-cyan',
                                'mutasi', 'keluar' => 'badge-amber',
                                'dropout' => 'badge-danger',
                                default => 'badge-neutral',
                            };
                        @endphp
                        <span class="badge {{ $statusBadgeClass }}">
                            {{ ucfirst($murid->status) }}
                        </span>
                    </td>

                    {{-- Status Keaktifan Akun --}}
                    <td class="text-center">
                        <form method="POST" action="{{ route('master.data-murid.toggle-status', $murid) }}" class="inline">
                            @csrf
                            <button
                                type="button"
                                class="btn-toggle btn-open-toggle-murid"
                                data-id="{{ $murid->id }}"
                                data-nama="{{ $murid->nama_lengkap }}"
                                data-action="{{ route('master.data-murid.toggle-status', $murid) }}"
                                data-status="{{ $murid->is_active ? 'menonaktifkan' : 'mengaktifkan' }}"
                                title="Klik untuk {{ $murid->is_active ? 'menonaktifkan' : 'mengaktifkan' }} data murid">
                                <span class="toggle-indicator {{ $murid->is_active ? 'active' : '' }}"></span>
                            </button>
                        </form>
                    </td>

                    {{-- Tombol Aksi --}}
                    <td class="text-right">
                        <x-table-actions>
                            {{-- Pratinjau Profil Lengkap --}}
                            <x-crud-button
                                action="view"
                                class="btn-open-show-murid"
                                title="Lihat Profil Murid {{ $murid->nama_lengkap }}"
                                data-url="{{ route('master.data-murid.show', $murid) }}"
                            />

                            {{-- Ubah Data Murid --}}
                            <x-crud-button
                                action="edit"
                                class="btn-open-edit-murid"
                                title="Ubah Data Murid {{ $murid->nama_lengkap }}"
                                data-murid="{{ json_encode($murid) }}"
                                data-action="{{ route('master.data-murid.update', $murid) }}"
                            />

                            {{-- Reset Password Akun --}}
                            <button
                                type="button"
                                class="btn-icon btn-open-reset-password-murid"
                                title="Reset Kata Sandi Akun {{ $murid->nama_lengkap }}"
                                data-id="{{ $murid->id }}"
                                data-nama="{{ $murid->nama_lengkap }}"
                                data-username="{{ $murid->user?->username ?: ($murid->nisn ?: $murid->nis) }}"
                                data-action="{{ route('master.data-murid.reset-password', $murid) }}"
                            >
                                <x-icon name="key" />
                            </button>

                            {{-- Hapus Data (Delete Modal) --}}
                            <x-crud-button
                                action="delete"
                                class="btn-open-delete-murid"
                                title="Hapus Data Murid {{ $murid->nama_lengkap }}"
                                data-id="{{ $murid->id }}"
                                data-nama="{{ $murid->nama_lengkap }}"
                                data-action="{{ route('master.data-murid.destroy', $murid) }}"
                            />
                        </x-table-actions>
                    </td>
                </tr>
            @empty
                <x-empty-state
                    icon="users"
                    iconSize="48"
                    colspan="8"
                    :text="request()->filled('search') || request()->filled('kelas_id') || request()->filled('tingkatan_id') || request()->filled('status') || request()->filled('keaktifan')
                        ? 'Tidak ada data murid yang cocok dengan kriteria filter pencarian Anda.'
                        : 'Belum terdapat data peserta didik yang terdaftar pada unit sekolah ini.'">
                </x-empty-state>
            @endforelse
        </tbody>

        <x-slot:footer>
            <x-pagination-summary :paginator="$murids" />
        </x-slot:footer>
    </x-data-table>

    {{-- Dialog Modals --}}
    @include('master.data-murid.create')
    @include('master.data-murid.edit')
    @include('master.data-murid.show')
    @include('master.data-murid.import')

    {{-- Modal Konfirmasi Hapus Data Murid --}}
    <x-modal
        name="delete"
        id="modalDeleteMurid"
        form-id="formDeleteMurid"
        cancel-btn-id="btnCancelDeleteMurid"
        confirm-btn-id="btnConfirmDeleteMurid"
        message-id="deleteMuridMessage"
        badge-id="deleteMuridBadge"
        item-title-id="deleteMuridTitle"
    />

    {{-- Modal Konfirmasi Reset Password Murid --}}
    <x-modal
        name="confirm"
        id="modalResetPasswordMurid"
        form-id="formResetPasswordMurid"
        title="Reset Kata Sandi Akun Siswa"
        cancel-btn-id="btnCancelResetPasswordMurid"
        confirm-btn-id="btnConfirmResetPasswordMurid"
        message-id="resetPasswordMuridMessage"
        badge-id="resetPasswordMuridBadge"
        item-title-id="resetPasswordMuridItemTitle"
        hint-id="resetPasswordMuridHint"
    />

    {{-- Modal Konfirmasi Toggle Status Murid --}}
    <x-modal
        name="confirm"
        id="modalToggleStatusMurid"
        form-id="formToggleStatusMurid"
        title="Ubah Status Keaktifan Murid"
        cancel-btn-id="btnCancelToggleStatusMurid"
        confirm-btn-id="btnConfirmToggleStatusMurid"
        message-id="toggleStatusMuridMessage"
        badge-id="toggleStatusMuridBadge"
        item-title-id="toggleStatusMuridItemTitle"
        hint-id="toggleStatusMuridHint"
    />

    {{-- Formulir Trigger Eksternal Global --}}
    <form id="formDeleteMurid" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <form id="formResetPasswordMurid" method="POST" class="hidden">
        @csrf
    </form>

    <form id="formToggleStatusMurid" method="POST" class="hidden">
        @csrf
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 0. Modal Impor Data Murid
            InforaModals.setup('modalImportMurid', { cancelBtnId: 'btnCancelImportMurid' });
            InforaModals.bindOpenButton('btnOpenImportMurid', 'modalImportMurid');

            // 1. Modal Tambah Murid
            InforaModals.setup('modalCreateMurid', { cancelBtnId: 'btnCancelCreateMurid' });
            InforaModals.bindOpenButton('btnOpenCreateMurid', 'modalCreateMurid');

            // Toggle visibilitas kredensial kustom saat checkbox diubah
            const createUserAccountCb = document.getElementById('create_create_user_account');
            const userCredsBox = document.getElementById('createUserCredentialsBox');
            if (createUserAccountCb && userCredsBox) {
                createUserAccountCb.addEventListener('change', function() {
                    userCredsBox.style.display = this.checked ? 'grid' : 'none';
                });
            }

            // 2. Modal Ubah Murid
            InforaModals.setup('modalEditMurid', { cancelBtnId: 'btnCancelEditMurid' });
            InforaModals.bindEditButtons('.btn-open-edit-murid', 'modalEditMurid', function(modal, data, formEdit) {
                document.getElementById('edit_nama_lengkap').value = data.nama_lengkap || '';
                document.getElementById('edit_jenis_kelamin').value = data.jenis_kelamin || 'L';
                document.getElementById('edit_kelas_id').value = data.kelas_id || '';
                document.getElementById('edit_nisn').value = data.nisn || '';
                document.getElementById('edit_nis').value = data.nis || '';
                document.getElementById('edit_nik').value = data.nik || '';
                document.getElementById('edit_angkatan').value = data.angkatan || '';
                document.getElementById('edit_tempat_lahir').value = data.tempat_lahir || '';
                document.getElementById('edit_tanggal_lahir').value = data.tanggal_lahir ? data.tanggal_lahir.substring(0, 10) : '';
                document.getElementById('edit_agama').value = data.agama || '';
                document.getElementById('edit_no_hp').value = data.no_hp || '';
                document.getElementById('edit_email').value = data.email || '';
                document.getElementById('edit_alamat').value = data.alamat || '';
                document.getElementById('edit_nama_ayah').value = data.nama_ayah || '';
                document.getElementById('edit_nama_ibu').value = data.nama_ibu || '';
                document.getElementById('edit_nama_wali').value = data.nama_wali || '';
                document.getElementById('edit_no_hp_ortu').value = data.no_hp_ortu || '';
                document.getElementById('edit_status').value = data.status || 'aktif';
                document.getElementById('edit_is_active').checked = Boolean(data.is_active);

                // Kredensial & Status Info Akun
                const user = data.user || null;
                const username = data.username || (user ? user.username : '');
                const userId = data.user_id || (user ? user.id : null);

                document.getElementById('edit_username').value = username;
                document.getElementById('edit_password').value = '';

                const usernameText = document.getElementById('edit_account_username_text');
                const statusBadge = document.getElementById('edit_account_status_badge');
                const menuAccessBtn = document.getElementById('edit_account_menu_access_btn');

                if (usernameText) usernameText.textContent = username || 'Tanpa Akun (Belum Dibuat)';
                if (statusBadge) {
                    statusBadge.textContent = data.is_active ? 'Aktif' : 'Nonaktif';
                    statusBadge.className = data.is_active ? 'badge badge-success' : 'badge badge-neutral';
                }
                if (menuAccessBtn) {
                    if (userId) {
                        menuAccessBtn.href = '/sistem/user/user/' + userId;
                        menuAccessBtn.style.display = 'inline-flex';
                    } else {
                        menuAccessBtn.style.display = 'none';
                    }
                }

                // Populate Cascading Wilayah Kemendagri
                window.InforaWilayah?.populateCascade('edit', data);
            });

            // 3. Modal Pratinjau Detail Profil Murid
            InforaModals.setup('modalShowMurid', { cancelBtnId: 'btnCloseShowMurid' });
            document.querySelectorAll('.btn-open-show-murid').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const url = btn.dataset.url;
                    fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('showMuridNamaLengkap').textContent = data.nama_lengkap;
                        document.getElementById('showMuridSchoolName').textContent = data.school_name || '';
                        document.getElementById('showMuridKelasNama').textContent = data.kelas_nama || 'Tanpa Kelas';
                        document.getElementById('showMuridJk').textContent = data.jenis_kelamin_label;
                        document.getElementById('showMuridAngkatan').textContent = data.angkatan ? 'Angkatan ' + data.angkatan : '-';

                        const statusBadge = document.getElementById('showMuridStatusAktif');
                        statusBadge.textContent = data.is_active ? 'Aktif' : 'Nonaktif';
                        statusBadge.className = 'badge ' + (data.is_active ? 'badge-success' : 'badge-neutral');

                        document.getElementById('showMuridInitials').textContent = (data.nama_lengkap || 'MD').substring(0, 2).toUpperCase();
                        document.getElementById('showMuridNisn').textContent = data.nisn || '-';
                        document.getElementById('showMuridNis').textContent = data.nis || '-';
                        document.getElementById('showMuridNik').textContent = data.nik || '-';
                        document.getElementById('showMuridTtl').textContent = (data.tempat_lahir !== '-' ? data.tempat_lahir + ', ' : '') + (data.tanggal_lahir_formatted || '-');
                        document.getElementById('showMuridAgama').textContent = data.agama || '-';
                        document.getElementById('showMuridTingkatanJurusan').textContent = (data.tingkatan !== '-' ? 'Tingkat ' + data.tingkatan : '') + (data.jurusan !== '-' ? ' &bull; ' + data.jurusan : '-');

                        document.getElementById('showMuridNoHp').textContent = data.no_hp || '-';
                        document.getElementById('showMuridEmail').textContent = data.email || '-';

                        const wilayahText = [
                            data.kelurahan_nama ? 'Kel./Desa ' + data.kelurahan_nama : '',
                            data.kecamatan_nama ? 'Kec. ' + data.kecamatan_nama : '',
                            data.kabupaten_nama || '',
                            data.provinsi_nama || ''
                        ].filter(Boolean).join(', ');
                        document.getElementById('showMuridWilayah').textContent = wilayahText || '-';
                        document.getElementById('showMuridAlamat').textContent = data.alamat || '-';

                        document.getElementById('showMuridAyah').textContent = data.nama_ayah || '-';
                        document.getElementById('showMuridIbu').textContent = data.nama_ibu || '-';
                        document.getElementById('showMuridWali').textContent = data.nama_wali || '-';
                        document.getElementById('showMuridNoHpOrtu').textContent = data.no_hp_ortu || '-';

                        document.getElementById('showMuridUsername').textContent = data.username || '-';

                        InforaModals.open('modalShowMurid');
                    })
                    .catch(err => {
                        console.error('Gagal mengambil data murid:', err);
                    });
                });
            });

            // 4. Modal Konfirmasi Hapus Data Murid
            InforaModals.setup('modalDeleteMurid', { cancelBtnId: 'btnCancelDeleteMurid' });
            InforaModals.bindDeleteButtons('.btn-open-delete-murid', 'modalDeleteMurid', function(modal, btn) {
                const nama = btn.dataset.nama || 'Murid';
                document.getElementById('modalDeleteMuridTitle').textContent = 'Hapus Data Murid?';
                document.getElementById('deleteMuridMessage').textContent = 'Apakah Anda yakin ingin menghapus data peserta didik berikut? Data rekam jejak akademik akan diamankan dalam arsip (Soft Delete).';
                document.getElementById('deleteMuridBadge').textContent = 'Peserta Didik';
                document.getElementById('deleteMuridTitle').textContent = nama;
            });

            // 5. Modal Konfirmasi Reset Password Murid
            InforaModals.setup('modalResetPasswordMurid', { cancelBtnId: 'btnCancelResetPasswordMurid' });
            InforaModals.bindConfirmButtons('.btn-open-reset-password-murid', 'modalResetPasswordMurid', {
                formId: 'formResetPasswordMurid',
                title: 'Reset Kata Sandi Akun Murid?',
                message: 'Apakah Anda yakin ingin mereset kata sandi akun login untuk murid berikut ke nilai bawaan?',
                badge: 'Akun Login',
                itemTitle: (el) => `${el.dataset.username || '-'} (${el.dataset.nama || '-'})`,
                hint: 'Kata sandi akun akan direset otomatis ke NISN murid atau "infora123".',
                confirmText: 'Reset Password',
                confirmBtnClass: 'btn-primary w-full justify-center font-medium',
                badgeId: 'resetPasswordMuridBadge',
                messageId: 'resetPasswordMuridMessage',
                itemTitleId: 'resetPasswordMuridItemTitle',
                hintId: 'resetPasswordMuridHint',
            });

            // 6. Modal Konfirmasi Toggle Status Murid
            InforaModals.setup('modalToggleStatusMurid', { cancelBtnId: 'btnCancelToggleStatusMurid' });
            InforaModals.bindConfirmButtons('.btn-open-toggle-murid', 'modalToggleStatusMurid', {
                formId: 'formToggleStatusMurid',
                title: 'Ubah Status Keaktifan?',
                message: (el) => `Apakah Anda yakin ingin ${el.dataset.status} data murid berikut?`,
                badge: 'Status Murid',
                itemTitle: (el) => el.dataset.nama || '-',
                hint: 'Akun login pengguna terkait akan otomatis disinkronkan dengan status murid ini.',
                confirmText: 'Lanjutkan',
                confirmBtnClass: (el) => el.dataset.status === 'menonaktifkan' ? 'btn-delete w-full justify-center font-medium' : 'btn-primary w-full justify-center font-medium',
                badgeId: 'toggleStatusMuridBadge',
                messageId: 'toggleStatusMuridMessage',
                itemTitleId: 'toggleStatusMuridItemTitle',
                hintId: 'toggleStatusMuridHint',
            });

            // 7. Cascading Wilayah Administratif Kemendagri
            window.InforaWilayah?.bindCascade({ prefix: 'create' });
            window.InforaWilayah?.bindCascade({ prefix: 'edit' });

            // 8. Reopen modal otomatis saat terjadi validasi error
            window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
            window.errorMethod = @json(old('_method'));
            InforaModals.reopenOnError({
                create: 'modalCreateMurid',
                edit: 'modalEditMurid'
            });

            @if ($errors->any() && old('provinsi_id'))
                window.InforaWilayah?.restoreOnError('{{ old('_method') === 'PUT' ? 'edit' : 'create' }}', {
                    provinsi_id: '{{ old('provinsi_id') }}',
                    kabupaten_id: '{{ old('kabupaten_id') }}',
                    kecamatan_id: '{{ old('kecamatan_id') }}',
                    kelurahan_id: '{{ old('kelurahan_id') }}',
                });
            @endif
        });
    </script>
@endsection
