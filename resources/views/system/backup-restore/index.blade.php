@extends('layouts.app')

@section('content')
<x-page-header
    title="Cadangan dan Pemulihan Sistem"
    subtitle="Pencadangan portabel (basis data dan berkas aset) untuk migrasi server dan disaster recovery INFORA">
    <x-slot:actions>
        <!-- Cadangan Lengkap ZIP (Primary) -->
        <form method="POST" action="{{ route('backup-restore.create') }}" class="form-inline-action form-backup-trigger" data-progress-stream>
            @csrf
            <input type="hidden" name="type" value="full">
            <button type="submit" class="btn-primary" id="btnCreateFullBackup" title="Buat Cadangan Lengkap (ZIP) - Buat arsip ZIP terpadu berisi basis data dan seluruh berkas aset pengguna">
                <x-icon name="download" size="16" />
                {{-- <span>Buat Cadangan Lengkap (ZIP)</span> --}}
            </button>
        </form>

        <!-- Cadangan Database SQL (Secondary Outline) -->
        <form method="POST" action="{{ route('backup-restore.create') }}" class="form-inline-action form-backup-trigger" data-progress-stream>
            @csrf
            <input type="hidden" name="type" value="database">
            <button type="submit" class="btn-outline-primary" id="btnCreateDbBackup" title="Cadangan Database (SQL) - Ekspor dump skema dan data basis data saja">
                <x-icon name="database" size="16" />
                {{-- <span>Cadangan Database (SQL)</span> --}}
            </button>
        </form>
    </x-slot:actions>
</x-page-header>

<x-alert type="success" />
<x-alert type="info" />
<x-alert type="error" />
<x-alert type="validation" />

<!-- Database dan Storage Summary Statistics -->
<x-stats-grid>
    <x-stat-card icon="database" color="primary" label="Basis Data Aktif" value="{{ $stats['database_name'] }}" />
    <x-stat-card icon="layout-dashboard" color="cyan" label="Total Tabel Sistem" value="{{ $stats['total_tables'] }} Tabel" />
    <x-stat-card icon="folder" color="success" label="Aset Storage Pengguna" value="{{ $stats['public_storage_file_count'] }} Berkas ({{ $stats['public_storage_human'] }})" />
    <x-stat-card icon="cloud-download" color="amber" label="Arsip Cadangan Tersimpan" value="{{ $stats['backup_count'] }} Berkas ({{ $stats['total_storage_human'] }})" />
</x-stats-grid>

<!-- Main Split: Archive List dan Upload Form -->
<div class="grid-split-2-1">
    <!-- Archive Table Card -->
    <x-data-table
        title="Riwayat Berkas Cadangan Tersimpan"
        :total="$backups->count()"
        total-label="Berkas">
        @if ($backups->isNotEmpty())
            <x-slot:actions>
                <button type="button" class="btn-delete" id="btnOpenDeleteAllBackups" title="Hapus semua berkas cadangan dari server">
                    <x-icon name="delete-2" size="18" />
                    {{-- <span>Hapus Semua Cadangan</span> --}}
                </button>
            </x-slot:actions>
        @endif

        <thead>
            <tr>
                <th>Nama Berkas</th>
                <th>Tipe Cadangan</th>
                <th>Ukuran</th>
                <th>Waktu Pembuatan</th>
                <th>Aksi</th>
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
                            <span class="badge badge-primary" title="Snapshot Lengkap: Basis Data + Seluruh Berkas Aset">ZIP</span>
                        @else
                            <span class="badge badge-cyan" title="Hanya Skema dan Data Basis Data">SQL</span>
                        @endif
                    </td>
                    <td>
                        <span class="table-cell-bold">{{ $backup['size_human'] }}</span>
                    </td>
                    <td>
                        <span class="table-cell-muted">{{ $backup['created_at']->translatedFormat('d M Y, H:i') }}</span>
                    </td>
                    <td>
                        <x-table-actions>
                            <a href="{{ route('backup-restore.download', $backup['filename']) }}" class="btn-download" data-no-progress download title="Unduh Berkas Cadangan">
                                <x-icon name="download" size="14" />
                                <!-- <span>Unduh</span> -->
                            </a>

                            <button
                                type="button"
                                class="btn-restore btn-trigger-restore"
                                data-filename="{{ $backup['filename'] }}"
                                data-type="{{ $backup['type_label'] }}"
                                data-size="{{ $backup['size_human'] }}"
                                data-date="{{ $backup['created_at']->translatedFormat('d M Y, H:i:s') }}"
                                title="Pulihkan Sistem dari Berkas Ini">
                                <x-icon name="restore" size="14" />
                                <!-- <span>Pulihkan</span> -->
                            </button>

                            <button type="button" class="btn-delete btn-open-delete-backup" title="Hapus Berkas Cadangan"
                                data-filename="{{ $backup['filename'] }}"
                                data-type="{{ $backup['type'] === 'full' ? 'ZIP' : 'SQL' }}"
                                data-action="{{ route('backup-restore.destroy', $backup['filename']) }}">
                                <x-icon name="delete" size="14" />
                                <!-- <span>Hapus</span> -->
                            </button>
                        </x-table-actions>
                    </td>
                </tr>
            @empty
                <x-empty-state icon="folder" colspan="5" title="Belum ada berkas cadangan di server." text='Klik "Buat Cadangan Lengkap (ZIP)" untuk membuat cadangan portabel pertama Anda.'/>
            @endforelse
        </tbody>
    </x-data-table>

    <!-- Upload dan Restore Card -->
    <div class="card-surface">
        <div class="card-header">
            <div>
                <span class="table-cell-bold">Unggah dan Pulihkan Eksternal</span>
                <div class="form-hint">Migrasi dari server lain / pulihkan berkas lokal</div>
            </div>
            <span class="badge badge-amber">Maks 250MB</span>
        </div>
        <div class="card-body">
            <!-- Security Alert Notice -->
            <div class="callout-danger">
                <div class="callout-icon">
                    <x-icon name="alert-triangle" size="22" />
                </div>
                <div class="callout-text">
                    <div class="callout-title">Perhatian Khusus Pemulihan Data (*Full System Restore*)</div>
                    Operasi pemulihan sistem bersifat <strong>destruktif menyeluruh</strong>. Data pada tabel dan berkas di dalam <code>storage/app/public/</code> akan digantikan oleh data dari berkas cadangan. Sistem secara otomatis memverifikasi dan menyambungkan kembali <em>symbolic link</em> storage agar seluruh aset gambar tetap terhubung dan bebas galat 404.
                </div>
            </div>
            <form method="POST" action="{{ route('backup-restore.restore') }}" enctype="multipart/form-data" id="formUploadRestore" data-progress-stream>
                @csrf
                <x-form-input
                    type="file"
                    name="backup_file"
                    id="backupFileInput"
                    label="Pilih Berkas Cadangan (.zip atau .sql)"
                    hint="Mendukung paket snapshot lengkap (ZIP) atau skrip SQL basis data maksimal 250 megabyte."
                    accept=".zip,.sql,.txt"
                    required
                />

                <div class="form-group">
                    <button type="button" class="btn-restore" id="btnTriggerUploadRestore">
                        <x-icon name="upload" size="16" />
                        <span>Unggah dan Mulai Pemulihan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>    
