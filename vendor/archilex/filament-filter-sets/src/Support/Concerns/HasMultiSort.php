<?php

namespace Archilex\AdvancedTables\Support\Concerns;

use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;
use Filament\Support\Enums\IconPosition;

trait HasMultiSort
{
    public static function multiSortIsEnabled(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->multiSortIsEnabled();
        }

        return config('advanced-tables.multi_sort.enabled', true);
    }

    public static function multiSortTablePosition(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->multiSortTablePosition();
        }

        return config('advanced-tables.multi_sort.table_position', 'tables::toolbar.search.after');
    }

    public static function showMultiSortAsButton(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->showMultiSortAsButton();
        }

        return config('advanced-tables.multi_sort.button', false);
    }

    public static function getMultiSortButtonLabel(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getMultiSortButtonLabel();
        }

        return config('advanced-tables.multi_sort.button_label', 'Views');
    }

    public static function getMultiSortButtonSize(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getMultiSortButtonSize();
        }

        return config('advanced-tables.multi_sort.button_size', 'md');
    }

    public static function showMultiSortButtonOutlined(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->showMultiSortButtonOutlined();
        }

        return config('advanced-tables.multi_sort.button_outlined', false);
    }

    public static function getMultiSortIcon(): string
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getMultiSortIcon();
        }

        return config('advanced-tables.multi_sort.icon', 'heroicon-o-queue-list');
    }

    public static function getMultiSortIconPosition(): null | string | IconPosition
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->getMultiSortIconPosition();
        }

        return config('advanced-tables.multi_sort.icon_position', IconPosition::Before);
    }

    public static function hasMultiSortBadge(): bool
    {
        if (self::pluginRegistered()) {
            return AdvancedTablesPlugin::get()->hasMultiSortBadge();
        }

        return config('advanced-tables.multi_sort.badge', true);
    }
}
