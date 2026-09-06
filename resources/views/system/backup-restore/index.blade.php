@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Cadangan & Pemulihan Sistem</h2>
        <div class="page-subtitle">Pencadangan portabel (basis data & berkas aset) untuk migrasi server dan disaster recovery INFORA</div>
    </div>
    <div class="page-actions page-actions-group">
        <!-- Cadangan Lengkap ZIP (Primary) -->
        <form method="POST" action="{{ route('backup-restore.create') }}" class="form-inline-action">
            @csrf
            <input type="hidden" name="type" value="full">
            <button type="submit" class="btn-primary" id="btnCreateFullBackup" title="Buat arsip ZIP terpadu berisi basis data dan seluruh berkas aset pengguna">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Buat Cadangan Lengkap (ZIP)</span>
            </button>
        </form>

        <!-- Cadangan Database SQL (Secondary Outline) -->
        <form method="POST" action="{{ route('backup-restore.create') }}" class="form-inline-action">
            @csrf
            <input type="hidden" name="type" value="database">
            <button type="submit" class="btn-outline-primary" id="btnCreateDbBackup" title="Ekspor dump skema dan data basis data saja">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                </svg>
                <span>Cadangan Database (SQL)</span>
            </button>
        </form>
    </div>
</div>

@if (session('success'))
    <div class="alert-success">
        <div class="alert-content">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="alert-danger">
        <div class="alert-content">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="alert-danger">
        <div class="alert-content">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    </div>
@endif

<!-- Database & Storage Summary Statistics -->
<div class="backup-stats-grid">
    <div class="backup-stat-card">
        <div class="backup-stat-icon is-primary">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
            </svg>
        </div>
        <div class="backup-stat-content">
            <span class="backup-stat-label">Basis Data Aktif</span>
            <span class="backup-stat-value">{{ $stats['database_name'] }}</span>
        </div>
    </div>

    <div class="backup-stat-card">
        <div class="backup-stat-icon is-cyan">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                <line x1="3" y1="9" x2="21" y2="9"></line>
                <line x1="9" y1="21" x2="9" y2="9"></line>
            </svg>
        </div>
        <div class="backup-stat-content">
            <span class="backup-stat-label">Total Tabel Sistem</span>
            <span class="backup-stat-value">{{ $stats['total_tables'] }} Tabel</span>
        </div>
    </div>

    <div class="backup-stat-card">
        <div class="backup-stat-icon is-success">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
        </div>
        <div class="backup-stat-content">
            <span class="backup-stat-label">Aset Storage Pengguna</span>
            <span class="backup-stat-value">{{ $stats['public_storage_file_count'] }} Berkas ({{ $stats['public_storage_human'] }})</span>
        </div>
    </div>

    <div class="backup-stat-card">
        <div class="backup-stat-icon is-amber">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"></path>
                <path d="M12 12v9"></path>
                <path d="m8 17 4 4 4-4"></path>
            </svg>
        </div>
        <div class="backup-stat-content">
            <span class="backup-stat-label">Arsip Cadangan Tersimpan</span>
            <span class="backup-stat-value">{{ $stats['backup_count'] }} Berkas ({{ $stats['total_storage_human'] }})</span>
        </div>
    </div>
</div>

<!-- Security Alert Notice -->
<div class="backup-danger-box">
    <div class="backup-danger-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
    </div>
    <div class="backup-danger-text">
        <div class="backup-danger-title">Perhatian Khusus Pemulihan Data (*Full System Restore*)</div>
        Operasi pemulihan sistem bersifat <strong>destruktif menyeluruh</strong>. Data pada tabel dan berkas di dalam <code>storage/app/public/</code> akan digantikan oleh data dari berkas cadangan. Sistem secara otomatis memverifikasi dan menyambungkan kembali <em>symbolic link</em> storage agar seluruh aset gambar tetap terhubung dan bebas galat 404.
    </div>
</div>

