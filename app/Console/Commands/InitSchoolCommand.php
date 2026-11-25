<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Support\AdminAccountManager;
use Illuminate\Console\Command;

class InitSchoolCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'infora:init-school
                            {--name= : Nama resmi sekolah}
                            {--npsn= : NPSN 8 digit angka}
                            {--type=SMK : Jenis sekolah (SMA, SMK, MA)}
                            {--status=Negeri : Status sekolah (Negeri atau Swasta)}
                            {--accreditation=A : Akreditasi sekolah (A, B, C, Belum)}
                            {--force : Sinkronkan ulang akun admin jika profil sekolah sudah ada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inisialisasi profil sekolah tunggal dan akun administrator sekolah untuk mode 1 sekolah = 1 database.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $existingSchool = School::current();

        if ($existingSchool) {
            if (! $this->option('force')) {
                $this->warn("Profil sekolah sudah terdaftar: {$existingSchool->name} (NPSN: {$existingSchool->npsn}).");
                $this->line('Gunakan opsi --force jika ingin menyinkronkan ulang akun administrator sekolah.');

                return self::FAILURE;
            }

            $admin = AdminAccountManager::createOrSyncAdmin($existingSchool);
            $this->info("Akun administrator sekolah {$existingSchool->name} berhasil disinkronkan (Username: {$admin->username}).");

            return self::SUCCESS;
        }

        $name = trim((string) ($this->option('name') ?: $this->ask('Nama resmi sekolah')));
        $npsn = trim((string) ($this->option('npsn') ?: $this->ask('NPSN (8 digit angka)')));
        $schoolType = strtoupper(trim((string) $this->option('type')));
        $status = ucfirst(strtolower(trim((string) $this->option('status'))));
        $accreditation = trim((string) $this->option('accreditation'));

        if ($name === '') {
            $this->error('Nama sekolah wajib diisi.');

            return self::FAILURE;
        }

        if (preg_match('/^\d{8}$/', $npsn) !== 1) {
            $this->error('NPSN harus terdiri tepat 8 digit angka.');

            return self::FAILURE;
        }

        if (! in_array($status, ['Negeri', 'Swasta'], true)) {
            $this->error('Status sekolah harus bernilai Negeri atau Swasta.');

            return self::FAILURE;
        }

        $school = School::create([
            'name' => $name,
            'npsn' => $npsn,
            'school_type' => $schoolType,
            'status' => $status,
            'accreditation' => $accreditation !== '' ? $accreditation : null,
            'is_active' => true,
        ]);

        $admin = AdminAccountManager::createOrSyncAdmin($school);

        $this->info("Profil sekolah berhasil dibuat: {$school->name} (NPSN: {$school->npsn}).");
        $this->info("Akun administrator sekolah aktif -> Username: {$admin->username} | Password awal: {$school->npsn}");

        return self::SUCCESS;
    }
}
