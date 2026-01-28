<?php

namespace EdrisaTuray\FilamentStarter\Concerns;

use Archilex\AdvancedTables\Concerns\HasViews;
use EdrisaTuray\FilamentUtilities\Concerns\CanAccessPanel;
use Filament\Panel;
use Filament\Facades\Filament;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

trait HasStarterUserFeatures
{
    use AuthenticationLoggable;
    use CanAccessPanel;
    use HasApiTokens;
    use HasRoles;
    use HasViews;
    use LogsActivity;
    use Notifiable;
    use TwoFactorAuthenticatable {
        enableTwoFactorAuthentication as private breezyEnableTwoFactorAuthentication;
        getTwoFactorQrCodeUrl as private breezyGetTwoFactorQrCodeUrl;
    }

    /**
     * Getter function for activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    /**
     * Get the Filament avatar URL for the user.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }

    /**
     * Determine if the user can access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Gracefully no-op 2FA when Breezy isn't registered on the current panel.
     */
    public function enableTwoFactorAuthentication(): void
    {
        if (! $this->isBreezyEnabled()) {
            return;
        }

        $this->breezyEnableTwoFactorAuthentication();
    }

    /**
     * @throws \LogicException when Breezy is not available.
     */
    public function getTwoFactorQrCodeUrl(): string
    {
        if (! $this->isBreezyEnabled()) {
            throw new \LogicException('Two-factor authentication is disabled.');
        }

        return $this->breezyGetTwoFactorQrCodeUrl();
    }

    protected function isBreezyEnabled(): bool
    {
        try {
            return Filament::getCurrentOrDefaultPanel()?->hasPlugin('filament-breezy') ?? false;
        } catch (\Throwable) {
            return false;
        }
    }
}