<!-- Main Split: Archive List & Upload Form -->
<div class="backup-actions-split">
    <!-- Archive Table Card -->
    <div class="table-card">
        <div class="table-toolbar">
            <div class="table-cell-bold">Riwayat Berkas Cadangan Tersimpan</div>
            <div class="table-cell-muted">Total: <strong>{{ $backups->count() }}</strong> Berkas</div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Berkas</th>
                        <th>Tipe Cadangan</th>
                        <th>Ukuran</th>
                        <th>Waktu Pembuatan</th>
                        <th class="col-w-actions">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($backups as $backup)
                        <tr>
                            <td>
                                <div class="table-cell-bold">{{ $backup['filename'] }}</div>
                            </td>
                            <td>
                                @if ($backup['type'] === 'full')
                                    <span class="badge badge-purple" title="Snapshot Lengkap: Basis Data + Seluruh Berkas Aset">Full Snapshot (ZIP)</span>
                                @else
                                    <span class="badge badge-cyan" title="Hanya Skema & Data Basis Data">Database Saja (SQL)</span>
                                @endif
                            </td>
                            <td>
                                <span class="table-cell-bold">{{ $backup['size_human'] }}</span>
                            </td>
                            <td>
                                <span class="table-cell-muted">{{ $backup['created_at']->translatedFormat('d M Y, H:i') }}</span>
                            </td>
                            <td>
                                <div class="table-actions table-actions-right">
                                    <a href="{{ route('backup-restore.download', $backup['filename']) }}" class="btn-download" title="Unduh Berkas Cadangan">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                        </svg>
                                        <span>Unduh</span>
                                    </a>

                                    <button
                                        type="button"
                                        class="btn-restore btn-trigger-restore"
                                        data-filename="{{ $backup['filename'] }}"
                                        data-type="{{ $backup['type_label'] }}"
                                        data-size="{{ $backup['size_human'] }}"
                                        data-date="{{ $backup['created_at']->translatedFormat('d M Y, H:i:s') }}"
                                        title="Pulihkan Sistem dari Berkas Ini"
                                    >
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                            <path d="M3 3v5h5"></path>
                                        </svg>
                                        <span>Pulihkan</span>
                                    </button>

                                    <form method="POST" action="{{ route('backup-restore.destroy', $backup['filename']) }}" onsubmit="return confirm('Hapus permanen berkas cadangan {{ $backup['filename'] }} dari server?');" class="form-inline-action">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete" title="Hapus Berkas Cadangan">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <svg class="empty-state-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                                    </svg>
                                    <span class="empty-state-text">Belum ada berkas cadangan di server. Klik "Buat Cadangan Lengkap (ZIP)" untuk membuat cadangan portabel pertama Anda.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Upload & Restore Card -->
    <div class="card-surface">
        <div class="card-header">
            <div>
                <span class="table-cell-bold">Unggah & Pulihkan Eksternal</span>
                <div class="form-hint">Migrasi dari server lain / pulihkan berkas lokal</div>
            </div>
            <span class="badge badge-amber">Maks 200MB</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('backup-restore.restore') }}" enctype="multipart/form-data" id="formUploadRestore">
                @csrf
                <div class="form-group">
                    <label for="backupFileInput" class="form-label">Pilih Berkas Cadangan (.zip atau .sql)</label>
                    <input
                        type="file"
                        id="backupFileInput"
                        name="backup_file"
                        accept=".zip,.sql,.txt"
                        class="form-input"
                        required
                    >
                    <div class="form-hint">Mendukung paket snapshot lengkap (ZIP) atau skrip SQL basis data maksimal 200 megabyte.</div>
                </div>

                <div class="form-group">
                    <button type="button" class="btn-restore" id="btnTriggerUploadRestore">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        <span>Unggah & Mulai Pemulihan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Pemulihan Data (Danger Confirmation Modal) -->
