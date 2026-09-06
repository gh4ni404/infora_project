<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\RestoreBackupRequest;
use App\Services\DatabaseBackupService;
use Exception;
use Illuminate\Http\RedirectResponse;
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
     * Display a listing of database backups and system database statistics.
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
     * Trigger generation of a fresh database backup dump.
     */
    public function store(): RedirectResponse
    {
        try {
            $backup = $this->backupService->createBackup();

            return redirect()
                ->route('backup-restore')
                ->with('success', "Cadangan basis data berhasil dibuat: {$backup['filename']} ({$backup['size_human']}).");
        } catch (Exception $e) {
            return redirect()
                ->route('backup-restore')
                ->with('error', 'Gagal membuat cadangan basis data: '.$e->getMessage());
        }
    }

    /**
     * Download an existing backup file to the client machine.
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

            return response()->download($path, $filename, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        } catch (Exception $e) {
            return redirect()
                ->route('backup-restore')
                ->with('error', 'Gagal mengunduh berkas cadangan: '.$e->getMessage());
        }
    }

    /**
     * Restore the database from an existing archive or an uploaded .sql file.
     */
    public function restore(RestoreBackupRequest $request): RedirectResponse
    {
        try {
            if ($request->hasFile('backup_file')) {
                $uploadedFile = $request->file('backup_file');
                $realPath = $uploadedFile->getRealPath();
                $originalName = $uploadedFile->getClientOriginalName();

                $this->backupService->restoreBackup($realPath);

                return redirect()
                    ->route('backup-restore')
                    ->with('success', "Basis data berhasil dipulihkan dari berkas unggahan: {$originalName}.");
            }

            $filename = (string) $request->input('filename');
            $path = $this->backupService->getBackupPath($filename);

            if (! $path) {
                return redirect()
                    ->route('backup-restore')
                    ->with('error', 'Berkas cadangan terpilih tidak ditemukan.');
            }

            $this->backupService->restoreBackup($path);

            return redirect()
                ->route('backup-restore')
                ->with('success', "Basis data berhasil dipulihkan dari cadangan server: {$filename}.");
        } catch (Exception $e) {
            return redirect()
                ->route('backup-restore')
                ->with('error', 'Gagal memulihkan basis data: '.$e->getMessage());
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
