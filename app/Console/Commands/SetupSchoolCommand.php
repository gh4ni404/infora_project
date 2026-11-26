<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\DatabaseBackupService;
use Database\Seeders\KabupatenSeeder;
use Database\Seeders\KecamatanSeeder;
use Database\Seeders\KelurahanSeeder;
use Database\Seeders\MenuAccessTemplateSeeder;
use Database\Seeders\ProvinsiSeeder;
use Database\Seeders\SuperAdminSeeder;
use Database\Seeders\SystemMenuSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetupSchoolCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'infora:setup-school
                            {--name= : Nama resmi sekolah}
                            {--npsn= : NPSN 8 digit angka}
                            {--type= : Jenis sekolah (SMA, SMK, MA)}
                            {--status= : Status sekolah (Negeri atau Swasta)}
                            {--accreditation= : Akreditasi sekolah (A, B, C, Belum)}
                            {--without-wilayah : Lewati seeding master wilayah Kemendagri agar setup lebih ringan}
                            {--skip-optimize : Lewati caching konfigurasi dan rute (artisan optimize)}
                            {--force : Tetap jalankan migrasi, seeder, dan sinkronisasi admin bila sekolah sudah ada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup instalasi baru INFORA (migrasi, seeder, storage link, profil sekolah, dan akun admin) dalam satu perintah.';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $this->info('Memulai setup instalasi INFORA (Mode 1 Sekolah = 1 Database)...');

        // 1. Verifikasi koneksi basis data (atau minta input interaktif jika belum terkonfigurasi)
        if (! $this->ensureDatabaseConnection()) {
            return self::FAILURE;
        }

        // 2. Pastikan APP_KEY tersedia
        if (empty(config('app.key')) && ! app()->runningUnitTests()) {
            $this->info('Menghasilkan APP_KEY aplikasi...');
            $this->call('key:generate', ['--force' => true]);
        }

        // 3. Cegah penimpaan jika profil sekolah sudah ada (kecuali --force)
        if (Schema::hasTable('schools')) {
            $existingSchool = School::current();
            if ($existingSchool && ! $this->option('force')) {
                $this->warn("Instalasi ini sudah memiliki profil sekolah: {$existingSchool->name} (NPSN: {$existingSchool->npsn}).");
                $this->line('Gunakan --force jika ingin menjalankan ulang migrasi, seeder dasar, dan sinkronisasi akun admin.');

                return self::FAILURE;
            }
        }

        // 4. Jalankan migrasi skema basis data
        $this->info('Menjalankan migrasi basis data...');
        $this->call('migrate', ['--force' => true]);

        // 5. Jalankan seeder inti sistem
        $coreSeeders = [
            SuperAdminSeeder::class,
            SystemMenuSeeder::class,
            MenuAccessTemplateSeeder::class,
        ];

        $this->info('Mengisi data inti sistem (Super Admin, Menu Navigasi, Template Peran)...');
        foreach ($coreSeeders as $seederClass) {
            $this->call('db:seed', [
                '--class' => $seederClass,
                '--force' => true,
            ]);
        }

        // 6. Jalankan seeder wilayah Kemendagri (opsional dilewati via --without-wilayah)
        if (! $this->option('without-wilayah')) {
            $this->info('Mengisi data master wilayah Kemendagri...');
            foreach ([
                ProvinsiSeeder::class,
                KabupatenSeeder::class,
                KecamatanSeeder::class,
                KelurahanSeeder::class,
            ] as $wilayahSeeder) {
                $this->call('db:seed', [
                    '--class' => $wilayahSeeder,
                    '--force' => true,
                ]);
            }
        } else {
            $this->line('Melewati seeding master wilayah Kemendagri (--without-wilayah).');
        }

        // 7. Pastikan symlink storage publik sehat
        if (! app()->runningUnitTests()) {
            $this->info('Memverifikasi tautan simbolik public/storage...');
            $backupService->ensureStorageLink();
        }

        // 8. Inisialisasi profil sekolah & akun admin sekolah
        $initArgs = [];

        foreach (['name', 'npsn', 'type', 'status', 'accreditation'] as $opt) {
            if ($this->filledOption($opt)) {
                $initArgs["--{$opt}"] = $this->option($opt);
            }
        }
        if ($this->option('force')) {
            $initArgs['--force'] = true;
        }

        $initStatus = $this->call('infora:init-school', $initArgs);
        if ($initStatus !== self::SUCCESS) {
            return self::FAILURE;
        }

        // 9. Optimasi cache hanya untuk produksi (agar tidak mengunci config di local/testing)
        if (! $this->option('skip-optimize') && app()->isProduction()) {
            $this->info('Mengoptimasi cache konfigurasi, rute, dan view...');
            $this->call('optimize');
        }

        $this->newLine();
        $this->info('Setup sekolah baru selesai! Aplikasi siap digunakan di: '.config('app.url'));

        return self::SUCCESS;
    }

    /**
     * Periksa apakah opsi string terisi dan tidak kosong.
     */
    protected function filledOption(string $key): bool
    {
        $value = $this->option($key);

        return is_string($value) && trim($value) !== '';
    }

    /**
     * Pastikan koneksi basis data aktif; jika belum valid, minta detail DB secara interaktif.
     */
    protected function ensureDatabaseConnection(): bool
    {
        if ($this->canConnectToDatabase()) {
            return true;
        }

        if (! $this->input->isInteractive() || $this->option('no-interaction')) {
            $this->error('Gagal terhubung ke basis data. Periksa konfigurasi DB_* pada berkas .env.');

            return false;
        }

        $this->warn('Koneksi basis data belum terkonfigurasi atau gagal terhubung.');
        $this->line('Silakan masukkan detail database eksternal (cPanel / VPS) Anda:');

        while (true) {
            $currentConn = (string) config('database.default', 'mysql');
            $defaultConn = in_array($currentConn, ['mysql', 'mariadb'], true) ? $currentConn : 'mysql';
            $connection = (string) $this->choice('Driver database', ['mysql', 'mariadb'], $defaultConn);

            $currentHost = (string) config("database.connections.{$connection}.host", '');
            $defaultHost = ($currentHost !== '' && $currentHost !== 'IP_PUBLIC_CPANEL') ? $currentHost : null;

            $host = trim((string) $this->ask('Host / IP Database (cPanel / VPS)', $defaultHost));
            $port = trim((string) $this->ask('Port Database', (string) config("database.connections.{$connection}.port", '3306')));
            $database = trim((string) $this->ask('Nama Database'));
            $username = trim((string) $this->ask('Username Database'));
            $password = (string) ($this->secret('Password Database (kosongkan jika tidak ada)') ?? '');

            $this->applyDatabaseConfig($connection, $host, $port, $database, $username, $password);

            try {
                DB::connection($connection)->getPdo();
                $this->info('Koneksi database berhasil!');
                $this->writeEnvValues([
                    'DB_CONNECTION' => $connection,
                    'DB_HOST' => $host,
                    'DB_PORT' => $port,
                    'DB_DATABASE' => $database,
                    'DB_USERNAME' => $username,
                    'DB_PASSWORD' => $password,
                ]);

                return true;
            } catch (\Throwable $e) {
                $this->error('Gagal terhubung ke database: '.$e->getMessage());

                if (! $this->confirm('Ingin mencoba memasukkan ulang detail database?', true)) {
                    return false;
                }
            }
        }
    }

    /**
     * Periksa apakah koneksi database default saat ini dapat terhubung.
     */
    protected function canConnectToDatabase(): bool
    {
        $defaultConn = (string) config('database.default');
        if (in_array($defaultConn, ['mysql', 'mariadb'], true) && empty(config("database.connections.{$defaultConn}.database"))) {
            return false;
        }

        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Terapkan konfigurasi database baru ke runtime Laravel saat ini.
     */
    protected function applyDatabaseConfig(string $connection, string $host, string $port, string $database, string $username, string $password): void
    {
        config()->set('database.default', $connection);
        config()->set("database.connections.{$connection}.host", $host);
        config()->set("database.connections.{$connection}.port", $port);
        config()->set("database.connections.{$connection}.database", $database);
        config()->set("database.connections.{$connection}.username", $username);
        config()->set("database.connections.{$connection}.password", $password);

        DB::purge($connection);
        DB::setDefaultConnection($connection);
    }

    /**
     * Simpan pasangan key-value konfigurasi ke file .env aplikasi.
     *
     * @param  array<string, string>  $values
     */
    protected function writeEnvValues(array $values): void
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            return;
        }

        $content = (string) file_get_contents($envPath);

        foreach ($values as $key => $value) {
            $formatted = $this->formatEnvValue($value);
            $pattern = "/^{$key}=.*$/m";

            if (preg_match($pattern, $content) === 1) {
                $content = (string) preg_replace($pattern, "{$key}={$formatted}", $content);
            } else {
                $content = rtrim($content)."\n{$key}={$formatted}\n";
            }
        }

        file_put_contents($envPath, $content);
    }

    /**
     * Format nilai environment agar aman ditulis ke file .env.
     */
    protected function formatEnvValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('/[\s#"\'\\\\]/', $value) === 1) {
            return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
        }

        return $value;
    }
}