<div class="modal-backdrop hidden" id="modalRestoreConfirmation" role="dialog" aria-modal="true" aria-labelledby="modalRestoreTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title text-danger" id="modalRestoreTitle">Konfirmasi Bahaya Pemulihan Sistem</h3>
                <p class="modal-subtitle">Tindakan ini akan menimpa basis data aktif dan menyelaraskan berkas penyimpanan</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseRestoreModal" aria-label="Tutup Dialog">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('backup-restore.restore') }}" id="formConfirmRestore">
            @csrf
            <input type="hidden" name="filename" id="restoreFilenameInput" value="">

            <div class="modal-body">
                <div class="backup-danger-box">
                    <div class="backup-danger-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                    <div class="backup-danger-text">
                        Anda akan memulihkan data sistem menggunakan berkas:
                        <div class="table-cell-bold" id="restoreTargetFilenameText">-</div>
                        <div class="form-hint" id="restoreTargetMetaText">-</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Ketik kata <strong>PULIHKAN</strong> di bawah untuk membuka kunci:</label>
                    <input
                        type="text"
                        id="restoreConfirmKeyword"
                        class="form-input"
                        placeholder="Ketik PULIHKAN"
                        autocomplete="off"
                    >
                    <div class="form-hint">Proteksi pencegahan eksekusi tidak sengaja.</div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelRestoreModal">Batal</button>
                <button type="submit" class="btn-delete" id="btnSubmitRestore" disabled>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                        <path d="M3 3v5h5"></path>
                    </svg>
                    <span>Lanjutkan Pemulihan Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalRestoreConfirmation');
    const btnClose = document.getElementById('btnCloseRestoreModal');
    const btnCancel = document.getElementById('btnCancelRestoreModal');
    const filenameInput = document.getElementById('restoreFilenameInput');
    const targetFilenameText = document.getElementById('restoreTargetFilenameText');
    const targetMetaText = document.getElementById('restoreTargetMetaText');
    const keywordInput = document.getElementById('restoreConfirmKeyword');
    const submitBtn = document.getElementById('btnSubmitRestore');
    const restoreTriggers = document.querySelectorAll('.btn-trigger-restore');

    // Upload & restore elements
    const btnTriggerUploadRestore = document.getElementById('btnTriggerUploadRestore');
    const backupFileInput = document.getElementById('backupFileInput');
    const formUploadRestore = document.getElementById('formUploadRestore');

    let activeRestoreMode = 'server'; // 'server' or 'upload'

    function openRestoreModal(filename, metaText, mode) {
        if (!modal) return;
        activeRestoreMode = mode;
        filenameInput.value = filename || '';
        targetFilenameText.textContent = filename || 'Berkas Unggahan Komputer';
        targetMetaText.textContent = metaText || '';
        keywordInput.value = '';
        submitBtn.disabled = true;

        modal.classList.remove('hidden');
        document.body.classList.add('modal-open');
        setTimeout(() => keywordInput.focus(), 50);
    }

    function closeRestoreModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    restoreTriggers.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const filename = btn.getAttribute('data-filename');
            const type = btn.getAttribute('data-type');
            const size = btn.getAttribute('data-size');
            const date = btn.getAttribute('data-date');
            openRestoreModal(filename, 'Tipe: ' + type + ' | Ukuran: ' + size + ' | Dibuat: ' + date, 'server');
        });
    });

    if (btnTriggerUploadRestore) {
        btnTriggerUploadRestore.addEventListener('click', function() {
            if (!backupFileInput.files || backupFileInput.files.length === 0) {
                alert('Silakan pilih berkas .zip atau .sql terlebih dahulu.');
                backupFileInput.focus();
                return;
            }
            const file = backupFileInput.files[0];
            const sizeHuman = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            openRestoreModal(file.name, 'Ukuran Unggahan: ' + sizeHuman, 'upload');
        });
    }

    keywordInput && keywordInput.addEventListener('input', function() {
        if (keywordInput.value.trim().toUpperCase() === 'PULIHKAN') {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    });

    const formConfirm = document.getElementById('formConfirmRestore');
    if (formConfirm) {
        formConfirm.addEventListener('submit', function(e) {
            if (activeRestoreMode === 'upload') {
                e.preventDefault();
                closeRestoreModal();
                formUploadRestore.submit();
            }
        });
    }

    btnClose && btnClose.addEventListener('click', closeRestoreModal);
    btnCancel && btnCancel.addEventListener('click', closeRestoreModal);

    modal && modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeRestoreModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeRestoreModal();
        }
    });
});
</script>
@endsection
