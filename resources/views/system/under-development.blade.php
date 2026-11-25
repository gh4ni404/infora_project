@extends('layouts.app')

@section('content')
    @if (auth()->user()?->isSuperAdmin())
        <div class="page-header">
            <div>
                <div class="page-breadcrumbs"
                    style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: var(--infora-text-muted); margin-bottom: 0.375rem;">
                    <span>{{ $module?->name }}</span>
                    <x-icon name="chevron-right" size="14" />
                    @if ($itemType === 'Sub-Menu' && $menu)
                        <span>{{ $menu->name }}</span>
                        <x-icon name="chevron-right" size="14" />
                    @endif
                    <span style="color: var(--infora-brand-primary); font-weight: 600;">{{ $item->name }}</span>
                </div>
                <h2 class="page-title">{{ $item->name }}</h2>
                <div class="page-subtitle">Item {{ strtolower($itemType) }} aktif pada struktur navigasi INFORA</div>
            </div>
            <div class="page-actions">
                <a href="{{ $editUrl }}" class="btn-secondary" title="Ubah data {{ strtolower($itemType) }}">
                    <x-icon name="pencil" size="18" />
                    <span>Edit {{ $itemType }}</span>
                </a>
                <a href="{{ route('dashboard') }}" class="btn-secondary" title="Kembali ke Dashboard">
                    <x-icon name="chevron-left" size="18" />
                    <span>Dashboard</span>
                </a>
            </div>
        </div>

        <div class="card-surface" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div
                        style="width: 36px; height: 36px; border-radius: 0.5rem; background: rgba(245, 158, 11, 0.12); color: #f59e0b; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(245, 158, 11, 0.25);">
                        <x-icon name="tool" size="20" />
                    </div>
                    <div>
                        <span class="user-name-text">Status Fitur: Dalam Tahap Pengembangan</span>
                        <div class="brand-subtitle">Modul ini telah siap di struktur menu dan menunggu implementasi kode
                        </div>
                    </div>
                </div>
                <span class="badge"
                    style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);">Under
                    Development</span>
            </div>

            <div class="card-body">
                <div class="alert-info">
                    <x-icon name="info" size="40" />
                    <div>
                        <strong>Navigasi Telah Terdaftar:</strong> <br>
                        Item <strong>{{ $item->name }}</strong> sudah berhasil terdaftar pada navigasi sistem INFORA.
                        Tautan di sidebar diarahkan ke halaman pratinjau ini karena rute backend
                        <code>{{ $rawRoute ?: '(kosong)' }}</code> belum diaktifkan di file <code>routes/web.php</code>.
                    </div>
                </div>

                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                    <div class="card-surface" style="padding: 1.25rem; background: var(--infora-surface-elevated);">
                        <div
                            style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--infora-text-muted); margin-bottom: 0.5rem; font-weight: 600;">
                            Detail Navigasi</div>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.875rem;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--infora-text-muted);">Tipe Entitas:</span>
                                <span class="badge badge-neutral">{{ $itemType }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--infora-text-muted);">Nama Tampilan:</span>
                                <span style="font-weight: 600; color: var(--infora-text-main);">{{ $item->name }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--infora-text-muted);">Induk Modul:</span>
                                <span style="color: var(--infora-text-main);">{{ $module?->name ?? '-' }}</span>
                            </div>
                            @if ($itemType === 'Sub-Menu' && $menu)
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: var(--infora-text-muted);">Induk Menu:</span>
                                    <span style="color: var(--infora-text-main);">{{ $menu->name }}</span>
                                </div>
                            @endif
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--infora-text-muted);">Rute Terdaftar:</span>
                                <code
                                    style="font-size: 0.8125rem; color: var(--infora-brand-primary); background: var(--infora-surface-muted); padding: 0.125rem 0.375rem; border-radius: 0.25rem;">
                                    {{ $rawRoute ?: 'Belum diisi' }}
                                </code>
                            </div>
                        </div>
                    </div>

                    <div class="card-surface" style="padding: 1.25rem; background: var(--infora-surface-elevated);">
                        <div
                            style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--infora-text-muted); margin-bottom: 0.5rem; font-weight: 600;">
                            Status Operasional</div>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.875rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: var(--infora-text-muted);">Status Visibilitas:</span>
                                <span class="badge badge-success">Tampil di Sidebar</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: var(--infora-text-muted);">Urutan Tampil (Order):</span>
                                <span style="font-weight: 600; color: var(--infora-text-main);">{{ $item->order }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: var(--infora-text-muted);">Implementasi Code:</span>
                                <span style="color: #f59e0b; font-weight: 500;">Menunggu Controller</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="route-guide-box"
                    style="margin-top: 1rem; padding: 1.25rem; background: var(--infora-surface-card); border: 1px solid var(--infora-border);">
                    <div class="route-guide-title" style="font-size: 0.9375rem; margin-bottom: 0.75rem;">
                        <x-icon name="code" size="18" />
                        <span>Panduan Developer (Blueprint Implementasi)</span>
                    </div>
                    <p style="color: var(--infora-text-muted); font-size: 0.8125rem; margin-bottom: 0.75rem;">
                        Untuk mengaktifkan fitur ini, developer cukup membuat Controller dan mendaftarkan rute di
                        <code>routes/web.php</code>:
                    </p>

                    <div style="margin-bottom: 1rem;">
                        <div
                            style="font-size: 0.75rem; font-weight: 600; color: var(--infora-text-dim); margin-bottom: 0.25rem;">
                            1. Buat Controller via Artisan:</div>
                        <div
                            style="background: #0f172a; padding: 0.625rem 0.875rem; border-radius: 0.375rem; font-family: monospace; font-size: 0.8125rem; color: #38bdf8; overflow-x: auto; border: 1px solid rgba(255, 255, 255, 0.08);">
                            {{ $suggestedArtisanCmd }}
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <div
                            style="font-size: 0.75rem; font-weight: 600; color: var(--infora-text-dim); margin-bottom: 0.25rem;">
                            2. Daftarkan Rute di routes/web.php:</div>
                        <div
                            style="background: #0f172a; padding: 0.625rem 0.875rem; border-radius: 0.375rem; font-family: monospace; font-size: 0.8125rem; color: #a5f3fc; overflow-x: auto; border: 1px solid rgba(255, 255, 255, 0.08);">
                            {{ $suggestedRouteCode }}
                        </div>
                    </div>

                    <p style="color: var(--infora-text-dim); font-size: 0.75rem; margin-top: 0.5rem; margin-bottom: 0;">
                        💡 Setelah rute di atas terdaftar, tautan di sidebar akan secara otomatis mengarah ke halaman
                        controller tersebut tanpa perlu mengubah konfigurasi database lagi!
                    </p>
                </div>

                <div
                    style="display: flex; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--infora-border);">
                    <a href="{{ $editUrl }}" class="btn-primary">
                        <x-icon name="pencil" size="16" />
                        <span>Edit Konfigurasi {{ $itemType }}</span>
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn-secondary">
                        <span>Kembali ke Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    @else
        @php
            $moduleCode = Str::upper(Str::slug($module?->name, '-')) . '-' . sprintf('%02d', $item->order);
            $authorityLabel = match (auth()->user()?->user_type) {
                'admin' => 'Administrator Sekolah',
                'guru' => 'Pengajar/GTK',
                'staf' => 'Tenaga Kependidikan/Staf',
                'murid' => 'Peserta Didik/Murid',
                default => 'Pengguna Terverifikasi',
            };
        @endphp

        <x-page-header title="{{ $item->name }}"
            subtitle="Modul ({{ $module?->name }}) • Fitur Sedang Dalam Tahap Persiapan" badgePage="Dalam Pengembangan" />

        <div class="card-surface p-6 sm:p-10 mb-6">
            <!-- Center Illustration & Error / Maintenance Hero Section -->
            <div class="px-6 pt-10 pb-8 sm:px-12 flex flex-col items-center text-center">
                <!-- Graphic SVG: Screen + Gear + Construction / Wrench element -->
                <div aria-hidden="true" class="relative w-48 h-44 mb-6 flex items-center justify-center">
                    <!-- Background soft glow -->
                    <div class="absolute inset-0 bg-sky-100/60 rounded-full blur-2xl transform scale-90"></div>
                    <x-icon name="monitor-error" />
                </div>

                {{-- 2. Metadata Pills --}}
                <div class="flex flex-wrap items-center justify-center gap-2.5 mb-3">
                    <span class="badge badge-cyan font-mono">
                        <span class="font-bold">&lt;/&gt;</span>
                        <span>Kode Modul: {{ $moduleCode }}</span>
                    </span>

                    <span class="badge badge-warning">
                        Status: Fitur Tahap Persiapan
                    </span>
                </div>

                {{-- 3. Title & Reassuring Description --}}
                <div class="text-center max-w-2xl mx-auto mb-6">
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mb-2">
                        Modul {{ $item->name }} Sedang Dipersiapkan
                    </h3>
                    <p class="text-sm text-slate-500 leading-relaxed mb-0">
                        Akses Anda Terverifikasi: <strong
                            class="text-slate-800 font-semibold">{{ auth()->user()->name }}</strong>. Fitur ini sedang
                        disiapkan oleh tim pengembang dan akan segera dapat digunakan secara langsung.
                    </p>
                </div>

                {{-- 4. Progress Card --}}
                <div class="max-w-lg mx-auto bg-slate-50/80 border border-slate-100 rounded-xl p-4 mb-8">
                    <div class="flex items-center justify-between text-xs sm:text-sm font-semibold mb-2">
                        <div class="flex items-center gap-1.5 text-slate-700">
                            <x-icon name="zap" size="16" class="text-sky-500" />
                            <span>Tahap Integrasi Data Sistem</span>
                        </div>
                        <span class="text-sky-600 font-bold">80%</span>
                    </div>

                    <div class="w-full bg-slate-200/80 h-2 rounded-full overflow-hidden mb-2">
                        <div class="bg-gradient-to-r from-sky-500 to-blue-600 h-full rounded-full w-4/5"></div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span>Proses sinkronisasi komponen antarmuka</span>
                        <span class="font-mono">v0.9-alpha</span>
                    </div>
                </div>

                {{-- 5. Three Status Cards Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    {{-- Card 1: Menu System --}}
                    <div
                        class="p-4 sm:p-5 rounded-xl border border-slate-100 bg-white shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                                MENU SYSTEM
                            </div>
                            <div class="font-semibold text-slate-800 text-sm mb-0.5">
                                {{ $item->name }}{{ Str::lower($item->name) === 'dashboard' ? ' Utama' : '' }}
                            </div>
                            <div class="text-xs text-slate-500 mb-4">
                                Kategori: {{ $module?->name }}
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-slate-100/80 text-xs">
                            <span class="text-slate-400">Tipe Navigasi:</span>
                            <span class="badge badge-neutral font-semibold uppercase">{{ $itemType }}</span>
                        </div>
                    </div>

                    {{-- Card 2: Otorisasi Pengguna --}}
                    <div
                        class="p-4 sm:p-5 rounded-xl border border-slate-100 bg-white shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                                OTORISASI PENGGUNA
                            </div>
                            <div class="font-semibold text-slate-800 text-sm mb-0.5">
                                {{ auth()->user()->name }}
                            </div>
                            <div class="text-xs text-slate-500 mb-4">
                                Otoritas Tingkat {{ $authorityLabel }}
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-slate-100/80 text-xs">
                            <span class="text-slate-400">Hak Akses:</span>
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Diberikan (Aktif)</span>
                            </span>
                        </div>
                    </div>

                    {{-- Card 3: Estimasi Rilis --}}
                    <div
                        class="p-4 sm:p-5 rounded-xl border border-slate-100 bg-white shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                                ESTIMASI RILIS
                            </div>
                            <div class="font-semibold text-slate-800 text-sm mb-0.5">
                                Tahap Finalisasi
                            </div>
                            <div class="text-xs text-slate-500 mb-4">
                                Pembaruan otomatis tanpa rekonfigurasi
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-slate-100/80 text-xs">
                            <span class="text-slate-400">Ketersediaan:</span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200/60 uppercase">
                                Segera Hadir
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 6. Bottom Notice Banner --}}
                <div class="p-3.5 rounded-xl border border-sky-100 bg-sky-50/50 flex items-start sm:items-center gap-3">
                    <x-icon name="info" size="18" class="text-sky-500 shrink-0 mt-0.5 sm:mt-0" />
                    <div class="text-xs text-slate-600 leading-relaxed">
                        <strong class="font-semibold text-slate-800">Pemberitahuan Sistem:</strong> Modul ini sudah
                        terdaftar resmi pada akun Anda. Begitu implementasi fitur selesai diperbarui oleh pengembang sistem,
                        tampilan ini akan otomatis berganti ke halaman kerja fitur tanpa perlu pengaturan ulang.
                    </div>
                </div>
            </div>
    @endif
@endsection
