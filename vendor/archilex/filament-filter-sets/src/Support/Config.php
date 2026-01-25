<?php

namespace Archilex\AdvancedTables\Support;

use Archilex\AdvancedTables\Support\Concerns\CanPersistViews;
use Archilex\AdvancedTables\Support\Concerns\CanReorderColumns;
use Archilex\AdvancedTables\Support\Concerns\HasFavoritesBar;
use Archilex\AdvancedTables\Support\Concerns\HasFilterBuilder;
use Archilex\AdvancedTables\Support\Concerns\HasLoadingIndicator;
use Archilex\AdvancedTables\Support\Concerns\HasManagedDefaultViews;
use Archilex\AdvancedTables\Support\Concerns\HasManagedUserViews;
use Archilex\AdvancedTables\Support\Concerns\HasMultiSort;
use Archilex\AdvancedTables\Support\Concerns\HasPresetViews;
use Archilex\AdvancedTables\Support\Concerns\HasQuickFilters;
use Archilex\AdvancedTables\Support\Concerns\HasQuickSave;
use Archilex\AdvancedTables\Support\Concerns\HasResource;
use Archilex\AdvancedTables\Support\Concerns\HasStatus;
use Archilex\AdvancedTables\Support\Concerns\HasSupport;
use Archilex\AdvancedTables\Support\Concerns\HasTenancy;
use Archilex\AdvancedTables\Support\Concerns\HasUsers;
use Archilex\AdvancedTables\Support\Concerns\HasUserViews;
use Archilex\AdvancedTables\Support\Concerns\HasViewManager;
use Exception;

class Config
{
    use CanPersistViews;
    use CanReorderColumns;
    use HasFavoritesBar;
    use HasFilterBuilder;
    use HasLoadingIndicator;
    use HasManagedDefaultViews;
    use HasManagedUserViews;
    use HasMultiSort;
    use HasPresetViews;
    use HasQuickFilters;
    use HasQuickSave;
    use HasResource;
    use HasStatus;
    use HasSupport;
    use HasTenancy;
    use HasUsers;
    use HasUserViews;
    use HasViewManager;

    public static function pluginRegistered(): bool
    {
        if (! class_exists(\Filament\PanelProvider::class)) {
            return false;
        }

        try {
            return filament()->hasPlugin('advanced-tables');
        } catch (Exception $e) {
            return false;
        }
    }
}
