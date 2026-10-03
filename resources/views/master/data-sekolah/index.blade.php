@extends('layouts.app')

@section('content')
    <x-page-header title="Data Sekolah">
        <x-slot:subtitle>
            @if ($user?->isSuperAdmin())
                Kelola registri sekolah SMA dan SMK dalam platform INFORA
            @else
                Informasi profil dan identitas unit sekolah Anda
            @endif
        </x-slot:subtitle>
        <x-slot:actions>
            @if ($user?->isSuperAdmin())
                <button type="button" class="btn-secondary" id="btnOpenSyncAdmins"
                    title="Sinkronkan Akun Administrator Semua Sekolah"
                    aria-label="Sinkronkan Akun Administrator Semua Sekolah">
                    <x-icon name="sync" size="16" />
                </button>
            @endif
            <x-crud-button 
                action="create" 
                id="btnOpenCreateSchool"
                title="Tambah Data Sekolah"
            />
        </x-slot:actions>
    </x-page-header>

    {{-- Flash Alerts --}}
    <x-alert type="success" />
    <x-alert type="error" />
    <x-alert type="validation" />

    <x-data-table route="{{ route('master.data-sekolah.index') }}"
        search-placeholder="{{ $user?->isSuperAdmin() ? 'Cari nama sekolah, NPSN, atau kota...' : 'Cari...' }}"
        :per-page="$perPageInput" :total="$schools->total()" total-label="Sekolah">
        <x-slot:filters>
            <x-form-select id="school_type" name="school_type" class="form-select-sm" onchange="this.form.submit()"
                placeholder="Semua Jenis" :selected="request('school_type')" :options="['SMA' => 'SMA', 'SMK' => 'SMK', 'MA' => 'MA']" />
        </x-slot:filters>
        <thead>
            <tr>
                <th>Sekolah</th>
                <th>NPSN</th>
                <th>Akun Admin</th>
                <th>Jenis</th>
                <th>Status</th>
                <th>Akreditasi</th>
                <th>Kota</th>
                <th>Kondisi</th>
                <th class="col-w-actions">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($schools as $school)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            @if ($school->logo_path)
                                <img src="{{ asset('storage/' . $school->logo_path) }}" alt="Logo"class="entity-logo-thumb">
                            @else
                                <span class="entity-logo-placeholder">
                                    <x-icon name="school" size="18" />
                                </span>
                            @endif
                            <div>
                                <div class="table-cell-bold">{{ $school->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <code class="text-brand">{{ $school->npsn }}</code>
                    </td>
                    <td>
                        @if ($school->adminUser)
                            <span class="badge badge-success" title="Akun Administrator Terdaftar: {{ $school->adminUser->username }}">
                                <x-icon name="user" size="14" />
                                <span>{{ $school->adminUser->username }}</span>
                            </span>
                        @else
                            <span class="table-cell-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if ($school->school_type === 'SMA')
                            <span class="badge badge-primary">SMA</span>
                        @elseif ($school->school_type === 'SMK')
                            <span class="badge badge-cyan">SMK</span>
                        @elseif ($school->school_type === 'MA')
                            <span class="badge badge-warning">MA</span>
                        @endif
                    </td>
                    <td>
                        @if ($school->status === 'Negeri')
                            <span class="badge badge-success">Negeri</span>
                        @else
                            <span class="badge badge-warning">Swasta</span>
                        @endif
                    </td>
                    <td>
                        @switch($school->accreditation)
                            @case('A')
                                <span class="badge badge-success">A</span>
                            @break

                            @case('B')
                                <span class="badge badge-cyan">B</span>
                            @break

                            @case('C')
                                <span class="badge badge-warning">C</span>
                            @break

                            @case('Belum')
                                <span class="badge badge-neutral">Belum</span>
                            @break

                            @default
                                <span class="table-cell-muted">-</span>
                        @endswitch
                    </td>
                    <td>
                        <span class="table-cell-muted">{{ $school->kabupaten?->nama ?? ($school->city ?? '-') }}</span>
                    </td>
                    <td>
                        @if ($school->is_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-neutral">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <x-table-actions align="right">
                            <x-crud-button 
                                action="edit" 
                                class="btn-open-edit-school" 
                                title="Edit Data Sekolah"
                                data-school="{{ json_encode($school) }}"
                                data-action="{{ route('master.data-sekolah.update', $school) }}" 
                                :data-logo-url="$school->logo_path ? asset('storage/' . $school->logo_path) : null" 
                            />

                            <x-crud-button 
                                action="delete" 
                                class="btn-open-delete-school"
                                data-action="{{ route('master.data-sekolah.destroy', $school) }}"
                                data-name="{{ $school->name }}" 
                                data-npsn="{{ $school->npsn }}" 
                            />
                        </x-table-actions>
                    </td>
                </tr>
                @empty
                    <x-empty-state icon="home" iconSize="48" colspan="9" :text="!$user?->isSuperAdmin()
                        ? 'Akun Anda belum terhubung dengan unit sekolah manapun. Silakan hubungi administrator platform.'
                        : (request()->filled('search') || request()->filled('school_type')
                            ? 'Hasil pencarian tidak ditemukan.'
                            : 'Belum ada data sekolah yang terdaftar. Klik tombol Tambah untuk mendaftarkan sekolah pertama.')">
                    </x-empty-state>
                @endforelse
            </tbody>
            <x-slot:footer>
                <x-pagination-summary :paginator="$schools" />
            </x-slot:footer>
        </x-data-table>

        <!-- Modal Tambah Sekolah Component -->
        @include('master.data-sekolah.create')

        @if ($user?->isSuperAdmin())
            @include('master.data-sekolah.sync')
        @endif

        {{-- Modal Konfirmasi Hapus Data Sekolah --}}
        <x-modal 
            name="delete" 
            id="modalDeleteSchool" 
            form-id="formDeleteSchool" 
            cancel-btn-id="btnCancelDeleteSchool"
            confirm-btn-id="btnConfirmDeleteSchool" 
            message-id="deleteSchoolMessage" 
            badge-id="deleteSchoolBadge"
            item-title-id="deleteSchoolTitle" 
        />

        <!-- Modal Edit Sekolah Component -->
        @include('master.data-sekolah.edit')

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // 1. Setup Modals via InforaModals
                InforaModals.setup('modalCreateSchool', { cancelBtnId: 'btnCancelCreateSchool' });
                InforaModals.setup('modalEditSchool', { cancelBtnId: 'btnCancelEditSchool' });
                InforaModals.setup('modalDeleteSchool', { cancelBtnId: 'btnCancelDeleteSchool' });

                // 2. Hubungkan tombol buka modal tambah
                InforaModals.bindOpenButton('btnOpenCreateSchool', 'modalCreateSchool');

                // Reset logo saat tombol tambah dibuka
                document.getElementById('btnOpenCreateSchool')?.addEventListener('click', function() {
                    window.ImageUpload?.getInstance('create_logo')?.clear();
                });

                @if ($user?->isSuperAdmin())
                    InforaModals.setup('modalSyncAdmins', {
                        cancelBtnId: 'btnCancelSyncAdmins'
                    });
                    InforaModals.bindOpenButton('btnOpenSyncAdmins', 'modalSyncAdmins');
                @endif

                // 3. Hubungkan tombol edit modal via InforaModals
                InforaModals.bindEditButtons('.btn-open-edit-school', 'modalEditSchool', function(modal, school, formEdit, btn) {
                    document.getElementById('edit_name').value = school.name || '';
                    document.getElementById('edit_npsn').value = school.npsn || '';
                    document.getElementById('edit_school_type').value = school.school_type || '';
                    document.getElementById('edit_status').value = school.status || '';
                    document.getElementById('edit_accreditation').value = school.accreditation || '';
                    document.getElementById('edit_nss').value = school.nss || '';
                    document.getElementById('edit_address').value = school.address || '';
                    document.getElementById('edit_village').value = school.village || '';
                    document.getElementById('edit_district').value = school.district || '';
                    document.getElementById('edit_city').value = school.city || '';
                    document.getElementById('edit_province').value = school.province || '';
                    document.getElementById('edit_postal_code').value = school.postal_code || '';
                    document.getElementById('edit_phone').value = school.phone || '';
                    document.getElementById('edit_fax').value = school.fax || '';
                    document.getElementById('edit_email').value = school.email || '';
                    document.getElementById('edit_website').value = school.website || '';
                    document.getElementById('edit_principal_name').value = school.principal_name || '';
                    document.getElementById('edit_principal_nip').value = school.principal_nip || '';
                    document.getElementById('edit_foundation_name').value = school.foundation_name || '';
                    document.getElementById('edit_is_active').checked = Boolean(school.is_active);

                    // Preview logo sekolah via komponen ImageUpload
                    const logoUrl = btn.dataset.logoUrl;
                    const editLogo = window.ImageUpload?.getInstance('edit_logo');
                    if (logoUrl) {
                        editLogo?.setPreview(logoUrl);
                    } else {
                        editLogo?.clear();
                    }

                    // Populasi nilai dropdown wilayah di modal edit via InforaWilayah
                    window.InforaWilayah?.populateCascade('edit', school);
                });

                // 4. Hubungkan tombol konfirmasi hapus sekolah via InforaModals
                InforaModals.bindDeleteButtons('.btn-open-delete-school', 'modalDeleteSchool', function(modal, btn) {
                    document.getElementById('modalDeleteSchoolTitle').textContent = 'Hapus Sekolah ' + btn.dataset.name + '?';
                    document.getElementById('deleteSchoolMessage').textContent = 'Apakah Anda yakin ingin menghapus data sekolah berikut dari registri?';
                    document.getElementById('deleteSchoolBadge').textContent = 'NPSN: ' + (btn.dataset.npsn || '-');
                    document.getElementById('deleteSchoolTitle').textContent = btn.dataset.name || '-';
                });

                // 5. Re-open modal saat terjadi error validasi
                window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
                window.errorMethod = @json(old('_method'));
                InforaModals.reopenOnError({ create: 'modalCreateSchool', edit: 'modalEditSchool' });

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
            window.InforaWilayah?.bindCascade({ prefix: 'create', syncTextInputs: true });
            window.InforaWilayah?.bindCascade({ prefix: 'edit', syncTextInputs: true });
        </script>
    @endsection
