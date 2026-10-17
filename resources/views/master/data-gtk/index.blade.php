@extends('layouts.app')

@section('content')
    <x-page-header title="Data Pendidik & Tenaga Kependidikan (GTK)">
        <x-slot:subtitle>
            Kelola master data guru, pegawai tata usaha, dan kredensial akun pengguna sistem
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
                <form method="GET" action="{{ route('master.data-gtk') }}" id="schoolFilterForm">
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
                id="btnOpenCreateGtk"
                title="Tambah GTK"
            />
        </x-slot:actions>
    </x-page-header>

    {{-- Flash Alerts --}}
    <x-alert type="success" />
    <x-alert type="error" />
    <x-alert type="validation" />

    {{-- Kartu Metrik Ringkasan (KPI Cards) --}}
    <x-stats-grid>
        <x-stat-card icon="users" color="primary" label="Total GTK" :value="number_format($totalCount, 0, ',', '.')" />
        <x-stat-card icon="user-check" color="cyan" label="Tenaga Pendidik (Guru)" :value="number_format($pendidikCount, 0, ',', '.')" />
        <x-stat-card icon="briefcase" color="purple" label="Tenaga Kependidikan" :value="number_format($tendikCount, 0, ',', '.')" />
        <x-stat-card icon="check-circle" color="success" label="GTK Aktif" :value="number_format($activeCount, 0, ',', '.')" />
    </x-stats-grid>

    {{-- Tabel Data GTK --}}
    <x-data-table
        route="{{ route('master.data-gtk') }}"
        search-placeholder="Cari nama, NIP, NUPTK, NIK, atau kontak..."
        :per-page="$perPageInput">

        <x-slot:filters>
            <input type="hidden" name="school_id" value="{{ $selectedSchoolId }}">

            <x-form-select
                name="jenis_ptk"
                id="filterJenisPtk"
                class="form-select-sm"
                placeholder="Semua Jenis PTK"
                onchange="this.form.submit()"
                :selected="request('jenis_ptk')"
                :options="collect($jenisPtkList)->mapWithKeys(fn($item) => [$item => $item])->all()"
            />

            <x-form-select
                name="status_kepegawaian"
                id="filterStatusKepegawaian"
                class="form-select-sm"
                placeholder="Semua Status Pegawai"
                onchange="this.form.submit()"
                :selected="request('status_kepegawaian')"
                :options="collect($statusKepegawaianList)->mapWithKeys(fn($item) => [$item => $item])->all()"
            />

            <x-form-select
                name="status"
                id="filterStatus"
                class="form-select-sm"
                placeholder="Semua Status"
                onchange="this.form.submit()"
                :selected="request('status')"
                :options="[
                    'aktif' => 'Aktif',
                    'nonaktif' => 'Nonaktif',
                ]"
            />
        </x-slot:filters>

        <thead>
            <tr>
                <th class="col-w-idx">No</th>
                <th>Pendidik / Tenaga Kependidikan</th>
                <th>Jenis PTK & Kualifikasi</th>
                <th>Status Kepegawaian</th>
                <th>Kontak & Akun Login</th>
                <th>Status</th>
                <th class="col-w-actions">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($gtks as $index => $gtk)
                <tr>
                    <td class="col-w-idx text-center">
                        {{ $gtks->firstItem() + $index }}
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="user-avatar-circle avatar-sm {{ $gtk->foto_path ? 'has-image' : '' }}">
                                @if ($gtk->foto_path)
                                    <img src="{{ asset('storage/' . $gtk->foto_path) }}" alt="{{ $gtk->nama_lengkap }}">
                                @else
                                    <span>{{ strtoupper(substr($gtk->nama_lengkap, 0, 2)) }}</span>
                                @endif
                            </div>
                            <div>
                                <div class="table-cell-title font-weight-bold">{{ $gtk->nama_bergelar }}</div>
                                <div class="table-cell-muted text-xs">
                                    @if ($gtk->nip)
                                        NIP: {{ $gtk->nip }}
                                    @elseif ($gtk->nuptk)
                                        NUPTK: {{ $gtk->nuptk }}
                                    @elseif ($gtk->nik)
                                        NIK: {{ $gtk->nik }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-1 mb-1">
                            <span class="badge {{ $gtk->is_pendidik ? 'badge-primary' : 'badge-purple' }}">
                                {{ $gtk->is_pendidik ? 'Pendidik' : 'Tendik' }}
                            </span>
                        </div>
                        <div class="d-flex flex-wrap gap-1">
                            @php
                                $roles = (array) ($gtk->jenis_ptk ?? []);
                                $visibleRoles = array_slice($roles, 0, 2);
                                $remainingCount = count($roles) - 2;
                            @endphp
                            @foreach ($visibleRoles as $role)
                                <span class="badge badge-neutral text-xs">
                                    {{ $role }}
                                </span>
                            @endforeach
                            @if ($remainingCount > 0)
                                <span class="badge badge-cyan text-xs" title="{{ implode(', ', array_slice($roles, 2)) }}">
                                    +{{ $remainingCount }} lagi
                                </span>
                            @endif
                        </div>
                        @if ($gtk->pendidikan_terakhir)
                            <div class="table-cell-muted text-xs mt-1">
                                {{ $gtk->pendidikan_terakhir }} {{ $gtk->jurusan_pendidikan ? '(' . $gtk->jurusan_pendidikan . ')' : '' }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ str_contains($gtk->status_kepegawaian, 'PNS') || str_contains($gtk->status_kepegawaian, 'PPPK') ? 'badge-success' : 'badge-cyan' }}">
                            {{ $gtk->status_kepegawaian }}
                        </span>
                        @if ($gtk->jenis_kelamin)
                            <div class="table-cell-muted text-xs mt-1">
                                {{ $gtk->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if ($gtk->no_hp)
                            <div class="table-cell-title text-xs">{{ $gtk->no_hp }}</div>
                        @endif
                        @if ($gtk->user)
                            <div class="d-flex align-items-center gap-1 mt-1">
                                <span class="badge badge-purple text-xs">
                                    <x-icon name="user" size="12" />
                                    {{ $gtk->user->username }}
                                </span>
                            </div>
                        @else
                            <div class="table-cell-muted text-xs">Tanpa Akun</div>
                        @endif
                    </td>
                    <td>
                        @if ($gtk->is_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-neutral">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <x-table-actions align="right">
                            {{-- Pratinjau Detail Profil --}}
                            <x-crud-button
                                action="view"
                                class="btn-open-show-gtk"
                                title="Lihat Profil Lengkap {{ $gtk->nama_bergelar }}"
                                data-url="{{ route('master.data-gtk.show', $gtk) }}"
                            />

                            {{-- Toggle Status Cepat (1-klik) --}}
                            <button
                                type="button"
                                class="btn-icon btn-open-toggle-gtk"
                                title="{{ $gtk->is_active ? 'Nonaktifkan' : 'Aktifkan' }} GTK {{ $gtk->nama_bergelar }}"
                                data-action="{{ route('master.data-gtk.toggle-status', $gtk) }}"
                                data-nama="{{ $gtk->nama_bergelar }}"
                                data-status="{{ $gtk->is_active ? 'nonaktifkan' : 'aktifkan' }}"
                            >
                                <x-icon name="{{ $gtk->is_active ? 'power' : 'check-circle' }}" class="text-danger" size="16" />
                            </button>

                            {{-- Reset Password Akun --}}
                            @if ($gtk->user)
                                <button
                                    type="button"
                                    class="btn-icon btn-open-reset-password-gtk"
                                    title="Reset Password Akun {{ $gtk->user->username }}"
                                    data-action="{{ route('master.data-gtk.reset-password', $gtk) }}"
                                    data-nama="{{ $gtk->nama_bergelar }}"
                                    data-username="{{ $gtk->user->username }}"
                                >
                                    <x-icon name="key" />
                                </button>
                            @endif

                            {{-- Ubah Data GTK --}}
                            <x-crud-button
                                action="edit"
                                class="btn-open-edit-gtk"
                                title="Ubah Data {{ $gtk->nama_bergelar }}"
                                data-gtk="{{ json_encode($gtk) }}"
                                data-action="{{ route('master.data-gtk.update', $gtk) }}"
                                :data-foto-url="$gtk->foto_path ? asset('storage/' . $gtk->foto_path) : null"
                                data-username="{{ $gtk->user?->username }}"
                            />

                            {{-- Hapus Data (Delete Modal) --}}
                            <x-crud-button
                                action="delete"
                                class="btn-open-delete-gtk"
                                title="Hapus Data GTK {{ $gtk->nama_bergelar }}"
                                data-id="{{ $gtk->id }}"
                                data-nama="{{ $gtk->nama_bergelar }}"
                                data-action="{{ route('master.data-gtk.destroy', $gtk) }}"
                            />
                        </x-table-actions>
                    </td>
                </tr>
            @empty
                <x-empty-state
                    icon="users"
                    iconSize="48"
                    colspan="7"
                    :text="request()->filled('search') || request()->filled('status') || request()->filled('jenis_ptk') || request()->filled('status_kepegawaian')
                        ? 'Tidak ada data GTK yang cocok dengan kriteria filter pencarian Anda.'
                        : 'Belum terdapat data pendidik dan tenaga kependidikan yang terdaftar pada unit sekolah ini.'">
                </x-empty-state>
            @endforelse
        </tbody>

        <x-slot:footer>
            <x-pagination-summary :paginator="$gtks" />
        </x-slot:footer>
    </x-data-table>

    {{-- Komponen Dialog Modals --}}
    @include('master.data-gtk.create')
    @include('master.data-gtk.edit')
    @include('master.data-gtk.show')

    {{-- Modal Konfirmasi Hapus Data GTK --}}
    <x-modal
        name="delete"
        id="modalDeleteGtk"
        form-id="formDeleteGtk"
        cancel-btn-id="btnCancelDeleteGtk"
        confirm-btn-id="btnConfirmDeleteGtk"
        message-id="deleteGtkMessage"
        badge-id="deleteGtkBadge"
        item-title-id="deleteGtkTitle"
    />

    {{-- Modal Konfirmasi Reset Password --}}
    <x-modal
        name="confirm"
        id="modalResetPasswordGtk"
        form-id="formResetPasswordGtk"
        title="Reset Kata Sandi Akun"
        cancel-btn-id="btnCancelResetPasswordGtk"
        confirm-btn-id="btnConfirmResetPasswordGtk"
        message-id="resetPasswordMessage"
        badge-id="resetPasswordBadge"
        item-title-id="resetPasswordItemTitle"
        hint-id="resetPasswordHint"
    />

    {{-- Modal Konfirmasi Toggle Status --}}
    <x-modal
        name="confirm"
        id="modalToggleStatusGtk"
        form-id="formToggleStatusGtk"
        title="Ubah Status Keaktifan GTK"
        cancel-btn-id="btnCancelToggleStatusGtk"
        confirm-btn-id="btnConfirmToggleStatusGtk"
        message-id="toggleStatusMessage"
        badge-id="toggleStatusBadge"
        item-title-id="toggleStatusItemTitle"
        hint-id="toggleStatusHint"
    />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Modal Tambah GTK via InforaModals
            InforaModals.setup('modalCreateGtk', { cancelBtnId: 'btnCancelCreateGtk' });
            InforaModals.bindOpenButton('btnOpenCreateGtk', 'modalCreateGtk');
            document.getElementById('btnOpenCreateGtk')?.addEventListener('click', function() {
                window.ImageUpload?.getInstance('create_foto')?.clear();
            });

            // 2. Modal Ubah GTK via InforaModals
            InforaModals.setup('modalEditGtk', { cancelBtnId: 'btnCancelEditGtk' });
            InforaModals.bindEditButtons('.btn-open-edit-gtk', 'modalEditGtk', function(modal, data, formEdit, btn) {
                document.getElementById('edit_nama_lengkap').value = data.nama_lengkap || '';
                document.getElementById('edit_gelar_depan').value = data.gelar_depan || '';
                document.getElementById('edit_gelar_belakang').value = data.gelar_belakang || '';
                document.getElementById('edit_jenis_kelamin').value = data.jenis_kelamin || 'L';
                document.getElementById('edit_nik').value = data.nik || '';
                document.getElementById('edit_nip').value = data.nip || '';
                document.getElementById('edit_nuptk').value = data.nuptk || '';
                document.getElementById('edit_tempat_lahir').value = data.tempat_lahir || '';
                document.getElementById('edit_tanggal_lahir').value = data.tanggal_lahir ? data.tanggal_lahir.substring(0, 10) : '';
                document.getElementById('edit_agama').value = data.agama || '';
                document.getElementById('edit_status_kepegawaian').value = data.status_kepegawaian || '';
                document.getElementById('edit_pendidikan_terakhir').value = data.pendidikan_terakhir || '';
                document.getElementById('edit_jurusan_pendidikan').value = data.jurusan_pendidikan || '';
                document.getElementById('edit_no_hp').value = data.no_hp || '';
                document.getElementById('edit_email').value = data.email || '';
                document.getElementById('edit_alamat').value = data.alamat || '';
                document.getElementById('edit_username').value = data.username || (data.user ? data.user.username : '');
                document.getElementById('edit_password').value = '';
                document.getElementById('edit_is_active').checked = Boolean(data.is_active);

                // Reset dan centang checkbox jenis_ptk
                document.querySelectorAll('.edit-jenis-ptk-cb').forEach(cb => cb.checked = false);
                let roles = [];
                if (Array.isArray(data.jenis_ptk)) {
                    roles = data.jenis_ptk;
                } else if (typeof data.jenis_ptk === 'string') {
                    try {
                        roles = JSON.parse(data.jenis_ptk);
                    } catch (e) {
                        roles = [data.jenis_ptk];
                    }
                }
                document.querySelectorAll('.edit-jenis-ptk-cb').forEach(cb => {
                    if (roles.includes(cb.value)) {
                        cb.checked = true;
                    }
                });

                // Integrasi preview foto profil via modul JavaScript ImageUpload
                const fotoUrl = btn.dataset.fotoUrl || formEdit.getAttribute('data-foto-url');
                const editFoto = window.ImageUpload?.getInstance('edit_foto');
                if (fotoUrl) {
                    editFoto?.setPreview(fotoUrl);
                } else {
                    editFoto?.clear();
                }

                // Populasi cascading wilayah administratif di modal edit via InforaWilayah
                window.InforaWilayah?.populateCascade('edit', data);
            });

            // 3. Modal Pratinjau Detail Profil GTK
            InforaModals.setup('modalShowGtk', { cancelBtnId: 'btnCloseShowGtk' });
            document.querySelectorAll('.btn-open-show-gtk').forEach(function(btn) {
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
                        document.getElementById('showGtkNamaBergelar').textContent = data.nama_bergelar || data.nama_lengkap;
                        document.getElementById('showGtkSchoolName').textContent = data.school_name || '';
                        document.getElementById('showGtkJenisPtk').textContent = data.jenis_ptk || '-';
                        document.getElementById('showGtkStatusKepegawaian').textContent = data.status_kepegawaian || '-';
                        
                        const statusBadge = document.getElementById('showGtkStatusAktif');
                        statusBadge.textContent = data.is_active ? 'Aktif' : 'Nonaktif';
                        statusBadge.className = 'badge ' + (data.is_active ? 'badge-success' : 'badge-neutral');

                        document.getElementById('showGtkNip').textContent = data.nip || '-';
                        document.getElementById('showGtkNuptk').textContent = data.nuptk || '-';
                        document.getElementById('showGtkNik').textContent = data.nik || '-';
                        document.getElementById('showGtkJenisKelamin').textContent = data.jenis_kelamin || '-';
                        document.getElementById('showGtkTtl').textContent = (data.tempat_lahir !== '-' ? data.tempat_lahir + ', ' : '') + data.tanggal_lahir;
                        document.getElementById('showGtkAgama').textContent = data.agama || '-';

                        const rolesContainer = document.getElementById('showGtkRolesList');
                        if (rolesContainer) {
                            rolesContainer.innerHTML = '';
                            const rolesArray = Array.isArray(data.jenis_ptk_list) ? data.jenis_ptk_list : [];
                            if (rolesArray.length > 0) {
                                rolesArray.forEach(role => {
                                    const badge = document.createElement('span');
                                    badge.className = 'badge badge-primary text-xs';
                                    badge.textContent = role;
                                    rolesContainer.appendChild(badge);
                                });
                            } else {
                                rolesContainer.textContent = data.jenis_ptk || '-';
                            }
                        }

                        document.getElementById('showGtkPendidikan').textContent = data.pendidikan_terakhir || '-';
                        document.getElementById('showGtkJurusan').textContent = data.jurusan_pendidikan || '-';
                        document.getElementById('showGtkNoHp').textContent = data.no_hp || '-';
                        document.getElementById('showGtkEmail').textContent = data.email || '-';

                        const wilayahText = [
                            data.kelurahan_nama ? 'Kel./Desa ' + data.kelurahan_nama : '',
                            data.kecamatan_nama ? 'Kec. ' + data.kecamatan_nama : '',
                            data.kabupaten_nama || '',
                            data.provinsi_nama || ''
                        ].filter(Boolean).join(', ');
                        document.getElementById('showGtkWilayah').textContent = wilayahText || '-';
                        document.getElementById('showGtkAlamat').textContent = data.alamat || '-';

                        document.getElementById('showGtkUsername').textContent = data.username || '-';
                        document.getElementById('showGtkUserType').textContent = data.user_type || '-';

                        const avatarImg = document.getElementById('showGtkAvatarImg');
                        const avatarInitials = document.getElementById('showGtkAvatarInitials');
                        const avatarContainer = document.getElementById('showGtkAvatarContainer');
                        if (data.foto_url) {
                            avatarImg.src = data.foto_url;
                            avatarImg.classList.remove('hidden');
                            avatarInitials.classList.add('hidden');
                            if (avatarContainer) avatarContainer.classList.add('has-image');
                        } else {
                            avatarImg.classList.add('hidden');
                            avatarInitials.classList.remove('hidden');
                            avatarInitials.textContent = (data.nama_lengkap || 'GTK').substring(0, 2).toUpperCase();
                            if (avatarContainer) avatarContainer.classList.remove('has-image');
                        }

                        InforaModals.open('modalShowGtk');
                    })
                    .catch(err => {
                        console.error('Gagal mengambil data GTK:', err);
                    });
                });
            });

            // 4. Modal Konfirmasi Hapus Data GTK via InforaModals
            InforaModals.setup('modalDeleteGtk', { cancelBtnId: 'btnCancelDeleteGtk' });
            InforaModals.bindDeleteButtons('.btn-open-delete-gtk', 'modalDeleteGtk', function(modal, btn) {
                const nama = btn.dataset.nama || 'GTK';
                document.getElementById('modalDeleteGtkTitle').textContent = 'Hapus Data ' + nama + '?';
                document.getElementById('deleteGtkMessage').textContent = 'Apakah Anda yakin ingin menghapus data GTK berikut? Akun login pengguna terkait juga akan dinonaktifkan.';
                document.getElementById('deleteGtkBadge').textContent = 'GTK';
                document.getElementById('deleteGtkTitle').textContent = nama;
            });

            // 5. Modal Konfirmasi Reset Password GTK via InforaModals
            InforaModals.setup('modalResetPasswordGtk', { cancelBtnId: 'btnCancelResetPasswordGtk' });
            InforaModals.bindConfirmButtons('.btn-open-reset-password-gtk', 'modalResetPasswordGtk', {
                formId: 'formResetPasswordGtk',
                title: 'Reset Kata Sandi Akun?',
                message: 'Apakah Anda yakin ingin mereset kata sandi akun untuk GTK berikut ke nilai bawaan?',
                badge: 'Akun Login',
                itemTitle: (el) => `${el.dataset.username || '-'} (${el.dataset.nama || '-'})`,
                hint: 'Kata sandi akan direset otomatis ke nomor NIP, NUPTK, NIK, atau username yang bersangkutan.',
                confirmText: 'Reset Password',
                confirmBtnClass: 'btn-primary w-full justify-center font-medium',
                badgeId: 'resetPasswordBadge',
                messageId: 'resetPasswordMessage',
                itemTitleId: 'resetPasswordItemTitle',
                hintId: 'resetPasswordHint',
            });

            // 6. Modal Konfirmasi Toggle Status GTK via InforaModals
            InforaModals.setup('modalToggleStatusGtk', { cancelBtnId: 'btnCancelToggleStatusGtk' });
            InforaModals.bindConfirmButtons('.btn-open-toggle-gtk', 'modalToggleStatusGtk', {
                formId: 'formToggleStatusGtk',
                title: 'Ubah Status Keaktifan?',
                message: (el) => `Apakah Anda yakin ingin ${el.dataset.status} status keaktifan GTK berikut?`,
                badge: 'Status GTK',
                itemTitle: (el) => el.dataset.nama || '-',
                hint: 'Akses masuk akun pengguna terkait akan otomatis disinkronkan sesuai status GTK.',
                confirmText: 'Lanjutkan',
                confirmBtnClass: (el) => el.dataset.status === 'nonaktifkan' ? 'btn-delete w-full justify-center font-medium' : 'btn-primary w-full justify-center font-medium',
                badgeId: 'toggleStatusBadge',
                messageId: 'toggleStatusMessage',
                itemTitleId: 'toggleStatusItemTitle',
                hintId: 'toggleStatusHint',
            });

            // 7. Reopen modal otomatis saat terjadi validasi error
            window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
            window.errorMethod = @json(old('_method'));
            InforaModals.reopenOnError({
                create: 'modalCreateGtk',
                edit: 'modalEditGtk'
            });

            // Pulihkan opsi bertingkat jika terjadi error validasi via InforaWilayah
            @if ($errors->any() && old('provinsi_id'))
                window.InforaWilayah?.restoreOnError('{{ old('_method') === 'PUT' ? 'edit' : 'create' }}', {
                    provinsi_id: '{{ old('provinsi_id') }}',
                    kabupaten_id: '{{ old('kabupaten_id') }}',
                    kecamatan_id: '{{ old('kecamatan_id') }}',
                    kelurahan_id: '{{ old('kelurahan_id') }}',
                });
            @endif
        });

        // Delegasi Event Global untuk Cascading Wilayah (Modal Tambah dan Modal Edit)
        window.InforaWilayah?.bindCascade({ prefix: 'create', syncTextInputs: false });
        window.InforaWilayah?.bindCascade({ prefix: 'edit', syncTextInputs: false });
    </script>
@endsection
