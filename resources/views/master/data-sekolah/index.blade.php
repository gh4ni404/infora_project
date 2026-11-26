@extends('layouts.app')

@section('content')
    <x-page-header title="Profil Sekolah">
        <x-slot:subtitle>
            Informasi profil, identitas resmi, dan akun administrator sekolah pada instalasi INFORA
        </x-slot:subtitle>
        <x-slot:actions>
            @if ($school)
                @if ($user?->isSuperAdmin())
                    <form action="{{ route('master.data-sekolah.sync-single-admin', $school) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-secondary" title="Sinkronkan Akun Administrator Sekolah">
                            <x-icon name="sync" size="16" />
                            <span>Sinkronkan Admin</span>
                        </button>
                    </form>
                @endif
                <x-crud-button
                    action="edit"
                    class="btn-open-edit-school"
                    title="Edit Profil Sekolah"
                    data-school="{{ json_encode($school) }}"
                    data-action="{{ route('master.data-sekolah.update', $school) }}"
                    :data-logo-url="$school->logo_path ? asset('storage/' . $school->logo_path) : null"
                />
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Flash Alerts --}}
    <x-alert type="success" />
    <x-alert type="error" />
    <x-alert type="validation" />

    @if ($school)
        <div class="card-surface mb-6">
            <div class="card-header">
                <div class="flex items-center gap-3">
                    @if ($school->logo_path)
                        <img src="{{ asset('storage/' . $school->logo_path) }}" alt="Logo {{ $school->name }}" class="entity-logo-thumb">
                    @else
                        <span class="entity-logo-placeholder">
                            <x-icon name="school" size="18" />
                        </span>
                    @endif
                    <div>
                        <h2 class="user-name-text">{{ $school->name }}</h2>
                        <div class="brand-subtitle">NPSN: <code class="text-brand">{{ $school->npsn }}</code></div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge badge-primary">{{ $school->school_type }}</span>
                    <span class="badge {{ $school->status === 'Negeri' ? 'badge-success' : 'badge-warning' }}">{{ $school->status }}</span>
                    <span class="badge {{ $school->is_active ? 'badge-success' : 'badge-neutral' }}">
                        {{ $school->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>

            <div class="card-body">
                <div class="grid-split-1-1">
                    <div>
                        <div class="form-group">
                            <span class="form-label">Akreditasi:</span>
                            <span class="badge badge-cyan">{{ $school->accreditation ?? '-' }}</span>
                        </div>
                        <div class="form-group">
                            <span class="form-label">NSS / NSM:</span>
                            <span class="text-muted">{{ $school->nss ?? '-' }}</span>
                        </div>
                        <div class="form-group">
                            <span class="form-label">Kepala Sekolah:</span>
                            <span class="user-name-text">{{ $school->principal_name ?? '-' }}</span>
                            @if ($school->principal_nip)
                                <span class="text-muted text-xs">(NIP: {{ $school->principal_nip }})</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <span class="form-label">Yayasan / Penyelenggara:</span>
                            <span class="text-muted">{{ $school->foundation_name ?? '-' }}</span>
                        </div>
                        <div class="form-group">
                            <span class="form-label">Akun Administrator:</span>
                            @if ($school->adminUser)
                                <span class="badge badge-success" title="Akun Administrator Terdaftar: {{ $school->adminUser->username }}">
                                    <x-icon name="user" size="14" />
                                    <span>{{ $school->adminUser->username }}</span>
                                </span>
                            @else
                                <span class="table-cell-muted">-</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="form-group">
                            <span class="form-label">Alamat Lengkap:</span>
                            <span class="text-muted">{{ $school->address ?? '-' }}</span>
                        </div>
                        <div class="form-group">
                            <span class="form-label">Wilayah Administratif:</span>
                            <span class="text-muted">
                                {{ collect([
                                    $school->kelurahan?->nama ?? $school->village,
                                    $school->kecamatan?->nama ?? $school->district,
                                    $school->kabupaten?->nama ?? $school->city,
                                    $school->provinsi?->nama ?? $school->province,
                                ])->filter()->implode(', ') ?: '-' }}
                                @if ($school->postal_code)
                                    ({{ $school->postal_code }})
                                @endif
                            </span>
                        </div>
                        <div class="form-group">
                            <span class="form-label">Telepon / Fax:</span>
                            <span class="text-muted">{{ $school->phone ?? '-' }} / {{ $school->fax ?? '-' }}</span>
                        </div>
                        <div class="form-group">
                            <span class="form-label">Email:</span>
                            <span class="text-muted">{{ $school->email ?? '-' }}</span>
                        </div>
                        <div class="form-group">
                            <span class="form-label">Website:</span>
                            <span class="text-muted">{{ $school->website ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card-surface">
            <div class="card-body">
                <x-empty-state
                    icon="home"
                    iconSize="48"
                    :text="!$user?->isSuperAdmin()
                        ? 'Akun Anda belum terhubung dengan unit sekolah manapun. Silakan hubungi administrator platform.'
                        : 'Profil sekolah belum terdaftar. Jalankan setup sekolah untuk menginisialisasi profil sekolah tunggal.'"
                />
            </div>
        </div>
    @endif

    <!-- Modal Edit Sekolah Component -->
    @include('master.data-sekolah.edit')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            InforaModals.setup('modalEditSchool', { cancelBtnId: 'btnCancelEditSchool' });

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

                const logoUrl = btn.dataset.logoUrl;
                const editLogo = window.ImageUpload?.getInstance('edit_logo');
                if (logoUrl) {
                    editLogo?.setPreview(logoUrl);
                } else {
                    editLogo?.clear();
                }

                window.InforaWilayah?.populateCascade('edit', school);
            });

            window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
            window.errorMethod = @json(old('_method'));
            InforaModals.reopenOnError({ edit: 'modalEditSchool' });

            @if ($errors->any() && old('provinsi_id'))
                window.InforaWilayah?.restoreOnError('edit', {
                    provinsi_id: '{{ old('provinsi_id') }}',
                    kabupaten_id: '{{ old('kabupaten_id') }}',
                    kecamatan_id: '{{ old('kecamatan_id') }}',
                    kelurahan_id: '{{ old('kelurahan_id') }}',
                });
            @endif

            window.InforaWilayah?.bindCascade({ prefix: 'edit', syncTextInputs: true });
        });
    </script>
@endsection
