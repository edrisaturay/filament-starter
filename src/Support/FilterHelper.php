<?php

namespace EdrisaTuray\FilamentStarter\Support;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

class FilterHelper
{
    /**
     * Create a standard select filter.
     */
    public static function selectFilter(
        string $name,
        array|\Closure|null $options = null,
        ?string $label = null,
        bool $multiple = false,
        bool $searchable = true,
        bool $preload = true,
        ?string $relationship = null,
        ?string $titleAttribute = null,
    ): SelectFilter {
        $filter = SelectFilter::make($name)
            ->label($label)
            ->multiple($multiple)
            ->searchable($searchable)
            ->preload($preload);

        if ($options) {
            $filter->options($options);
        }

        if ($relationship) {
            $filter->relationship($relationship, $titleAttribute);
        }

        return $filter;
    }

    /**
     * Create a ternary filter (True/False/All).
     */
    public static function ternaryFilter(
        string $name,
        ?string $label = null,
        ?string $placeholder = 'All',
    ): TernaryFilter {
        return TernaryFilter::make($name)
            ->label($label)
            ->placeholder($placeholder);
    }

    /**
     * Create a date range filter.
     */
    public static function dateRangeFilter(
        string $name,
        ?string $label = null,
        string $column = 'created_at',
    ): Filter {
        return Filter::make($name)
            ->label($label)
            ->form([
                DatePicker::make('from')->label('From'),
                DatePicker::make('until')->label('Until'),
            ])
            ->query(function (Builder $query, array $data) use ($column): Builder {
                return $query
                    ->when(
                        $data['from'],
                        fn (Builder $query, $date): Builder => $query->whereDate($column, '>=', $date),
                    )
                    ->when(
                        $data['until'],
                        fn (Builder $query, $date): Builder => $query->whereDate($column, '<=', $date),
                    );
            });
    }

    /**
     * Create a simple boolean filter using a toggle.
     */
    public static function toggleFilter(
        string $name,
        ?string $label = null,
        ?string $column = null,
    ): Filter {
        $column = $column ?? $name;

        return Filter::make($name)
            ->label($label)
            ->query(fn (Builder $query): Builder => $query->where($column, true));
    }
}
