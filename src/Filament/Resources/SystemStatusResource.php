<?php

namespace Raison\FilamentStarter\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SystemStatusResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Platform';

    public static function table(Table $table): Table
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
}
