<?php

namespace Raison\FilamentStarter\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Raison\FilamentStarter\Models\AuditLog;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Platform';

    public static function table(Table $table): Table
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
}