</div>

<!-- Modal Konfirmasi Pemulihan Data (Danger Confirmation Modal) -->
<x-modal
    id="modalRestoreConfirmation"
    title="Konfirmasi Bahaya Pemulihan Sistem"
    subtitle="Tindakan ini akan menimpa basis data aktif dan menyelaraskan berkas penyimpanan"
    action="{{ route('backup-restore.restore') }}"
    form-id="formConfirmRestore">

    <x-form-input
        type="hidden"
        name="filename"
        id="restoreFilenameInput"
        value=""
    />

    <div class="callout-danger">
        <div class="callout-icon">
            <x-icon name="alert-triangle" size="22" />
        </div>
        <div class="callout-text">
            Anda akan memulihkan data sistem menggunakan berkas:
            <div class="table-cell-bold" id="restoreTargetFilenameText">-</div>
            <div class="form-hint" id="restoreTargetMetaText">-</div>
        </div>
    </div>

    <x-form-input
        name="confirm_keyword"
        id="restoreConfirmKeyword"
        placeholder="Ketik PULIHKAN"
        hint="Proteksi pencegahan eksekusi tidak sengaja."
        autocomplete="off"
    >
        <x-slot:label>
            Ketik kata <strong>PULIHKAN</strong> di bawah untuk membuka kunci:
        </x-slot:label>
    </x-form-input>

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelRestoreModal">Batal</button>
        <button type="submit" class="btn-delete" id="btnSubmitRestore" disabled>
            <span>Pulihkan</span>
        </button>
    </x-slot:footer>
</x-modal>

{{-- Modal Konfirmasi Hapus Berkas Cadangan Satuan --}}
<x-modal 
    name="delete" 
    id="modalDeleteBackup" 
    form-id="formDeleteBackup" 
    cancel-btn-id="btnCancelDeleteBackup"
    confirm-btn-id="btnConfirmDeleteBackup"
    message-id="deleteBackupMessage"
    badge-id="deleteBackupBadge"
    item-title-id="deleteBackupTitle" />

