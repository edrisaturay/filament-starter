<?php

use EdrisaTuray\FilamentStarter\Http\Controllers\DeveloperGateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('starter/developer-gate', [DeveloperGateController::class, 'show'])->name('starter.developer-gate.login');
    Route::post('starter/developer-gate', [DeveloperGateController::class, 'login']);
});
