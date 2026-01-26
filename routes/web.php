<?php

use Illuminate\Support\Facades\Route;
use EdrisaTuray\FilamentStarter\Http\Controllers\DeveloperGateController;

Route::middleware(['web'])->group(function () {
    Route::get('starter/developer-gate', [DeveloperGateController::class, 'show'])->name('starter.developer-gate.login');
    Route::post('starter/developer-gate', [DeveloperGateController::class, 'login']);
});
