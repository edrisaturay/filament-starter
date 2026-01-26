<?php

namespace EdrisaTuray\FilamentStarter\Support;

use Cheesegrits\FilamentPhoneNumbers\Columns\PhoneNumberColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class TableHelper
{
    /**
     * Create a standard text column.
     */
    public static function textColumn(
        string $name,
        ?string $label = null,
        bool $sortable = true,
        bool $searchable = true,
        bool $toggleable = true,
        ?string $description = null,
        ?string $tooltip = null,
    ): TextColumn {
        return TextColumn::make($name)
            ->label($label)
            ->sortable($sortable)
            ->searchable($searchable)
            ->toggleable($toggleable)
            ->description($description)
            ->tooltip($tooltip);
    }

    /**
     * Create a numeric/money column.
     */
    public static function moneyColumn(
        string $name,
        ?string $label = null,
        string $currency = 'USD',
        bool $sortable = true,
    ): TextColumn {
        return self::textColumn($name, $label, $sortable, false)
            ->money($currency);
    }

    /**
     * Create a date/time column.
     */
    public static function dateColumn(
        string $name,
        ?string $label = null,
        ?string $format = null,
        bool $sortable = true,
    ): TextColumn {
        return self::textColumn($name, $label, $sortable, false)
            ->dateTime($format);
    }

    /**
     * Create a boolean icon column.
     */
    public static function booleanColumn(
        string $name,
        ?string $label = null,
        bool $sortable = true,
    ): IconColumn {
        return IconColumn::make($name)
            ->label($label)
            ->boolean()
            ->sortable($sortable);
    }

    /**
     * Create an image column.
     */
    public static function imageColumn(
        string $name,
        ?string $label = null,
        bool $circular = false,
        int|string|null $width = null,
        int|string|null $height = null,
    ): ImageColumn {
        return ImageColumn::make($name)
            ->label($label)
            ->circular($circular)
            ->width($width)
            ->height($height);
    }

    /**
     * Create an icon column with custom icons/colors.
     */
    public static function iconColumn(
        string $name,
        array $icons = [],
        ?string $label = null,
    ): IconColumn {
        return IconColumn::make($name)
            ->label($label)
            ->options($icons);
    }

    /**
     * Create a phone number column.
     */
    public static function phoneColumn(
        string $name,
        ?string $label = null,
        bool $sortable = true,
        bool $searchable = true,
        bool $displayNumberFormat = true,
    ): PhoneNumberColumn {
        return PhoneNumberColumn::make($name)
            ->label($label)
            ->sortable($sortable)
            ->searchable($searchable)
            ->displayNumberFormat($displayNumberFormat);
    }

    /**
     * Create a toggleable column (interactive in table).
     */
    public static function toggleColumn(
        string $name,
        ?string $label = null,
    ): ToggleColumn {
        return ToggleColumn::make($name)
            ->label($label);
    }
}
