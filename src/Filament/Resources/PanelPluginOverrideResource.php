<?php

namespace Raison\FilamentStarter\Filament\Resources;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Raison\FilamentStarter\Models\PanelPluginOverride;
use Raison\FilamentStarter\Models\PanelSnapshot;
use Raison\FilamentStarter\Support\PluginRegistry;

class PanelPluginOverrideResource extends Resource
{
    protected static ?string $model = PanelPluginOverride::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Platform';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('panel_id')
                    ->options(fn () => PanelSnapshot::pluck('panel_id', 'panel_id'))
                    ->required(),
                Select::make('plugin_key')
                    ->options(collect(PluginRegistry::getPlugins())->mapWithKeys(fn ($v, $k) => [$k => $v['label']]))
                    ->required(),
                Toggle::make('enabled')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('panel_id'),
                TextColumn::make('plugin_key'),
                IconColumn::make('enabled')->boolean(),
                TextColumn::make('tenant_id'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => PanelPluginOverrideResource\Pages\ListPanelPluginOverrides::route('/'),
            'create' => PanelPluginOverrideResource\Pages\CreatePanelPluginOverride::route('/create'),
            'edit' => PanelPluginOverrideResource\Pages\EditPanelPluginOverride::route('/{record}/edit'),
        ];
    }

    // Custom model binding because we use DB table
    public static function getModel(): string
    {
        return \Raison\FilamentStarter\Models\PanelPluginOverride::class;
    }
}
