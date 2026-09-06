<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

class DatabaseBackupService
{
    /**
     * Get the directory where database backups are stored.
     */
    public function getBackupDirectory(): string
    {
        $directory = storage_path('app/backups');

        File::ensureDirectoryExists($directory);

        return $directory;
    }

    /**
     * Create a backup according to specified type ('full' for ZIP or 'database' for SQL).
     *
     * @param  (callable(int, string, string): void)|null  $progressCallback
     * @return array{filename: string, path: string, size_bytes: int, size_human: string, created_at: Carbon, type: string}
     */
    public function createBackup(string $type = 'full', ?callable $progressCallback = null): array
    {
        if ($type === 'database') {
            return $this->createDatabaseBackup(null, $progressCallback);
        }

        return $this->createFullBackup($progressCallback);
    }

    /**
     * Create a full portable snapshot (.zip) containing database.sql, storage assets, and manifest.json.
     *
     * @param  (callable(int, string, string): void)|null  $progressCallback
     * @return array{filename: string, path: string, size_bytes: int, size_human: string, created_at: Carbon, type: string, files_count: int}
     */
    public function createFullBackup(?callable $progressCallback = null): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP ZipArchive tidak terpasang di server.');
        }

        $report = function (int $pct, string $stage, string $detail) use ($progressCallback): void {
            if (is_callable($progressCallback)) {
                $progressCallback($pct, $stage, $detail);
            }
        };

        $report(5, 'Inisialisasi', 'Menyiapkan direktori dan parameter pencadangan penuh...');

        $backupDir = $this->getBackupDirectory();
        $timestamp = Carbon::now()->format('Ymd_His');
        $zipFilename = "backup_infora_full_{$timestamp}.zip";
        $zipPath = $backupDir.DIRECTORY_SEPARATOR.$zipFilename;

        // 1. Generate temporary database dump (10% - 50%)
        $tempSqlFilename = "temp_db_{$timestamp}.sql";
        $dbDump = $this->createDatabaseBackup($tempSqlFilename, $progressCallback, 10, 50);
        $tempSqlPath = $dbDump['path'];

        $publicStoragePath = storage_path('app/public');
        File::ensureDirectoryExists($publicStoragePath);

        $report(52, 'Pengarsipan Berkas', 'Menyiapkan berkas arsip ZIP dan menautkan skrip SQL...');

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            File::delete($tempSqlPath);
            throw new RuntimeException("Gagal menginisialisasi berkas arsip ZIP di: {$zipPath}");
        }

        try {
            // 2. Add database dump to ZIP root
            $zip->addFile($tempSqlPath, 'database.sql');

            // 3. Collect & add all user uploaded files from storage/app/public/ (55% - 85%)
            $storageFilesCount = 0;
            $storageSizeBytes = 0;

            if (File::isDirectory($publicStoragePath)) {
                $allFiles = File::allFiles($publicStoragePath);
                $totalFiles = count($allFiles);

                $report(55, 'Pengarsipan Berkas', "Mendeteksi {$totalFiles} berkas di penyimpanan publik...");

                foreach ($allFiles as $idx => $file) {
                    $filePath = $file->getRealPath();
                    $relativePath = 'storage/'.substr($filePath, strlen($publicStoragePath) + 1);
                    $zip->addFile($filePath, $relativePath);
                    $storageFilesCount++;
                    $storageSizeBytes += (int) $file->getSize();

                    if ($idx % 10 === 0 || $idx === $totalFiles - 1) {
                        $filePct = 55 + (int) round((($idx + 1) / max(1, $totalFiles)) * 30);
                        $report($filePct, 'Pengarsipan Berkas', "Mengarsipkan berkas: {$file->getFilename()} (".($idx + 1)."/{$totalFiles})...");
                    }
                }
            }

            // 4. Compile manifest.json (88% - 95%)
            $report(88, 'Finalisasi Arsip', 'Menyusun berkas manifest.json dan informasi metadata...');

            $manifest = [
                'app_name' => config('app.name', 'INFORA Platform'),
                'backup_type' => 'full',
                'format_version' => '2.0',
                'created_at' => Carbon::now()->toIso8601String(),
                'created_at_human' => Carbon::now()->translatedFormat('d F Y H:i:s'),
                'environment' => config('app.env', 'production'),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'database' => [
                    'name' => DB::connection()->getDatabaseName(),
                    'driver' => DB::connection()->getDriverName(),
                    'total_tables' => count($this->getAllTables()),
                    'dump_size_bytes' => $dbDump['size_bytes'],
                ],
                'storage' => [
                    'source_path' => 'storage/app/public',
                    'target_symlink' => 'public/storage',
                    'total_files' => $storageFilesCount,
                    'total_size_bytes' => $storageSizeBytes,
                ],
            ];

            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $report(95, 'Finalisasi Arsip', 'Mengompresi dan mengunci berkas arsip ZIP...');
        } finally {
            $zip->close();
            // Clean up temporary database dump
            File::delete($tempSqlPath);
        }

        $sizeBytes = (int) File::size($zipPath);

        $report(100, 'Selesai', "Cadangan lengkap ZIP berhasil dibuat ({$this->formatBytes($sizeBytes)}).");

        return [
            'filename' => $zipFilename,
            'path' => $zipPath,
            'size_bytes' => $sizeBytes,
            'size_human' => $this->formatBytes($sizeBytes),
            'created_at' => Carbon::now(),
            'type' => 'full',
            'files_count' => $storageFilesCount,
        ];
    }

    /**
     * Create a fresh database-only SQL dump.
     *
     * @param  (callable(int, string, string): void)|null  $progressCallback
     * @return array{filename: string, path: string, size_bytes: int, size_human: string, created_at: Carbon, type: string}
     */
    public function createDatabaseBackup(
        ?string $customFilename = null,
        ?callable $progressCallback = null,
        int $startPercent = 0,
        int $endPercent = 100
    ): array {
        $report = function (int $pct, string $stage, string $detail) use ($progressCallback): void {
            if (is_callable($progressCallback)) {
                $progressCallback($pct, $stage, $detail);
            }
        };

        $report($startPercent + 1, 'Ekspor Basis Data', 'Menginisialisasi berkas penampung SQL...');

        $directory = $this->getBackupDirectory();
        $filename = $customFilename ?: 'backup_infora_db_'.Carbon::now()->format('Ymd_His').'.sql';
        $filePath = $directory.DIRECTORY_SEPARATOR.$filename;

        $handle = fopen($filePath, 'w');
        if (! $handle) {
            throw new RuntimeException("Gagal membuat berkas cadangan di: {$filePath}");
        }

        try {
            $pdo = DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
            $driver = DB::connection()->getDriverName();

            // Header metadata & safe SQL state
            fwrite($handle, "-- ========================================================\n");
            fwrite($handle, "-- INFORA PLATFORM - DATABASE BACKUP DUMP\n");
            fwrite($handle, '-- Tanggal Pembuatan : '.Carbon::now()->translatedFormat('d F Y H:i:s')."\n");
            fwrite($handle, "-- Basis Data       : {$dbName}\n");
            fwrite($handle, "-- Driver           : {$driver}\n");
            fwrite($handle, "-- ========================================================\n\n");

            if ($driver === 'sqlite') {
                fwrite($handle, "PRAGMA foreign_keys = OFF;\n\n");
            } else {
                fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
                fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
                fwrite($handle, "SET time_zone = '+00:00';\n");
                fwrite($handle, "SET NAMES utf8mb4;\n\n");
            }

            // Fetch all base tables
            $tables = $this->getAllTables();
            $totalTables = count($tables);

            $report($startPercent + 2, 'Ekspor Basis Data', "Mengidentifikasi tabel ({$totalTables} tabel terdeteksi)...");

            foreach ($tables as $index => $table) {
                $tablePct = $startPercent + (int) round((($index + 1) / max(1, $totalTables)) * ($endPercent - $startPercent - 3));
                $report($tablePct, 'Ekspor Basis Data', "Mengekspor struktur dan baris tabel `{$table}` (".($index + 1)."/{$totalTables})...");

                // Table schema
                fwrite($handle, "-- --------------------------------------------------------\n");
                fwrite($handle, "-- Struktur Tabel untuk `{$table}`\n");
                fwrite($handle, "-- --------------------------------------------------------\n");
                fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

                $createSql = $this->getTableCreateSql($table);
                if ($createSql) {
                    fwrite($handle, $createSql.";\n\n");
                }

                // Table data
                fwrite($handle, "-- Data untuk Tabel `{$table}`\n");
                $rowCount = DB::table($table)->count();

                if ($rowCount > 0) {
                    DB::table($table)->orderBy(DB::raw('1'))->chunk(500, function ($rows) use ($handle, $table, $pdo) {
                        $values = [];
                        foreach ($rows as $row) {
                            $rowArray = (array) $row;
                            $escapedValues = array_map(function ($val) use ($pdo) {
                                if (is_null($val)) {
                                    return 'NULL';
                                }

                                return $pdo->quote((string) $val);
                            }, $rowArray);

                            $values[] = '('.implode(', ', $escapedValues).')';
                        }

                        if (! empty($values)) {
                            $columns = array_keys((array) $rows[0]);
                            $quotedColumns = array_map(fn ($col) => "`{$col}`", $columns);
                            $insertSql = "INSERT INTO `{$table}` (".implode(', ', $quotedColumns).") VALUES\n".implode(",\n", $values).";\n\n";
                            fwrite($handle, $insertSql);
                        }
                    });
                } else {
                    fwrite($handle, "-- (Tidak ada data)\n\n");
                }
            }

            // Footer reset
            if ($driver === 'sqlite') {
                fwrite($handle, "PRAGMA foreign_keys = ON;\n");
            } else {
                fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            }

            fwrite($handle, "-- ===================== SELESAI ======================\n");
        } finally {
            fclose($handle);
        }

        $sizeBytes = (int) File::size($filePath);

        if ($endPercent === 100) {
            $report(100, 'Selesai', "Ekspor basis data berhasil ({$this->formatBytes($sizeBytes)}).");
        }

        return [
            'filename' => $filename,
            'path' => $filePath,
            'size_bytes' => $sizeBytes,
            'size_human' => $this->formatBytes($sizeBytes),
            'created_at' => Carbon::now(),
            'type' => 'database',
        ];
    }

    /**
     * List all database & full backups ordered by newest first.
     *
     * @return Collection<int, array{filename: string, path: string, size_bytes: int, size_human: string, created_at: Carbon, timestamp: int, type: string, type_label: string}>
     */
    public function listBackups(): Collection
    {
        $directory = $this->getBackupDirectory();
        $files = array_merge(
            File::glob($directory.DIRECTORY_SEPARATOR.'*.zip') ?: [],
            File::glob($directory.DIRECTORY_SEPARATOR.'*.sql') ?: []
        );

        $backups = collect($files)->map(function (string $path) {
            $filename = basename($path);
            $sizeBytes = (int) File::size($path);
            $mtime = File::lastModified($path);
            $isZip = str_ends_with($filename, '.zip');

            return [
                'filename' => $filename,
                'path' => $path,
                'size_bytes' => $sizeBytes,
                'size_human' => $this->formatBytes($sizeBytes),
                'created_at' => Carbon::createFromTimestamp($mtime),
                'timestamp' => $mtime,
                'type' => $isZip ? 'full' : 'database',
                'type_label' => $isZip ? 'Full Snapshot (ZIP)' : 'Database Saja (SQL)',
            ];
        })->sortByDesc('timestamp')->values();

        return $backups;
    }

    /**
     * Get safe sanitized absolute path of a backup file (.sql or .zip).
     */
    public function getBackupPath(string $filename): ?string
    {
        $sanitized = basename($filename);
        if ($sanitized !== $filename || (! str_ends_with($filename, '.sql') && ! str_ends_with($filename, '.zip'))) {
            throw new InvalidArgumentException('Nama berkas cadangan tidak valid.');
        }

        $path = $this->getBackupDirectory().DIRECTORY_SEPARATOR.$sanitized;

        return File::exists($path) ? $path : null;
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        $path = $this->getBackupPath($filename);

        if (! $path) {
            return false;
        }

        return File::delete($path);
    }

    /**
     * Restore system from an SQL script or full ZIP archive.
     *
     * @param  (callable(int, string, string): void)|null  $progressCallback
     * @return array{type: string, message: string}
     */
    public function restoreBackup(string $filePath, ?callable $progressCallback = null): array
    {
        if (! File::exists($filePath) || ! File::isReadable($filePath)) {
            throw new RuntimeException('Berkas cadangan tidak ditemukan atau tidak dapat dibaca.');
        }

        if (str_ends_with($filePath, '.zip')) {
            return $this->restoreFromZip($filePath, $progressCallback);
        }

        $this->restoreDatabaseFromSql($filePath, $progressCallback);

        return [
            'type' => 'database',
            'message' => 'Basis data berhasil dipulihkan dari skrip SQL.',
        ];
    }

    /**
     * Restore database from raw SQL file.
     *
     * @param  (callable(int, string, string): void)|null  $progressCallback
     */
    public function restoreDatabaseFromSql(string $filePath, ?callable $progressCallback = null): void
    {
        $report = function (int $pct, string $stage, string $detail) use ($progressCallback): void {
            if (is_callable($progressCallback)) {
                $progressCallback($pct, $stage, $detail);
            }
        };

        $report(10, 'Pemulihan Basis Data', 'Membaca dan memverifikasi isi skrip SQL cadangan...');

        $sqlContent = File::get($filePath);

        if (trim($sqlContent) === '') {
            throw new RuntimeException('Berkas cadangan basis data kosong.');
        }

        $report(25, 'Pemulihan Basis Data', 'Menonaktifkan kunci relasi basis data...');
        $this->disableForeignKeys();

        try {
            $report(45, 'Pemulihan Basis Data', 'Mengeksekusi skema dan record data ke dalam basis data...');
            DB::unprepared($sqlContent);
            $report(85, 'Pemulihan Basis Data', 'Mengaktifkan kembali kunci relasi basis data...');
        } finally {
            $this->enableForeignKeys();
        }

        $report(100, 'Pemulihan Basis Data', 'Basis data berhasil dipulihkan sepenuhnya.');
    }

    /**
     * Restore full snapshot (database + storage public files + symlink auto-link) from a ZIP archive.
     *
     * @param  (callable(int, string, string): void)|null  $progressCallback
     * @return array{type: string, message: string}
     */
    protected function restoreFromZip(string $zipPath, ?callable $progressCallback = null): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP ZipArchive tidak terpasang di server.');
        }

        $report = function (int $pct, string $stage, string $detail) use ($progressCallback): void {
            if (is_callable($progressCallback)) {
                $progressCallback($pct, $stage, $detail);
            }
        };

        $report(5, 'Inisialisasi', 'Membuka dan memeriksa integritas arsip ZIP...');

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Gagal membuka berkas arsip ZIP cadangan.');
        }

        $tempExtractDir = storage_path('app/temp_restore_'.uniqid());
        File::ensureDirectoryExists($tempExtractDir);

        try {
            $report(15, 'Ekstraksi Arsip', 'Mengekstrak paket arsip ke direktori sementara...');
            $zip->extractTo($tempExtractDir);
            $zip->close();

            $report(30, 'Ekstraksi Arsip', 'Ekstraksi arsip selesai. Memverifikasi struktur paket...');

            // 1. Restore database if database.sql is present (30% - 70%)
            $sqlFile = $tempExtractDir.DIRECTORY_SEPARATOR.'database.sql';
            if (File::exists($sqlFile)) {
                $report(35, 'Pemulihan Basis Data', 'Memproses skrip SQL basis data...');
                $this->restoreDatabaseFromSql($sqlFile, function (int $pct, string $stg, string $det) use ($report): void {
                    $mappedPct = 35 + (int) round(($pct / 100) * 35);
                    $report($mappedPct, $stg, $det);
                });
                $report(70, 'Pemulihan Basis Data', 'Struktur dan record basis data berhasil diperbarui.');
            }

            // 2. Restore storage files to storage/app/public/ (70% - 88%)
            $extractedStorageDir = $tempExtractDir.DIRECTORY_SEPARATOR.'storage';
            $destinationStorageDir = storage_path('app/public');
            File::ensureDirectoryExists($destinationStorageDir);

            if (File::isDirectory($extractedStorageDir)) {
                $allStorageFiles = File::allFiles($extractedStorageDir);
                $totalStorageFiles = count($allStorageFiles);

                $report(72, 'Sinkronisasi Media', "Menyinkronkan {$totalStorageFiles} berkas aset ke penyimpanan publik...");

                foreach ($allStorageFiles as $idx => $file) {
                    $rel = substr($file->getPathname(), strlen($extractedStorageDir) + 1);
                    $destPath = $destinationStorageDir.DIRECTORY_SEPARATOR.$rel;
                    File::ensureDirectoryExists(dirname($destPath));
                    File::copy($file->getPathname(), $destPath);

                    if ($idx % 10 === 0 || $idx === $totalStorageFiles - 1) {
                        $filePct = 72 + (int) round((($idx + 1) / max(1, $totalStorageFiles)) * 16);
                        $report($filePct, 'Sinkronisasi Media', "Menyalin berkas: {$file->getFilename()} (".($idx + 1)."/{$totalStorageFiles})...");
                    }
                }
            }

            // 3. Check and ensure storage symlink is healthy (88% - 96%)
            $report(90, 'Verifikasi Symlink', 'Memeriksa dan memvalidasi tautan simbolik public/storage...');
            $this->ensureStorageLink();
            $report(95, 'Verifikasi Symlink', 'Tautan simbolik storage telah diverifikasi dan aktif.');

            // 4. Clean up temporary extraction folder
            $report(98, 'Pembersihan', 'Membersihkan berkas sementara ekstraksi...');
            File::deleteDirectory($tempExtractDir);

            $report(100, 'Selesai', 'Seluruh sistem (basis data, aset media, symlink) berhasil dipulihkan!');

            return [
                'type' => 'full',
                'message' => 'Seluruh sistem (basis data dan berkas aset penyimpanan) berhasil dipulihkan, serta symlink storage telah diverifikasi.',
            ];
        } finally {
            if (File::isDirectory($tempExtractDir)) {
                File::deleteDirectory($tempExtractDir);
            }
        }
    }

    /**
     * Ensure the symbolic link from public/storage to storage/app/public is active and healthy.
     * Checks first: if healthy, continues. If broken or missing, recreates it.
     */
    public function ensureStorageLink(): bool
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        File::ensureDirectoryExists($target);

        // 1. Check if symlink exists
        if (is_link($link)) {
            $currentTarget = @readlink($link);
            // If target is already pointing correctly and target exists, it is healthy!
            if ($currentTarget !== false && realpath($currentTarget) === realpath($target) && file_exists($link)) {
                return true;
            }

            // Broken or pointing to obsolete old server path -> unlink
            @unlink($link);
        } elseif (file_exists($link)) {
            // Already exists as regular directory or file
            return true;
        }

        // 2. Create symlink via Artisan or fallback to native symlink
        try {
            Artisan::call('storage:link', ['--relative' => true]);
        } catch (\Throwable) {
            if (! file_exists($link)) {
                @symlink('../storage/app/public', $link);
            }
        }

        return file_exists($link);
    }

    /**
     * Temporarily disable foreign key constraints.
     */
    public function disableForeignKeys(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');

            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    }

    /**
     * Re-enable foreign key constraints.
     */
    public function enableForeignKeys(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');

            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Get system database statistics, storage assets summary, and backup archive stats.
     *
     * @return array{database_name: string, driver: string, total_tables: int, backup_count: int, total_storage_bytes: int, total_storage_human: string, public_storage_bytes: int, public_storage_human: string, public_storage_file_count: int, latest_backup: ?array}
     */
    public function getStatistics(): array
    {
        $tables = $this->getAllTables();
        $backups = $this->listBackups();
        $totalBytes = $backups->sum('size_bytes');

        // Calculate public storage usage
        $publicStoragePath = storage_path('app/public');
        $publicStorageFiles = 0;
        $publicStorageBytes = 0;

        if (File::isDirectory($publicStoragePath)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($publicStoragePath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                if (! $file->isDir()) {
                    $publicStorageFiles++;
                    $publicStorageBytes += (int) $file->getSize();
                }
            }
        }

        return [
            'database_name' => DB::connection()->getDatabaseName() ?? 'infora_db',
            'driver' => DB::connection()->getDriverName(),
            'total_tables' => count($tables),
            'backup_count' => $backups->count(),
            'total_storage_bytes' => $totalBytes,
            'total_storage_human' => $this->formatBytes($totalBytes),
            'public_storage_bytes' => $publicStorageBytes,
            'public_storage_human' => $this->formatBytes($publicStorageBytes),
            'public_storage_file_count' => $publicStorageFiles,
            'latest_backup' => $backups->first(),
        ];
    }

    /**
     * Get all base table names in the active database.
     *
     * @return array<int, string>
     */
    public function getAllTables(): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $rows = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

            return array_values(array_filter(array_map(fn ($row) => ((array) $row)['name'] ?? null, $rows)));
        }

        $rows = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');

        $tables = [];
        foreach ($rows as $row) {
            $rowArray = (array) $row;
            $tableName = array_values($rowArray)[0] ?? null;
            if ($tableName) {
                $tables[] = $tableName;
            }
        }

        return $tables;
    }

    /**
     * Get the CREATE TABLE SQL definition for a table.
     */
    public function getTableCreateSql(string $table): ?string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $result = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$table]);

            return ! empty($result) ? ((array) $result[0])['sql'] : null;
        }

        $createTableResult = DB::select("SHOW CREATE TABLE `{$table}`");
        if (! empty($createTableResult)) {
            $createRow = (array) $createTableResult[0];

            return $createRow['Create Table'] ?? array_values($createRow)[1] ?? null;
        }

        return null;
    }

    /**
     * Format bytes into human readable string.
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($bytes, 1024);
        $pow = min((int) floor($base), count($units) - 1);

        return round(pow(1024, $base - $pow), $precision).' '.$units[$pow];
    }
}
