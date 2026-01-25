<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;

trait HasQuickFilters
{
    public static function quickFiltersAreEnabled(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->quickFiltersAreEnabled();
        }

        return config('advanced-tables.quick_filters.enabled', false);
    }

    public static function getDefaultIndicatorLabelsLimit(): ?int
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getDefaultIndicatorLabelsLimit();
        }

        return config('advanced-tables.quick_filters.default_indicator_labels_limit', null);
    }
}
