@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <div class="page-breadcrumbs" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: var(--infora-text-muted); margin-bottom: 0.375rem;">
            <span>{{ $module?->name ?? 'SISTEM' }}</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            @if ($itemType === 'Sub-Menu' && $menu)
                <span>{{ $menu->name }}</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            @endif
            <span style="color: var(--infora-brand-primary); font-weight: 600;">{{ $item->name }}</span>
        </div>
        <h2 class="page-title">{{ $item->name }}</h2>
        <div class="page-subtitle">Item {{ strtolower($itemType) }} aktif pada struktur navigasi INFORA</div>
    </div>
    <div class="page-actions">
        <a href="{{ $editUrl }}" class="btn-secondary" title="Ubah data {{ strtolower($itemType) }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 20h9"></path>
                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
            </svg>
            <span>Edit {{ $itemType }}</span>
        </a>
        <a href="{{ route('dashboard') }}" class="btn-secondary" title="Kembali ke Dashboard">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <span>Dashboard</span>
        </a>
    </div>
</div>

<div class="card-surface" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 36px; height: 36px; border-radius: 0.5rem; background: rgba(245, 158, 11, 0.12); color: #f59e0b; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(245, 158, 11, 0.25);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
            </div>
            <div>
                <span class="user-name-text">Status Fitur: Dalam Tahap Pengembangan</span>
                <div class="brand-subtitle">Modul ini telah siap di struktur menu dan menunggu implementasi kode</div>
            </div>
        </div>
        <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);">Under Development</span>
    </div>

    <div class="card-body">
        <div class="alert-info" style="display: flex; gap: 1rem; align-items: flex-start; margin-bottom: 1.5rem;">
            <svg style="flex-shrink: 0; margin-top: 2px;" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <div>
                <strong style="color: var(--infora-brand-primary);">Navigasi Telah Terdaftar:</strong> 
                Item <strong>{{ $item->name }}</strong> sudah berhasil terdaftar pada navigasi sistem INFORA. 
                Tautan di sidebar diarahkan ke halaman pratinjau ini karena rute backend <code>{{ $rawRoute ?: '(kosong)' }}</code> belum diaktifkan di file <code>routes/web.php</code>.
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div class="card-surface" style="padding: 1.25rem; background: var(--infora-surface-elevated);">
                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--infora-text-muted); margin-bottom: 0.5rem; font-weight: 600;">Detail Navigasi</div>
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
                        <code style="font-size: 0.8125rem; color: var(--infora-brand-primary); background: var(--infora-surface-muted); padding: 0.125rem 0.375rem; border-radius: 0.25rem;">
                            {{ $rawRoute ?: 'Belum diisi' }}
                        </code>
                    </div>
                </div>
            </div>

            <div class="card-surface" style="padding: 1.25rem; background: var(--infora-surface-elevated);">
                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--infora-text-muted); margin-bottom: 0.5rem; font-weight: 600;">Status Operasional</div>
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

        <div class="route-guide-box" style="margin-top: 1rem; padding: 1.25rem; background: var(--infora-surface-card); border: 1px solid var(--infora-border);">
            <div class="route-guide-title" style="font-size: 0.9375rem; margin-bottom: 0.75rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="16 18 22 12 16 6"></polyline>
                    <polyline points="8 6 2 12 8 18"></polyline>
                </svg>
                <span>Panduan Developer (Blueprint Implementasi)</span>
            </div>
            <p style="color: var(--infora-text-muted); font-size: 0.8125rem; margin-bottom: 0.75rem;">
                Untuk mengaktifkan fitur ini, developer cukup membuat Controller dan mendaftarkan rute di <code>routes/web.php</code>:
            </p>

            <div style="margin-bottom: 1rem;">
                <div style="font-size: 0.75rem; font-weight: 600; color: var(--infora-text-dim); margin-bottom: 0.25rem;">1. Buat Controller via Artisan:</div>
                <div style="background: #0f172a; padding: 0.625rem 0.875rem; border-radius: 0.375rem; font-family: monospace; font-size: 0.8125rem; color: #38bdf8; overflow-x: auto; border: 1px solid rgba(255, 255, 255, 0.08);">
                    {{ $suggestedArtisanCmd }}
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <div style="font-size: 0.75rem; font-weight: 600; color: var(--infora-text-dim); margin-bottom: 0.25rem;">2. Daftarkan Rute di routes/web.php:</div>
                <div style="background: #0f172a; padding: 0.625rem 0.875rem; border-radius: 0.375rem; font-family: monospace; font-size: 0.8125rem; color: #a5f3fc; overflow-x: auto; border: 1px solid rgba(255, 255, 255, 0.08);">
                    {{ $suggestedRouteCode }}
                </div>
            </div>

            <p style="color: var(--infora-text-dim); font-size: 0.75rem; margin-top: 0.5rem; margin-bottom: 0;">
                💡 Setelah rute di atas terdaftar, tautan di sidebar akan secara otomatis mengarah ke halaman controller tersebut tanpa perlu mengubah konfigurasi database lagi!
            </p>
        </div>

        <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--infora-border);">
            <a href="{{ $editUrl }}" class="btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9"></path>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                </svg>
                <span>Edit Konfigurasi {{ $itemType }}</span>
            </a>
            <a href="{{ route('dashboard') }}" class="btn-secondary">
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>
</div>
@endsection
