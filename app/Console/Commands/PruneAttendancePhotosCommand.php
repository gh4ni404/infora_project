<?php

namespace App\Console\Commands;

use App\Models\AbsensiMurid;
use App\Support\ImageUploader;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PruneAttendancePhotosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'infora:prune-attendance-photos
                            {--days=180 : Hapus foto snapshot absensi yang lebih lama dari jumlah hari ini}
                            {--dry-run : Simulasikan pembersihan tanpa menghapus berkas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan berkas foto snapshot absensi lama untuk menghemat disk dan inode pada shared hosting.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $dryRun = (bool) $this->option('dry-run');
        $cutoffDate = Carbon::now()->subDays($days)->toDateString();

        $totalDeletedFiles = 0;
        $totalUpdatedRows = 0;

        AbsensiMurid::query()
            ->where('tanggal', '<', $cutoffDate)
            ->where(function ($query): void {
                $query->whereNotNull('foto_masuk_path')
                    ->orWhereNotNull('foto_pulang_path');
            })
            ->orderBy('id')
            ->chunkById(200, function ($records) use ($dryRun, &$totalDeletedFiles, &$totalUpdatedRows): void {
                foreach ($records as $record) {
                    $hasMasuk = ! empty($record->foto_masuk_path);
                    $hasPulang = ! empty($record->foto_pulang_path);

                    if (! $hasMasuk && ! $hasPulang) {
                        continue;
                    }

                    if (! $dryRun) {
                        if ($hasMasuk) {
                            ImageUploader::delete($record->foto_masuk_path);
                        }
                        if ($hasPulang) {
                            ImageUploader::delete($record->foto_pulang_path);
                        }

                        $record->update([
                            'foto_masuk_path' => null,
                            'foto_pulang_path' => null,
                        ]);
                    }

                    $totalDeletedFiles += ($hasMasuk ? 1 : 0) + ($hasPulang ? 1 : 0);
                    $totalUpdatedRows++;
                }
            });

        $modeLabel = $dryRun ? '[DRY RUN] ' : '';
        $this->info("{$modeLabel}Selesai: {$totalDeletedFiles} berkas foto dari {$totalUpdatedRows} catatan absensi sebelum {$cutoffDate} diproses.");

        return self::SUCCESS;
    }
}
