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
                            {--type=SMK : Jenis sekolah (SMA, SMK, MA)}
                            {--status=Negeri : Status sekolah (Negeri atau Swasta)}
                            {--accreditation=A : Akreditasi sekolah (A, B, C, Belum)}
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

        // 1. Verifikasi koneksi basis data sebelum eksekusi apa pun
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->error('Gagal terhubung ke basis data: '.$e->getMessage());
            $this->line('Periksa konfigurasi DB_CONNECTION, DB_HOST, DB_DATABASE, DB_USERNAME, dan DB_PASSWORD pada berkas .env.');

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
        $initArgs = [
            '--type' => $this->option('type'),
            '--status' => $this->option('status'),
            '--accreditation' => $this->option('accreditation'),
        ];

        if ($this->filledOption('name')) {
            $initArgs['--name'] = $this->option('name');
        }
        if ($this->filledOption('npsn')) {
            $initArgs['--npsn'] = $this->option('npsn');
        }
        if ($this->option('force')) {
            $initArgs['--force'] = true;
        }

        $initStatus = $this->call('infora:init-school', $initArgs);
        if ($initStatus !== self::SUCCESS) {
            return self::FAILURE;
        }

        // 9. Optimasi cache untuk produksi
        if (! $this->option('skip-optimize') && ! app()->runningUnitTests()) {
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
}