{{-- Modal Konfirmasi Hapus Semua Cadangan --}}
@if ($backups->isNotEmpty())
    <x-modal 
        name="delete" 
        id="modalDeleteAllBackups" 
        title="Hapus Semua Cadangan?"
        action="{{ route('backup-restore.destroy-all') }}"
        form-id="formDeleteAllBackups" 
        cancel-btn-id="btnCancelDeleteAllBackups"
        confirm-btn-id="btnConfirmDeleteAllBackups">
        <p class="text-sm text-gray-600 mb-2.5">
            PERINGATAN: Apakah Anda yakin ingin menghapus seluruh berkas cadangan dari server?
        </p>
        <div class="p-3.5 my-8 mx-6 bg-slate-50 border border-slate-200/90 rounded-xl text-center shadow-xs">
            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-mono font-bold bg-red-100 text-red-700 rounded-md mb-1.5">
                {{ $backups->count() }} Berkas Tersimpan
            </span>
            <div class="text-sm font-bold text-slate-900 leading-snug">Semua Berkas Cadangan Server</div>
        </div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-700 border border-red-100">
            <x-icon name="alert-circle" size="14" />
            <span>Tindakan ini permanen dan tidak dapat dibatalkan.</span>
        </div>
    </x-modal>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Setup Modal Konfirmasi Pemulihan
    InforaModals.setup('modalRestoreConfirmation', { cancelBtnId: 'btnCancelRestoreModal' });

    // 2. Setup dan Binding Modal Hapus Berkas Cadangan Satuan
    InforaModals.setup('modalDeleteBackup', { cancelBtnId: 'btnCancelDeleteBackup' });
    InforaModals.bindDeleteButtons('.btn-open-delete-backup', 'modalDeleteBackup', function(modal, btn) {
        document.getElementById('modalDeleteBackupTitle').textContent = 'Hapus Berkas Cadangan?';
        document.getElementById('deleteBackupMessage').textContent = 'Apakah Anda yakin ingin menghapus berkas cadangan berikut dari server?';
        document.getElementById('deleteBackupBadge').textContent = btn.dataset.type || (btn.dataset.filename.endsWith('.zip') ? 'ZIP' : 'SQL');
        document.getElementById('deleteBackupTitle').textContent = btn.dataset.filename;
    });

    // 3. Setup dan Binding Modal Hapus Semua Berkas Cadangan
    InforaModals.setup('modalDeleteAllBackups', { cancelBtnId: 'btnCancelDeleteAllBackups' });
    InforaModals.bindOpenButton('btnOpenDeleteAllBackups', 'modalDeleteAllBackups');

    // Elemen modal konfirmasi pemulihan
    const filenameInput = document.getElementById('restoreFilenameInput');
    const targetFilenameText = document.getElementById('restoreTargetFilenameText');
    const targetMetaText = document.getElementById('restoreTargetMetaText');
    const keywordInput = document.getElementById('restoreConfirmKeyword');
    const submitConfirmBtn = document.getElementById('btnSubmitRestore');
    const restoreTriggers = document.querySelectorAll('.btn-trigger-restore');

    // Elemen unggah dan formulir pemulihan
    const btnTriggerUploadRestore = document.getElementById('btnTriggerUploadRestore');
    const backupFileInput = document.getElementById('backupFileInput');
    const formUploadRestore = document.getElementById('formUploadRestore');
    const formConfirm = document.getElementById('formConfirmRestore');

    let activeRestoreMode = 'server'; // 'server' or 'upload'

    function openRestoreModal(filename, metaText, mode) {
        activeRestoreMode = mode;
        if (filenameInput) filenameInput.value = filename || '';
        if (targetFilenameText) targetFilenameText.textContent = filename || 'Berkas Unggahan Komputer';
        if (targetMetaText) targetMetaText.textContent = metaText || '';
        if (keywordInput) keywordInput.value = '';
        if (submitConfirmBtn) submitConfirmBtn.disabled = true;

        InforaModals.open('modalRestoreConfirmation');
    }

    function closeRestoreModal() {
        InforaModals.close('modalRestoreConfirmation');
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

    if (keywordInput) {
        keywordInput.addEventListener('input', function() {
            submitConfirmBtn.disabled = this.value.trim().toUpperCase() !== 'PULIHKAN';
        });
    }

    // Attach stream handler to backup generation forms via Universal InforaProgress API
    const backupForms = document.querySelectorAll('.form-backup-trigger');
    backupForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const type = form.querySelector('input[name="type"]')?.value || 'full';
            const title = type === 'full'
                ? 'Membuat Cadangan Lengkap Sistem (ZIP)'
                : 'Membuat Cadangan Basis Data (SQL)';
            const formData = new FormData(form);
            formData.append('stream', '1');

            if (window.InforaProgress) {
                window.InforaProgress.stream(form.action, formData, {
                    title: title,
                    statusText: 'Menginisialisasi pencadangan...',
                });
            } else {
                form.submit();
            }
        });
    });

    // Attach stream handler to confirmed restore action via Universal InforaProgress API
    if (formConfirm) {
        formConfirm.addEventListener('submit', function(e) {
            e.preventDefault();
            closeRestoreModal();

            if (activeRestoreMode === 'upload') {
                const formData = new FormData(formUploadRestore);
                formData.append('stream', '1');

                if (window.InforaProgress) {
                    window.InforaProgress.stream(formUploadRestore.action, formData, {
                        title: 'Memulihkan Sistem dari Berkas Unggahan',
                        statusText: 'Mempersiapkan berkas unggahan...',
                    });
                } else {
                    formUploadRestore.submit();
                }
            } else {
                const formData = new FormData(formConfirm);
                formData.append('stream', '1');

                if (window.InforaProgress) {
                    window.InforaProgress.stream(formConfirm.action, formData, {
                        title: 'Memulihkan Sistem dari Arsip Server',
                        statusText: 'Mempersiapkan pemulihan arsip...',
                    });
                } else {
                    formConfirm.submit();
                }
            }
        });
    }
});
</script>
@endsection
