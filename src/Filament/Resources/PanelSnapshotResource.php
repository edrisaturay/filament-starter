<?php

namespace Raison\FilamentStarter\Filament\Resources;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Raison\FilamentStarter\Models\PanelSnapshot;
use Raison\FilamentStarter\Support\PanelSnapshotManager;

class PanelSnapshotResource extends Resource
{
    protected static ?string $model = PanelSnapshot::class;

    protected static ?string $navigationIcon = 'heroicon-o-camera';

    protected static ?string $navigationGroup = 'Platform';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('panel_id')->disabled(),
                KeyValue::make('meta')->disabled(),
                TextInput::make('last_seen_at')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
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
}
