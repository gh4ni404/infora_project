<?php

use App\Http\Controllers\Api\Master\HeartbeatController;
use App\Http\Middleware\VerifyTenantNodeSignature;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/master')
    ->name('api.master.')
    ->middleware([VerifyTenantNodeSignature::class])
    ->group(function () {
        Route::post('/heartbeat', [HeartbeatController::class, 'store'])->name('heartbeat');
    });
