@extends('layouts.app')

@section('content')
    <x-page-header title="Pengaturan Profil">
        <x-slot:subtitle>
            Kelola data identitas diri, kepegawaian, kontak domisili, kredensial login, dan foto profil Anda
        </x-slot:subtitle>
    </x-page-header>

    {{-- Notifikasi Flash Alert Sistem --}}
    <x-alert type="success" />
    <x-alert type="error" />
    <x-alert type="validation" />

    {{-- Layout 2-Kolom: Sidebar Navigasi Vertikal & Konten Pengaturan --}}
    <div class="grid-split-sidebar mt-4">
        {{-- Sisi Kiri: Navigasi Sidebar & Mini Profile Card --}}
        <div>
            <div class="card-surface p-4 text-center">
                <div class="user-avatar-circle avatar-lg mx-auto mb-3 {{ $user->avatar_url ? 'has-image' : '' }}">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                    @else
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    @endif
                </div>

                <h3 class="user-name-text font-bold text-base mb-1">{{ $user->name }}</h3>
                <div class="brand-subtitle text-xs mb-3">&#64;{{ $user->username }}</div>

                <div class="mb-3">
                    <span class="badge badge-role-{{ $user->user_type }}">
                        {{ match($user->user_type) {
                            'super_admin' => 'Super Administrator',
                            'admin' => 'Administrator Sekolah',
                            'guru' => 'Pendidik / Guru',
                            'murid', 'siswa' => 'Peserta Didik',
                            default => ucfirst($user->user_type)
                        } }}
                    </span>
                </div>

                @if ($school)
                    <div class="pt-3 border-t border-dashed font-size-xs text-muted d-flex align-items-center justify-content-center gap-1 mb-4">
                        <x-icon name="building" size="14" />
                        <span class="truncate">{{ $school->name }}</span>
                    </div>
                @endif

                {{-- Navigasi Tab Vertikal Menggunakan Komponen Standar INFORA x-tab-pills --}}
                <x-tab-pills class="tab-pills-vertical w-full" role="tablist">
                    <x-tab-pill
                        :active="$activeTab === 'personal'"
                        icon="user"
                        :icon-size="16"
                        data-profile-tab="personal"
                    >
                        Informasi Pribadi
                    </x-tab-pill>

                    @if ($gtk)
                        <x-tab-pill
                            :active="$activeTab === 'employment'"
                            icon="briefcase"
                            :icon-size="16"
                            data-profile-tab="employment"
                        >
                            Klasifikasi & Pendidikan
                        </x-tab-pill>

                        <x-tab-pill
                            :active="$activeTab === 'contact'"
                            icon="map-pin"
                            :icon-size="16"
                            data-profile-tab="contact"
                        >
                            Kontak & Domisili
                        </x-tab-pill>
                    @endif

                    <x-tab-pill
                        :active="$activeTab === 'status' || $activeTab === 'photo'"
                        icon="shield-check"
                        :icon-size="16"
                        data-profile-tab="status"
                    >
                        Status Keaktifan
                    </x-tab-pill>
                </x-tab-pills>
            </div>
        </div>

        {{-- Sisi Kanan: Area Konten Formulir Pengaturan --}}
        <div>
            <div id="panel-personal" class="tab-panel {{ $activeTab === 'personal' ? '' : 'hidden' }}" role="tabpanel">
                @include('profile.partials.personal')
            </div>

            @if ($gtk)
                <div id="panel-employment" class="tab-panel {{ $activeTab === 'employment' ? '' : 'hidden' }}" role="tabpanel">
                    @include('profile.partials.employment')
                </div>

                <div id="panel-contact" class="tab-panel {{ $activeTab === 'contact' ? '' : 'hidden' }}" role="tabpanel">
                    @include('profile.partials.contact')
                </div>
            @endif

            <div id="panel-status" class="tab-panel {{ ($activeTab === 'status' || $activeTab === 'photo') ? '' : 'hidden' }}" role="tabpanel">
                @include('profile.partials.status')
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Penyimpanan Perubahan Data Sebelum Pindah Tab --}}
    <x-modal
        id="modalConfirmUnsavedProfile"
        title="Simpan Perubahan Data?"
        subtitle="Konfirmasi penyimpanan sebelum berpindah tab formulir"
        size="sm"
    >
        <div class="text-center py-2">
            <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-600 ring-8 ring-amber-50/60 flex items-center justify-center mx-auto mb-3 shrink-0">
                <x-icon name="alert-triangle" size="32" />
            </div>

            <h4 class="text-sm font-bold text-slate-900 mb-1">
                Perubahan Belum Disimpan
            </h4>
            <p class="text-xs text-muted mb-0">
                Anda memiliki perubahan data pada formulir ini yang belum disimpan. Apakah Anda ingin menyimpan data terlebih dahulu sebelum berpindah tab?
            </p>
        </div>

        <x-slot:footer>
            <div class="d-flex align-items-center justify-content-between w-full gap-2">
                <button type="button" class="btn-secondary font-size-xs" id="btnDiscardUnsavedProfile">
                    Abaikan
                </button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn-secondary font-size-xs" id="btnCancelUnsavedProfile">
                        Batal
                    </button>
                    <button type="button" class="btn-primary font-size-xs" id="btnSaveUnsavedProfile">
                        <x-icon name="check" size="14" />
                        <span>Simpan & Pindah</span>
                    </button>
                </div>
            </div>
        </x-slot:footer>
    </x-modal>

    {{-- Script Tab Switcher & Validasi Unsaved Changes --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabButtons = document.querySelectorAll('[data-profile-tab]');
            const tabPanels = document.querySelectorAll('.tab-panel');
            let currentActiveTab = '{{ $activeTab }}';
            let pendingTargetTab = null;
            let pendingDirtyForm = null;

            function showModal(id) {
                if (window.InforaModals?.open) {
                    window.InforaModals.open(id);
                } else {
                    const el = document.getElementById(id);
                    if (el) {
                        el.classList.remove('hidden');
                        document.body.classList.add('modal-open');
                    }
                }
            }

            function hideModal(id) {
                if (window.InforaModals?.close) {
                    window.InforaModals.close(id);
                } else {
                    const el = document.getElementById(id);
                    if (el) {
                        el.classList.add('hidden');
                        document.body.classList.remove('modal-open');
                    }
                }
            }

            function serializeForm(form) {
                if (!form) return '';
                const formData = new FormData(form);
                formData.delete('_token');
                formData.delete('_method');
                formData.delete('tab');
                const parts = [];
                for (const [key, value] of formData.entries()) {
                    if (value instanceof File) {
                        parts.push(`${key}=${value.name}:${value.size}`);
                    } else {
                        parts.push(`${key}=${value}`);
                    }
                }
                parts.sort();
                return parts.join('&');
            }

            function snapshotForms() {
                document.querySelectorAll('.tab-panel form').forEach(form => {
                    form.dataset.initialData = serializeForm(form);
                });
            }

            function isFormDirty(form) {
                if (!form || !form.dataset.initialData) return false;
                return serializeForm(form) !== form.dataset.initialData;
            }

            function getActivePanelDirtyForm() {
                const activePanel = document.getElementById('panel-' + currentActiveTab);
                if (!activePanel) return null;
                const forms = activePanel.querySelectorAll('form');
                for (const form of forms) {
                    if (isFormDirty(form)) {
                        return form;
                    }
                }
                return null;
            }

            function activateTab(tabName) {
                if (tabName === 'photo') {
                    tabName = 'status';
                }
                if (tabName === 'account' || tabName === 'security') {
                    tabName = 'personal';
                }
                const targetPanel = document.getElementById('panel-' + tabName);
                if (!targetPanel) return;

                currentActiveTab = tabName;

                tabButtons.forEach(btn => {
                    const isActive = btn.dataset.profileTab === tabName;
                    btn.classList.toggle('active', isActive);
                    btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                tabPanels.forEach(panel => {
                    panel.classList.toggle('hidden', panel.id !== 'panel-' + tabName);
                });

                // Sinkronisasi URL query string tanpa reload halaman
                const url = new URL(window.location);
                url.searchParams.set('tab', tabName);
                window.history.replaceState({}, '', url);
            }

            // Inisialisasi snapshot data formulir awal
            snapshotForms();

            tabButtons.forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetTab = this.dataset.profileTab;
                    if (!targetTab || targetTab === currentActiveTab) return;

                    const dirtyForm = getActivePanelDirtyForm();
                    if (dirtyForm) {
                        pendingTargetTab = targetTab;
                        pendingDirtyForm = dirtyForm;
                        showModal('modalConfirmUnsavedProfile');
                        return;
                    }

                    activateTab(targetTab);
                });
            });

            // Tombol Simpan & Pindah pada Modal
            document.getElementById('btnSaveUnsavedProfile')?.addEventListener('click', function () {
                if (!pendingDirtyForm) return;

                if (!pendingDirtyForm.reportValidity()) {
                    hideModal('modalConfirmUnsavedProfile');
                    return;
                }

                const tabInput = pendingDirtyForm.querySelector('input[name="tab"]');
                if (tabInput && pendingTargetTab) {
                    tabInput.value = pendingTargetTab;
                }

                hideModal('modalConfirmUnsavedProfile');
                pendingDirtyForm.submit();
            });

            // Tombol Abaikan Perubahan pada Modal
            document.getElementById('btnDiscardUnsavedProfile')?.addEventListener('click', function () {
                if (pendingDirtyForm) {
                    pendingDirtyForm.reset();
                    pendingDirtyForm.dataset.initialData = serializeForm(pendingDirtyForm);
                }
                hideModal('modalConfirmUnsavedProfile');
                if (pendingTargetTab) {
                    activateTab(pendingTargetTab);
                    pendingTargetTab = null;
                    pendingDirtyForm = null;
                }
            });

            // Tombol Batal pada Modal
            document.getElementById('btnCancelUnsavedProfile')?.addEventListener('click', function () {
                hideModal('modalConfirmUnsavedProfile');
                pendingTargetTab = null;
                pendingDirtyForm = null;
            });

            // Tutup modal jika klik backdrop di luar dialog
            const modalEl = document.getElementById('modalConfirmUnsavedProfile');
            if (modalEl) {
                modalEl.addEventListener('click', function (e) {
                    if (e.target === modalEl) {
                        hideModal('modalConfirmUnsavedProfile');
                        pendingTargetTab = null;
                        pendingDirtyForm = null;
                    }
                });
            }

            // Tutup modal jika menekan tombol Escape
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modalEl && !modalEl.classList.contains('hidden')) {
                    hideModal('modalConfirmUnsavedProfile');
                    pendingTargetTab = null;
                    pendingDirtyForm = null;
                }
            });

            // Sinkronisasi otomatis dari query URL jika ada
            const urlParams = new URLSearchParams(window.location.search);
            let initialTab = urlParams.get('tab');
            if (initialTab === 'account' || initialTab === 'security') {
                activateTab('personal');
                if (initialTab === 'security') {
                    const secCard = document.getElementById('cardProfileSecurity');
                    if (secCard) {
                        setTimeout(() => secCard.scrollIntoView({ behavior: 'smooth' }), 100);
                    }
                }
            } else if (initialTab) {
                if (initialTab === 'photo') {
                    initialTab = 'status';
                }
                if (document.getElementById('panel-' + initialTab)) {
                    activateTab(initialTab);
                }
            }

            // Expose helper snapshot ulang untuk integrasi async wilayah
            window.__profileSnapshotForms = snapshotForms;
        });
    </script>

    @if ($gtk)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Inisialisasi cascading dropdown wilayah domisili GTK
                window.InforaWilayah?.bindCascade({
                    provinsiId: 'profile_provinsi_id',
                    kabupatenId: 'profile_kabupaten_id',
                    kecamatanId: 'profile_kecamatan_id',
                    kelurahanId: 'profile_kelurahan_id',
                    syncTextInputs: false,
                });

                @if (old('provinsi_id'))
                    window.InforaWilayah?.restoreOnError({
                        provinsiId: 'profile_provinsi_id',
                        kabupatenId: 'profile_kabupaten_id',
                        kecamatanId: 'profile_kecamatan_id',
                        kelurahanId: 'profile_kelurahan_id',
                        syncTextInputs: false,
                    }, {
                        provinsi_id: '{{ old('provinsi_id') }}',
                        kabupaten_id: '{{ old('kabupaten_id') }}',
                        kecamatan_id: '{{ old('kecamatan_id') }}',
                        kelurahan_id: '{{ old('kelurahan_id') }}',
                    }).then(() => {
                        window.__profileSnapshotForms?.();
                    });
                @elseif ($gtk->provinsi_id)
                    window.InforaWilayah?.populateCascade({
                        provinsiId: 'profile_provinsi_id',
                        kabupatenId: 'profile_kabupaten_id',
                        kecamatanId: 'profile_kecamatan_id',
                        kelurahanId: 'profile_kelurahan_id',
                        syncTextInputs: false,
                    }, {
                        provinsi_id: '{{ $gtk->provinsi_id }}',
                        kabupaten_id: '{{ $gtk->kabupaten_id }}',
                        kecamatan_id: '{{ $gtk->kecamatan_id }}',
                        kelurahan_id: '{{ $gtk->kelurahan_id }}',
                    }).then(() => {
                        window.__profileSnapshotForms?.();
                    });
                @endif
            });
        </script>
    @endif
@endsection
