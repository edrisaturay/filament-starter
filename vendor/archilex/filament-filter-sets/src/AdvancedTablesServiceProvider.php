<?php

namespace Archilex\AdvancedTables;

use Archilex\AdvancedTables\Commands\AddTenancyCommand;
use Archilex\AdvancedTables\Filament\BaseFilter;
use Archilex\AdvancedTables\Filament\Filter;
use Archilex\AdvancedTables\Filament\Indicator;
use Archilex\AdvancedTables\Filament\SelectFilter;
use Archilex\AdvancedTables\Filament\Table;
use Archilex\AdvancedTables\Filament\TernaryFilter;
use Archilex\AdvancedTables\Filament\TrashedFilter;
use Archilex\AdvancedTables\Support\Config;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter as FilamentBaseFilter;
use Filament\Tables\Filters\Filter as FilamentFilter;
use Filament\Tables\Filters\Indicator as FilamentIndicator;
use Filament\Tables\Filters\SelectFilter as FilamentSelectFilter;
use Filament\Tables\Filters\TernaryFilter as FilamentTernaryFilter;
use Filament\Tables\Filters\TrashedFilter as FilamentTrashedFilter;
use Filament\Tables\Table as FilamentTable;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AdvancedTablesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('advanced-tables')
            ->hasCommands([
                AddTenancyCommand::class,
            ])
            ->hasConfigFile('advanced-tables')
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations([
                'create_filament_filter_sets_table',
                'create_filament_filter_set_user_table',
                'add_icon_and_color_columns_to_filter_sets_table',
                'add_is_visible_column_to_filter_set_users_table',
                'create_filament_filter_sets_managed_preset_views_table',
                'add_status_column_to_filter_sets_table',
                'change_filter_json_column_type_to_text_type',
                'add_tenant_id_to_filter_sets_table',
                'add_tenant_id_to_managed_preset_views_table',
                'create_filament_filter_sets_managed_default_views_table',
                'migrate_toggled_columns_to_v4',
                'migrate_v4_table_columns_to_new_structure',
                'migrate_table_sort_to_v4',
                'migrate_table_grouping_to_v4',
            ]);
    }

    public function packageRegistered(): void
    {
        app()->bind(FilamentTable::class, Table::class);
        app()->bind(FilamentBaseFilter::class, BaseFilter::class);
        app()->bind(FilamentFilter::class, Filter::class);
        app()->bind(FilamentSelectFilter::class, SelectFilter::class);
        app()->bind(FilamentTernaryFilter::class, TernaryFilter::class);
        app()->bind(FilamentTrashedFilter::class, TrashedFilter::class);
        app()->bind(FilamentIndicator::class, Indicator::class);

        $this->app->booted(function () {
            if (Config::tableHasLoadingOverlay()) {
                Column::configureUsing(function (Column $column) {
                    return $column->extraCellAttributes([
                        'wire:loading.class' => 'advanced-tables-table-loading-overlay',
                        'wire:target' => 'activeTab, applyTableFilters, gotoPage, loadDefaultView, loadPresetView, loadUserView, nextPage, previousPage, quickFilters, removeTableFilter, removeTableFilters, resetTableFiltersForm, resetTableSort, resetTableToDefault, sortTable, tableColumnSearches, tableFilters, tableGrouping, tableRecordsPerPage, tableSearch, tableColumns',
                    ]);
                });
            }
        });
    }
}
