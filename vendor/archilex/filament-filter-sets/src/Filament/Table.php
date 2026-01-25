<?php

namespace Archilex\AdvancedTables\Filament;

use Archilex\AdvancedTables\Filters\AdvancedFilter;
use Filament\Tables\Filters\BaseFilter as FilamentBaseFilter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Table as FilamentTable;

class Table extends FilamentTable
{
    /**
     * @return array<string, FilamentBaseFilter>
     */
    public function getFilters(bool $withHidden = false): array
    {
        if ($withHidden) {
            return $this->filters;
        }

        return array_filter(
            $this->filters,
            fn (FilamentBaseFilter $filter): bool => $filter->isVisible()
        );
    }

    /**
     * @return array<string, FilamentBaseFilter>
     */
    public function getFavoriteFilters()
    {
        if (! $this->quickFiltersAreEnabled()) {
            return [];
        }

        $defaultFavoriteFilters = method_exists($this->getLivewire(), 'getDefaultFavoriteFiltersFromCurrentPresetView')
            ? $this->getLivewire()->getDefaultFavoriteFiltersFromCurrentPresetView()
            : null;

        return array_filter(
            $this->getFilters(),
            function (FilamentBaseFilter $filter) use ($defaultFavoriteFilters): bool {
                if (is_array($defaultFavoriteFilters)) {
                    return in_array($filter->getName(), $defaultFavoriteFilters);
                }

                return method_exists($filter, 'isFavorite') && $filter->isFavorite();
            }
        );
    }

    /**
     * @return array<string, FilamentBaseFilter>
     */
    public function getNonFavoriteFilters(bool $excludeQuickFilters = false): array
    {
        if (! $this->quickFiltersAreEnabled()) {
            return $this->getFilters();
        }

        $defaultFavoriteFilters = method_exists($this->getLivewire(), 'getDefaultFavoriteFiltersFromCurrentPresetView')
            ? $this->getLivewire()->getDefaultFavoriteFiltersFromCurrentPresetView()
            : null;

        return array_filter(
            $this->getFilters(),
            function (FilamentBaseFilter $filter) use ($excludeQuickFilters, $defaultFavoriteFilters): bool {
                if ($excludeQuickFilters && $filter instanceof QueryBuilder) {
                    return false;
                }

                if (is_array($defaultFavoriteFilters)) {
                    return ! in_array($filter->getName(), $defaultFavoriteFilters);
                }

                return ! method_exists($filter, 'isFavorite') || (method_exists($filter, 'isFavorite') && ! $filter->isFavorite());
            }
        );
    }

    /**
     * @return array<Indicator>
     */
    public function getFilterIndicators(): array
    {
        if ($this->evaluate($this->areFilterIndicatorsHidden)) {
            return [];
        }

        return [
            ...$this->getFavoriteFilterIndicators(),
            ...($this->hasSearch() ? [$this->getSearchIndicator()] : []),
            ...$this->getColumnSearchIndicators(),
            ...$this->getNonFavoriteFilterIndicators(),
        ];
    }

    /**
     * @return array<Indicator>
     */
    public function getQuickFilterIndicators(): array
    {
        if ($this->evaluate($this->areFilterIndicatorsHidden)) {
            return [];
        }

        return [
            ...$this->getFavoriteFilterIndicators(),
            ...($this->hasSearch() ? [$this->getSearchIndicator()] : []),
            ...$this->getColumnSearchIndicators(),
            ...$this->getNonFavoriteFilterIndicators(excludeQuickFilters: true),
        ];
    }

    /**
     * @return array<Indicator>
     */
    public function getFavoriteFilterIndicators(): array
    {
        return array_reduce(
            $this->getFavoriteFilters(),
            fn (array $carry, FilamentBaseFilter $filter): array => [
                ...$carry,
                ...collect($filter->getFavoriteIndicators())
                    ->map(function (Indicator $indicator) use ($filter): Indicator {
                        $removeField = $indicator->getRemoveField();

                        return $indicator
                            ->filterName($filter->getName())
                            ->quickFilterName($this->getQuickFilterName($filter, $removeField))
                            ->removeLivewireClickHandler($this->getRemoveLivewireClickHandler($filter, $removeField));
                    })
                    ->all(),
            ],
            [],
        );
    }

    /**
     * @return array<Indicator>
     */
    public function getNonFavoriteFilterIndicators(bool $excludeQuickFilters = false): array
    {
        return array_reduce(
            $this->getNonFavoriteFilters($excludeQuickFilters),
            fn (array $carry, FilamentBaseFilter $filter): array => [
                ...$carry,
                ...collect($filter->getIndicators())
                    ->map(function (Indicator $indicator) use ($filter): Indicator {
                        $removeField = $indicator->getRemoveField();

                        return $indicator
                            ->advanced(! ($filter instanceof AdvancedFilter || $filter instanceof QueryBuilder))
                            ->filterName($filter->getName())
                            ->quickFilterName($this->getQuickFilterName($filter, $removeField))
                            ->removeLivewireClickHandler($this->getRemoveLivewireClickHandler($filter, $removeField));
                    })
                    ->all(),
            ],
            [],
        );
    }

    protected function quickFiltersAreEnabled(): bool
    {
        return once(function () {
            $livewire = $this->getLivewire();

            return method_exists($livewire, 'quickFiltersAreEnabled') && $livewire::quickFiltersAreEnabled();
        });
    }

    protected function getRemoveLivewireClickHandler(FilamentBaseFilter $filter, ?string $removeField): string
    {
        return "removeTableFilter('{$filter->getName()}'" . (filled($removeField) ? ', \'' . $removeField . '\'' : null) . ')';
    }

    protected function getQuickFilterName(FilamentBaseFilter $filter, ?string $removeField): string
    {
        return $filter->getName() . (filled($removeField) ? '.' . $removeField : null);
    }
}
