<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;

trait HasLoadingIndicator
{
    public static function favoritesBarHasLoadingIndicator(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->favoritesBarHasLoadingIndicator();
        }

        return config('advanced-tables.loading_indicator.favorites_bar_loading_indicator') ?? config('advanced-tables.favorites_bar.loading_indicator', false);
    }

    public static function tableHasLoadingOverlay(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->tableHasLoadingOverlay();
        }

        return config('advanced-tables.loading_indicator.table_loading_overlay', false);
    }
}
