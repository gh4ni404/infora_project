<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\RestoreBackupRequest;
use App\Services\DatabaseBackupService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupRestoreController extends Controller
{
    /**
     * Create a new controller instance with dependency injection.
     */
    public function __construct(
        protected DatabaseBackupService $backupService
    ) {}

    /**
     * Display a listing of database & full system backups, storage usage, and system stats.
     */
    public function index(): View
    {
        $stats = $this->backupService->getStatistics();
        $backups = $this->backupService->listBackups();

        return view('system.backup-restore.index', [
            'stats' => $stats,
            'backups' => $backups,
        ]);
    }

    /**
     * Trigger generation of a fresh full snapshot (.zip) or database dump (.sql).
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $type = $request->input('type', 'full');
            $backup = $this->backupService->createBackup($type);

            $label = $backup['type'] === 'full'
                ? 'Cadangan lengkap sistem (Basis Data & Berkas Aset)'
                : 'Cadangan basis data';

            return redirect()
                ->route('backup-restore')
                ->with('success', "{$label} berhasil dibuat: {$backup['filename']} ({$backup['size_human']}).");
        } catch (Exception $e) {
            return redirect()
                ->route('backup-restore')
                ->with('error', 'Gagal membuat cadangan sistem: '.$e->getMessage());
        }
    }

    /**
     * Download an existing backup file (.zip or .sql) to the client machine.
     */
    public function download(string $filename): BinaryFileResponse|RedirectResponse
    {
        try {
            $path = $this->backupService->getBackupPath($filename);

            if (! $path) {
                return redirect()
                    ->route('backup-restore')
                    ->with('error', 'Berkas cadangan tidak ditemukan di server.');
            }

            $mimeType = str_ends_with($filename, '.zip') ? 'application/zip' : 'application/sql';

            return response()->download($path, $filename, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        } catch (Exception $e) {
            return redirect()
                ->route('backup-restore')
                ->with('error', 'Gagal mengunduh berkas cadangan: '.$e->getMessage());
        }
    }

    /**
     * Restore the system from an existing archive or an uploaded .zip / .sql file.
     */
    public function restore(RestoreBackupRequest $request): RedirectResponse
    {
        try {
            if ($request->hasFile('backup_file')) {
                $uploadedFile = $request->file('backup_file');
                $realPath = $uploadedFile->getRealPath();
                $originalName = $uploadedFile->getClientOriginalName();

                // If uploaded file is a zip or sql, preserve extension in temporary target for extension checks
                $ext = strtolower($uploadedFile->getClientOriginalExtension());
                $tempPath = storage_path('app/temp_upload_'.uniqid().'.'.$ext);
                copy($realPath, $tempPath);

                try {
                    $result = $this->backupService->restoreBackup($tempPath);
                } finally {
                    if (file_exists($tempPath)) {
                        @unlink($tempPath);
                    }
                }

                $prefix = $result['type'] === 'full' ? 'Pemulihan Sistem Lengkap' : 'Pemulihan Basis Data';

                return redirect()
                    ->route('backup-restore')
                    ->with('success', "{$prefix} berhasil dari berkas unggahan: {$originalName}. {$result['message']}");
            }

            $filename = (string) $request->input('filename');
            $path = $this->backupService->getBackupPath($filename);

            if (! $path) {
                return redirect()
                    ->route('backup-restore')
                    ->with('error', 'Berkas cadangan terpilih tidak ditemukan.');
            }

            $result = $this->backupService->restoreBackup($path);
            $prefix = $result['type'] === 'full' ? 'Pemulihan Sistem Lengkap' : 'Pemulihan Basis Data';

            return redirect()
                ->route('backup-restore')
                ->with('success', "{$prefix} berhasil dari arsip server: {$filename}. {$result['message']}");
        } catch (Exception $e) {
            return redirect()
                ->route('backup-restore')
                ->with('error', 'Gagal memulihkan sistem: '.$e->getMessage());
        }
    }

    /**
     * Delete a backup file permanently from the server storage.
     */
    public function destroy(string $filename): RedirectResponse
    {
        try {
            $deleted = $this->backupService->deleteBackup($filename);

            if (! $deleted) {
                return redirect()
                    ->route('backup-restore')
                    ->with('error', 'Berkas cadangan tidak ditemukan atau gagal dihapus.');
            }

            return redirect()
                ->route('backup-restore')
                ->with('success', "Berkas cadangan {$filename} berhasil dihapus dari server.");
        } catch (Exception $e) {
            return redirect()
                ->route('backup-restore')
                ->with('error', 'Terjadi kesalahan saat menghapus cadangan: '.$e->getMessage());
        }
    }
}
