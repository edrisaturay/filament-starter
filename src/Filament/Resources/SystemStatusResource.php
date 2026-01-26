<?php

namespace EdrisaTuray\FilamentStarter\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class SystemStatusResource extends Resource
{
    protected static ?string $model = \EdrisaTuray\FilamentStarter\Models\AuditLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-heart';

    protected static string|\UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 3;

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('check'),
                IconColumn::make('status')
                    ->icon(fn (string $state): string => match ($state) {
                        'ok' => 'heroicon-o-check-circle',
                        'warning' => 'heroicon-o-exclamation-triangle',
                        'critical' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'ok' => 'success',
                        'warning' => 'warning',
                        'critical' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('message'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => SystemStatusResource\Pages\ListSystemStatuses::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return static::isSuperAdmin();
    }

    protected static function isSuperAdmin(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        $role = config('filament-starter.superadmin.role', 'super_admin');

        return method_exists($user, 'hasRole') && $user->hasRole($role);
    }
}
