<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use RuntimeException;

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
     * Create a fresh database backup SQL dump.
     *
     * @return array{filename: string, path: string, size_bytes: int, size_human: string, created_at: Carbon}
     */
    public function createBackup(): array
    {
        $directory = $this->getBackupDirectory();
        $filename = 'backup_infora_'.Carbon::now()->format('Ymd_His').'.sql';
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
        ];
    }

    /**
     * List all database backups ordered by newest first.
     *
     * @return Collection<int, array{filename: string, path: string, size_bytes: int, size_human: string, created_at: Carbon, timestamp: int}>
     */
    public function listBackups(): Collection
    {
        $directory = $this->getBackupDirectory();
        $files = File::glob($directory.DIRECTORY_SEPARATOR.'*.sql');

        $backups = collect($files)->map(function (string $path) {
            $filename = basename($path);
            $sizeBytes = (int) File::size($path);
            $mtime = File::lastModified($path);

            return [
                'filename' => $filename,
                'path' => $path,
                'size_bytes' => $sizeBytes,
                'size_human' => $this->formatBytes($sizeBytes),
                'created_at' => Carbon::createFromTimestamp($mtime),
                'timestamp' => $mtime,
            ];
        })->sortByDesc('timestamp')->values();

        return $backups;
    }

    /**
     * Get safe sanitized absolute path of a backup file.
     */
    public function getBackupPath(string $filename): ?string
    {
        $sanitized = basename($filename);
        if ($sanitized !== $filename || ! str_ends_with($filename, '.sql')) {
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
     * Restore database from an SQL file.
     */
    public function restoreBackup(string $filePath): void
    {
        if (! File::exists($filePath) || ! File::isReadable($filePath)) {
            throw new RuntimeException('Berkas cadangan tidak ditemukan atau tidak dapat dibaca.');
        }

        $sqlContent = File::get($filePath);

        if (trim($sqlContent) === '') {
            throw new RuntimeException('Berkas cadangan kosong.');
        }

        $this->disableForeignKeys();

        try {
            DB::unprepared($sqlContent);
        } finally {
            $this->enableForeignKeys();
        }
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
     * Get system database statistics and backup summary.
     *
     * @return array{database_name: string, driver: string, total_tables: int, backup_count: int, total_storage_bytes: int, total_storage_human: string, latest_backup: ?array}
     */
    public function getStatistics(): array
    {
        $tables = $this->getAllTables();
        $backups = $this->listBackups();
        $totalBytes = $backups->sum('size_bytes');

        return [
            'database_name' => DB::connection()->getDatabaseName() ?? 'infora_db',
            'driver' => DB::connection()->getDriverName(),
            'total_tables' => count($tables),
            'backup_count' => $backups->count(),
            'total_storage_bytes' => $totalBytes,
            'total_storage_human' => $this->formatBytes($totalBytes),
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
