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
                            {--type= : Jenis sekolah (SMA, SMK, MA)}
                            {--status= : Status sekolah (Negeri atau Swasta)}
                            {--accreditation= : Akreditasi sekolah (A, B, C, Belum)}
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
        $isInteractive = $this->input->isInteractive() && ! $this->option('no-interaction') && ! app()->runningUnitTests();

        $inferredType = $this->inferSchoolType($name);
        $rawType = $this->option('type');

        if ($rawType !== null && $rawType !== '') {
            $schoolType = strtoupper(trim((string) $rawType));
        } elseif ($inferredType !== null) {
            $schoolType = $inferredType;
            $this->line("Jenis sekolah terdeteksi otomatis dari nama: {$schoolType}");
        } else {
            $typeOptions = ['SD', 'SMP', 'SMA', 'SMK', 'MI', 'MTS', 'MA'];
            $schoolType = strtoupper(trim((string) ($isInteractive
                ? $this->choice('Jenis sekolah', $typeOptions, 'SMA')
                : 'SMA')));
        }

        $inferredStatus = str_contains(strtoupper($name), 'SWASTA') ? 'Swasta' : 'Negeri';
        $rawStatus = $this->option('status');
        $status = ucfirst(strtolower(trim((string) ($rawStatus !== null && $rawStatus !== ''
            ? $rawStatus
            : ($isInteractive ? $this->choice('Status sekolah', ['Negeri', 'Swasta'], $inferredStatus) : $inferredStatus)))));

        $rawAccreditation = $this->option('accreditation');
        $accreditation = trim((string) ($rawAccreditation !== null && $rawAccreditation !== ''
            ? $rawAccreditation
            : ($isInteractive ? $this->choice('Akreditasi sekolah', ['A', 'B', 'C', 'Belum'], 'A') : 'A')));

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

    /**
     * Deteksi otomatis jenis sekolah dari nama sekolah (SD, SMP, SMA, SMK, MI, MTS, MA).
     */
    protected function inferSchoolType(string $name): ?string
    {
        $upper = strtoupper($name);

        $patterns = [
            'SMK' => '/\b(?:SMK|SMKN|SMKS)\b/',
            'SMA' => '/\b(?:SMA|SMAN|SMAS)\b/',
            'SMP' => '/\b(?:SMP|SMPN|SMPS)\b/',
            'SD' => '/\b(?:SD|SDN|SDS)\b/',
            'MTS' => '/\b(?:MTS|MTSN|MTSS)\b|\bMADRASAH\s+TSANAWIYAH\b/',
            'MI' => '/\b(?:MI|MIN|MIS)\b|\bMADRASAH\s+IBTIDAIYAH\b/',
            'MA' => '/\b(?:MA|MAN|MAS)\b|\bMADRASAH\s+ALIYAH\b/',
        ];

        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $upper) === 1) {
                return $type;
            }
        }

        return null;
    }
}
