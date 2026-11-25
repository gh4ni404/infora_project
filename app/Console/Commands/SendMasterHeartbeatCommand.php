<?php

namespace App\Console\Commands;

use App\Support\MasterHeartbeatClient;
use Illuminate\Console\Command;

class SendMasterHeartbeatCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'infora:heartbeat {--maintenance : Tandai node dalam status maintenance}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim heartbeat status server/database dan sinkronisasi hak menu dengan Portal Master';

    /**
     * Execute the console command.
     */
    public function handle(MasterHeartbeatClient $client): int
    {
        $maintenance = $this->option('maintenance') ? true : null;
        $result = $client->send($maintenance);

        if ($result['ok']) {
            $this->info($result['message']);

            return self::SUCCESS;
        }

        $this->warn($result['message']);

        return self::FAILURE;
    }
}
