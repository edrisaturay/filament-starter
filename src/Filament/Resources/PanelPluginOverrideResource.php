<?php

namespace Raison\FilamentStarter\Filament\Resources;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Raison\FilamentStarter\Models\PanelPluginOverride;
use Raison\FilamentStarter\Support\PluginRegistry;

class PanelPluginOverrideResource extends Resource
{
    protected static ?string $model = PanelPluginOverride::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|\UnitEnum|null $navigationGroup = 'Platform';

    public static function form(\Filament\Schemas\Schema $form): \Filament\Schemas\Schema
    {
        /** @var \Filament\Forms\Form $form */
        return $form
            ->schema([
                Select::make('panel_id')
                    ->options(fn () => \Raison\FilamentStarter\Models\PanelSnapshot::pluck('panel_id', 'panel_id'))
                    ->required(),
                Select::make('plugin_key')
                    ->options(collect(PluginRegistry::getPlugins())->mapWithKeys(fn ($v, $k) => [$k => $v['label']]))
                    ->required()
                    ->live(),
                Toggle::make('enabled')
                    ->nullable()
                    ->disabled(fn (callable $get) => PluginRegistry::isDangerous($get('plugin_key')))
                    ->helperText(fn (callable $get) => PluginRegistry::isDangerous($get('plugin_key')) ? 'Dangerous plugins cannot be disabled.' : null),
                Select::make('tenant_id')
                    ->nullable()
                    ->hidden(! config('filament-starter.tenancy.enabled')),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
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

    public static function canEdit($record): bool
    {
        return static::isPlatformPanel() && static::isSuperAdmin();
    }

    public static function canCreate(): bool
    {
        return static::isPlatformPanel() && static::isSuperAdmin();
    }

    public static function canDelete($record): bool
    {
        return static::isPlatformPanel() && static::isSuperAdmin();
    }

    protected static function isPlatformPanel(): bool
    {
        return \Filament\Facades\Filament::getCurrentPanel()?->getId() === 'platform';
    }

    protected static function isSuperAdmin(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        $column = config('filament-starter.superadmin.column', 'is_admin');
        $value = config('filament-starter.superadmin.value', true);

        return $user->{$column} === $value;
    }
}
