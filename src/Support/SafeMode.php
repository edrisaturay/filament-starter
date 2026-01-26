<?php

namespace EdrisaTuray\FilamentStarter\Support;

use Illuminate\Support\Facades\Cache;

class SafeMode
{
    public static function isActive(): bool
    {
        if (config('app.env') === 'testing') {
            return false;
        }

        return env('STARTER_SAFE_MODE', false) || Cache::has('starter_safe_mode_active');
    }

    public static function activate(): void
    {
        Cache::forever('starter_safe_mode_active', true);
    }

    public static function deactivate(): void
    {
        Cache::forget('starter_safe_mode_active');
    }
}
