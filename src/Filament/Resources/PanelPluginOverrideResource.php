<?php

namespace EdrisaTuray\FilamentStarter\Filament\Resources;

use EdrisaTuray\FilamentStarter\Models\PanelPluginOverride;
use EdrisaTuray\FilamentStarter\Support\PluginRegistry;
use EdrisaTuray\FilamentStarter\Support\PluginStateResolver;
use EdrisaTuray\FilamentStarter\Support\PluginSyncManager;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class PanelPluginOverrideResource extends Resource
{
    protected static ?string $model = PanelPluginOverride::class;

    protected static ?string $navigationLabel = 'Plugin Management';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|\UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 1;

    public static function form(\Filament\Schemas\Schema $form): \Filament\Schemas\Schema
    {
        /** @var \Filament\Forms\Form $form */
        return $form
            ->schema([
                Select::make('panel_id')
                    ->options(function () {
                        $snapshots = \EdrisaTuray\FilamentStarter\Models\PanelSnapshot::pluck('panel_id', 'panel_id')->toArray();
                        if (empty($snapshots)) {
                            return collect(\Filament\Facades\Filament::getPanels())
                                ->mapWithKeys(fn ($panel) => [$panel->getId() => $panel->getId()])
                                ->toArray();
                        }

                        return $snapshots;
                    })
                    ->required(),
                Select::make('plugin_key')
                    ->options(collect(PluginRegistry::getPlugins())->mapWithKeys(fn ($v, $k) => [$k => $v['label']]))
                    ->required()
                    ->live(),
                Toggle::make('enabled')
                    ->nullable()
                    ->helperText(fn ($record) => $record && $record->is_dangerous ? 'This plugin is marked as dangerous. It cannot be disabled.' : null)
                    ->disabled(fn ($record) => $record && $record->is_dangerous)
                    ->dehydrated(),
                Toggle::make('is_dangerous')
                    ->label('Mark as Dangerous')
                    ->live(),
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
                ToggleColumn::make('enabled')
                    ->disabled(fn ($record) => $record && $record->is_dangerous),
                ToggleColumn::make('is_dangerous')
                    ->label('Dangerous'),
                TextColumn::make('tenant_id'),
            ])
            ->filters([
                SelectFilter::make('panel_id')
                    ->options(function () {
                        $snapshots = \EdrisaTuray\FilamentStarter\Models\PanelSnapshot::pluck('panel_id', 'panel_id')->toArray();
                        if (empty($snapshots)) {
                            return collect(\Filament\Facades\Filament::getPanels())
                                ->mapWithKeys(fn ($panel) => [$panel->getId() => $panel->getId()])
                                ->toArray();
                        }

                        return $snapshots;
                    }),
                SelectFilter::make('plugin_key')
                    ->options(collect(PluginRegistry::getPlugins())->mapWithKeys(fn ($v, $k) => [$k => $v['label']])),
                TernaryFilter::make('enabled'),
                SelectFilter::make('tenant_id')
                    ->options(fn () => PanelPluginOverride::whereNotNull('tenant_id')->distinct()->pluck('tenant_id', 'tenant_id'))
                    ->hidden(! config('filament-starter.tenancy.enabled')),
            ])
            ->headerActions([
                Action::make('sync_plugins')
                    ->label('Sync from Registry')
                    ->action(fn () => PluginSyncManager::sync())
                    ->requiresConfirmation()
                    ->color('info'),
                Action::make('clear_cache')
                    ->label('Clear Plugin Cache')
                    ->action(function () {
                        foreach (\Filament\Facades\Filament::getPanels() as $panelId => $panel) {
                            PluginStateResolver::invalidate($panelId);
                        }
                        \Filament\Notifications\Notification::make()
                            ->title('Plugin cache cleared successfully')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->color('warning'),
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

    public static function canViewAny(): bool
    {
        return static::isSuperAdmin();
    }

    public static function canEdit($record): bool
    {
        return static::isSuperAdmin();
    }

    public static function canCreate(): bool
    {
        return static::isSuperAdmin();
    }

    public static function canDelete($record): bool
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
