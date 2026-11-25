<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$interval = min(59, max(1, (int) config('infora.heartbeat.interval_minutes', 2)));

Schedule::command('infora:heartbeat')
    ->cron("*/{$interval} * * * *")
    ->when(fn () => config('infora.node_role') === 'school'
        && filled(config('infora.master_url'))
        && filled(config('infora.node_code')))
    ->withoutOverlapping();
