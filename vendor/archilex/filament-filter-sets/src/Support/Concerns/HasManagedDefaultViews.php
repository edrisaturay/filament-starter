<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Models\ManagedDefaultView;
use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;

trait HasManagedDefaultViews
{
    public static function managedDefaultViewsAreEnabled(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->managedDefaultViewsAreEnabled();
        }

        return config('advanced-tables.managed_default_views.enabled', false);
    }

    public static function getManagedDefaultView(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getManagedDefaultView();
        }

        return config('advanced-tables.managed_default_views.managed_default_view', ManagedDefaultView::class);
    }

    public static function showDefaultViewBadge(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->showDefaultViewBadge();
        }

        return config('advanced-tables.view_manager.show_default_view_badge', false);
    }

    public static function showDefaultViewIcon(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->showDefaultViewIcon();
        }

        return config('advanced-tables.view_manager.show_default_view_icon', true);
    }

    public static function getManagedDefaultViewSetIcon(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getManagedDefaultViewSetIcon();
        }

        return config('advanced-tables.managed_default_views.set_icon', 'heroicon-o-bookmark');
    }

    public static function getManagedDefaultViewRemoveIcon(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getManagedDefaultViewRemoveIcon();
        }

        return config('advanced-tables.managed_default_views.remove_icon', 'heroicon-o-bookmark-slash');
    }
}
