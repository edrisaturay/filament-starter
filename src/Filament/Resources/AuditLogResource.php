<?php

namespace EdrisaTuray\FilamentStarter\Filament\Resources;

use EdrisaTuray\FilamentStarter\Models\AuditLog;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 4;

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('actor_user_id')->label('Actor ID'),
                TextColumn::make('action'),
                TextColumn::make('panel_id'),
                TextColumn::make('plugin_key'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => AuditLogResource\Pages\ListAuditLogs::route('/'),
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
