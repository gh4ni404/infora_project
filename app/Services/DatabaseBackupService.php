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
     * @return array{filename: string, path: string, size_bytes: int, size_human: string, created_at: Carbon, type: string}
     */
    public function createBackup(string $type = 'full'): array
    {
        if ($type === 'database') {
            return $this->createDatabaseBackup();
        }

        return $this->createFullBackup();
    }

    /**
     * Create a full portable snapshot (.zip) containing database.sql, storage assets, and manifest.json.
     *
     * @return array{filename: string, path: string, size_bytes: int, size_human: string, created_at: Carbon, type: string, files_count: int}
     */
    public function createFullBackup(): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP ZipArchive tidak terpasang di server.');
        }

        $backupDir = $this->getBackupDirectory();
        $timestamp = Carbon::now()->format('Ymd_His');
        $zipFilename = "backup_infora_full_{$timestamp}.zip";
        $zipPath = $backupDir.DIRECTORY_SEPARATOR.$zipFilename;

        // 1. Generate temporary database dump
        $tempSqlFilename = "temp_db_{$timestamp}.sql";
        $dbDump = $this->createDatabaseBackup($tempSqlFilename);
        $tempSqlPath = $dbDump['path'];

        $publicStoragePath = storage_path('app/public');
        File::ensureDirectoryExists($publicStoragePath);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            File::delete($tempSqlPath);
            throw new RuntimeException("Gagal menginisialisasi berkas arsip ZIP di: {$zipPath}");
        }

        try {
            // 2. Add database dump to ZIP root
            $zip->addFile($tempSqlPath, 'database.sql');

            // 3. Collect & add all user uploaded files from storage/app/public/
            $storageFilesCount = 0;
            $storageSizeBytes = 0;

            if (File::isDirectory($publicStoragePath)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($publicStoragePath, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($iterator as $file) {
                    if (! $file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = 'storage/'.substr($filePath, strlen($publicStoragePath) + 1);
                        $zip->addFile($filePath, $relativePath);
                        $storageFilesCount++;
                        $storageSizeBytes += (int) $file->getSize();
                    }
                }
            }

            // 4. Compile manifest.json
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
        } finally {
            $zip->close();
            // Clean up temporary database dump
            File::delete($tempSqlPath);
        }

        $sizeBytes = (int) File::size($zipPath);

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
     * @return array{filename: string, path: string, size_bytes: int, size_human: string, created_at: Carbon, type: string}
     */
    public function createDatabaseBackup(?string $customFilename = null): array
    {
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

            foreach ($tables as $table) {
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
     * @return array{type: string, message: string}
     */
    public function restoreBackup(string $filePath): array
    {
        if (! File::exists($filePath) || ! File::isReadable($filePath)) {
            throw new RuntimeException('Berkas cadangan tidak ditemukan atau tidak dapat dibaca.');
        }

        if (str_ends_with($filePath, '.zip')) {
            return $this->restoreFromZip($filePath);
        }

        $this->restoreDatabaseFromSql($filePath);

        return [
            'type' => 'database',
            'message' => 'Basis data berhasil dipulihkan dari skrip SQL.',
        ];
    }

    /**
     * Restore database from raw SQL file.
     */
    public function restoreDatabaseFromSql(string $filePath): void
    {
        $sqlContent = File::get($filePath);

        if (trim($sqlContent) === '') {
            throw new RuntimeException('Berkas cadangan basis data kosong.');
        }

        $this->disableForeignKeys();

        try {
            DB::unprepared($sqlContent);
        } finally {
            $this->enableForeignKeys();
        }
    }

    /**
     * Restore full snapshot (database + storage public files + symlink auto-link) from a ZIP archive.
     *
     * @return array{type: string, message: string}
     */
    protected function restoreFromZip(string $zipPath): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP ZipArchive tidak terpasang di server.');
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Gagal membuka berkas arsip ZIP cadangan.');
        }

        $tempExtractDir = storage_path('app/temp_restore_'.uniqid());
        File::ensureDirectoryExists($tempExtractDir);

        try {
            $zip->extractTo($tempExtractDir);
            $zip->close();

            // 1. Restore database if database.sql is present
            $sqlFile = $tempExtractDir.DIRECTORY_SEPARATOR.'database.sql';
            if (File::exists($sqlFile)) {
                $this->restoreDatabaseFromSql($sqlFile);
            }

            // 2. Restore storage files to storage/app/public/
            $extractedStorageDir = $tempExtractDir.DIRECTORY_SEPARATOR.'storage';
            $destinationStorageDir = storage_path('app/public');
            File::ensureDirectoryExists($destinationStorageDir);

            if (File::isDirectory($extractedStorageDir)) {
                File::copyDirectory($extractedStorageDir, $destinationStorageDir);
            }

            // 3. Check and ensure storage symlink is healthy
            $this->ensureStorageLink();

            return [
                'type' => 'full',
                'message' => 'Seluruh sistem (basis data dan berkas aset penyimpanan) berhasil dipulihkan, serta symlink storage telah diverifikasi.',
            ];
        } finally {
            File::deleteDirectory($tempExtractDir);
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
            Artisan::call('storage:link');
        } catch (\Throwable) {
            if (! file_exists($link)) {
                @symlink($target, $link);
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
