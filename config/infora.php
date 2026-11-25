<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Peran Node Aplikasi (Dual-Mode Single Codebase)
    |--------------------------------------------------------------------------
    |
    | Nilai yang didukung:
    | - 'school' : Node sekolah mandiri (1 sekolah = 1 database) yang mengirim heartbeat.
    | - 'master' : Portal pusat Super Admin untuk memantau node sekolah & izin menu.
    |
    */
    'node_role' => env('INFORA_NODE_ROLE', 'school'),

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Client Sekolah -> Portal Master
    |--------------------------------------------------------------------------
    */
    'master_url' => rtrim((string) env('INFORA_MASTER_URL', ''), '/'),
    'node_code' => env('INFORA_NODE_CODE', ''),
    'node_token' => env('INFORA_NODE_TOKEN', ''),
    'node_secret' => env('INFORA_NODE_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Pengaturan Heartbeat & Batas Deteksi Status
    |--------------------------------------------------------------------------
    */
    'heartbeat' => [
        'interval_minutes' => max(1, (int) env('INFORA_HEARTBEAT_INTERVAL_MINUTES', 2)),
        'timeout_seconds' => max(2, (int) env('INFORA_HEARTBEAT_TIMEOUT_SECONDS', 8)),
        'idle_minutes' => max(1, (int) env('INFORA_HEARTBEAT_IDLE_MINUTES', 15)),
        'offline_minutes' => max(2, (int) env('INFORA_HEARTBEAT_OFFLINE_MINUTES', 6)),
        'clock_skew_seconds' => max(30, (int) env('INFORA_HEARTBEAT_CLOCK_SKEW_SECONDS', 300)),
        'user_activity_throttle_minutes' => 5,
    ],

];
