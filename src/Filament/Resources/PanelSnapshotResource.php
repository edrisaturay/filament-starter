<?php

namespace EdrisaTuray\FilamentStarter\Filament\Resources;

use EdrisaTuray\FilamentStarter\Models\PanelSnapshot;
use EdrisaTuray\FilamentStarter\Support\PanelSnapshotManager;
use Filament\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;

class PanelSnapshotResource extends Resource
{
    protected static ?string $model = PanelSnapshot::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-camera';

    protected static string|\UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 2;

    public static function form(\Filament\Schemas\Schema $form): \Filament\Schemas\Schema
    {
        /** @var \Filament\Forms\Form $form */
        return $form
            ->schema([
                TextInput::make('panel_id')->disabled(),
                KeyValue::make('meta')->disabled(),
                TextInput::make('last_seen_at')->disabled(),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('panel_id'),
                TextColumn::make('meta.path')->label('Path'),
                TextColumn::make('last_seen_at')->dateTime(),
            ])
            ->headerActions([
                Action::make('refresh_snapshots')
                    ->action(fn () => PanelSnapshotManager::snapshot())
                    ->requiresConfirmation(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => PanelSnapshotResource\Pages\ListPanelSnapshots::route('/'),
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
