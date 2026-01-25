<?php

namespace Archilex\AdvancedTables\Concerns;

use Archilex\AdvancedTables\Components\PresetView;
use Archilex\AdvancedTables\Filament\BaseFilter;
use Archilex\AdvancedTables\Filament\Filter;
use Archilex\AdvancedTables\Filament\SelectFilter;
use Archilex\AdvancedTables\Filament\TernaryFilter;
use Archilex\AdvancedTables\Filament\TrashedFilter;
use Archilex\AdvancedTables\Filters\AdvancedFilter;
use Archilex\AdvancedTables\Support\Config;
use Exception;
use Filament\Actions\Action;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Filters\BaseFilter as FilamentBaseFilter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\QueryBuilder;
use Illuminate\Support\Collection;

trait HasQuickFilters
{
    public ?array $quickFilters = null;

    public function cacheQuickFiltersForms(): void
    {
        if (! static::quickFiltersAreEnabled()) {
            return;
        }

        $filterIndicators = $this->getTable()->getQuickFilterIndicators();

        if (blank($filterIndicators)) {
            return;
        }

        foreach ($filterIndicators as $filterIndicator) {
            $filterName = $filterIndicator->getFilterName();

            if (blank($filterName)) {
                continue;
            }

            $tableFilter = $this->getTable()->getFilter($filterName);
            $fields = $tableFilter?->getSchemaComponents();

            if (! $fields) {
                continue;
            }

            if ($tableFilter instanceof AdvancedFilter || $tableFilter instanceof QueryBuilder) {
                continue;
            }

            if (
                ! $tableFilter instanceof BaseFilter &&
                ! $tableFilter instanceof Filter &&
                ! $tableFilter instanceof SelectFilter &&
                ! $tableFilter instanceof TernaryFilter &&
                ! $tableFilter instanceof TrashedFilter
            ) {
                throw new Exception('When Quick Filters are enabled, custom filter classes must extend one of Advanced Table\'s filter classes. Please update the ' . get_class($tableFilter) . ' class to extend Archilex\AdvancedTables\Filament\BaseFilter, Archilex\AdvancedTables\Filament\Filter, Archilex\AdvancedTables\Filament\SelectFilter, Archilex\AdvancedTables\Filament\TernaryFilter, or Archilex\AdvancedTables\Filament\TrashedFilter');
            }

            $indicatorName = $filterIndicator->getQuickFilterName();

            $this->cacheSchema('quickFilterForm_' . $indicatorName, $this->getQuickFilterForm($indicatorName));
        }
    }

    public function getQuickFilterForm(string | Indicator | null $indicator = null): Schema
    {
        $filter = match (true) {
            $indicator instanceof Indicator => $indicator->getQuickFilterName(),
            is_string($indicator) => $indicator,
            default => null,
        };

        if ((! $this->isCachingSchemas) && $this->hasCachedSchema('quickFilterForm_' . $filter)) {
            return $this->getSchema('quickFilterForm_' . $filter);
        }

        $schema = $this->getQuickFilterFormSchema($indicator, $filter);

        return $this->makeSchema()
            ->schema($schema)
            ->model($this->getTable()->getModel())
            ->statePath('quickFilters')
            ->live();
        // ->when(! $this->getTable()->hasDeferredFilters(), fn (Form $form) => $form->live());
    }

    public function applyQuickFilters(bool $forDeferredFilters = false, bool $andFill = true, bool $andApplyFavorites = true): void
    {
        if (! static::quickFiltersAreEnabled()) {
            return;
        }

        $filterIndicators = $this->getTable()->getQuickFilterIndicators();

        if (blank($filterIndicators)) {
            return;
        }

        $quickFilters = [];

        foreach ($filterIndicators as $filterIndicator) {
            $filterName = $filterIndicator->getFilterName();

            $tableFilters = $forDeferredFilters
                ? $this->tableDeferredFilters
                : $this->tableFilters;

            $quickFilters[$filterName] = $tableFilters[$filterName] ?? null;
        }

        if ($andFill) {
            $this->quickFilters = $quickFilters;

            $this->getQuickFilterForm()->fill($quickFilters);
        }

        if ($andApplyFavorites) {
            $this->applyFavoriteQuickFilters();
        }
    }

    public function applyFavoriteQuickFilters(): void
    {
        // Feature Under Development: Allow users to favorite filters
        // When complete, users will be able to favorite filters in the filters dropdown and
        // then save that configuration as a User View.
        // Preset view also need to be supported which is what this current code is doing.
        return;

        if (! static::quickFiltersAreEnabled()) {
            return;
        }

        $currentPresetView = $this->getCurrentPresetView();

        if (! $currentPresetView instanceof PresetView) {
            return;
        }

        $defaultFavoriteFilters = $currentPresetView->getDefaultFavoriteFilters();

        if (blank($defaultFavoriteFilters)) {
            return;
        }

        $filters = collect($this->getTable()->getFilters())
            ->filter(fn ($filter) => $filter instanceof FilamentBaseFilter)
            ->reject(fn ($filter) => $filter instanceof AdvancedFilter)
            ->reject(fn ($filter) => $filter instanceof QueryBuilder)
            ->map(function (FilamentBaseFilter $filter) use ($defaultFavoriteFilters) {
                return $filter->favorite(in_array($filter->getName(), $defaultFavoriteFilters));
            })
            ->toArray();

        $this->getTable()->filters($filters);
    }

    public static function quickFiltersAreEnabled(): bool
    {
        return Config::quickFiltersAreEnabled();
    }

    protected function getQuickFilterFormSchema(string | Indicator | null $indicator, ?string $filter): array
    {
        if (! $indicator) {
            return [];
        }

        return collect($this->getTable()->getFiltersFormSchema())
            ->flatMap(function (Section | Group $layout) {
                if ($layout instanceof Section) {
                    return invade($layout)->childComponents['default'] ?? [];
                }

                if ($layout instanceof Group) {
                    return [$layout];
                }
            })
            ->filter(function (Group $group) use ($filter) {
                return invade($group)->key === str($filter)->before('.')->toString();
            })
            ->map(function (Group $group) use ($filter) {
                $tableFilter = $this->getTable()->getFilter(str($filter)->before('.')->toString());

                if ($tableFilter instanceof AdvancedFilter || $tableFilter instanceof QueryBuilder) {
                    return $group;
                }

                $childComponents = $this->getQuickFilterFormGroupChildComponents($tableFilter, $group, $filter);

                return $group
                    ->getClone()
                    ->schema($childComponents);
            })
            ->toArray();
    }

    protected function getQuickFilterFormGroupChildComponents(FilamentBaseFilter $tableFilter, Group $group, string $filter): array
    {
        $childComponents = collect(invade($group)->childComponents['default'] ?? [])
            ->filter(fn (Component $component) => $component instanceof Component)
            ->values();
        $removeTableFilterName = str($filter)->before('.')->toString();

        if ($tableFilter->hasMultipleIndicators()) {
            return $this->buildComponentsForMultipleIndicator($childComponents, $filter, $removeTableFilterName);
        }

        return $this->buildComponentsForSingleIndicator($childComponents, $removeTableFilterName);
    }

    protected function buildComponentsForMultipleIndicator(Collection $childComponents, string $filter, string $removeTableFilterName): array
    {
        return $childComponents
            ->when($childComponents->count() > 1, function (Collection $components) use ($filter) {
                return $components->filter(
                    fn (Component $component) => $component->getName() === str($filter)->after('.')->toString()
                );
            })
            ->map(fn (Component $component) => $this->addResetAction($component->getClone(), $removeTableFilterName, $component->getName()))
            ->toArray();
    }

    protected function buildComponentsForSingleIndicator(Collection $childComponents, string $removeTableFilterName): array
    {
        return $childComponents
            ->map(function (Component $component, $key) use ($removeTableFilterName) {
                $component = $component->getClone();

                return $key === 0 ? $this->addResetAction($component, $removeTableFilterName) : $component;
            })
            ->toArray();
    }

    protected function addResetAction(Component $component, string $removeTableFilterName, ?string $componentName = null): Component
    {
        $removeTableFilter = "'{$removeTableFilterName}'" . (filled($componentName) ? ", '{$componentName}'" : '');

        return $component->hintAction(
            Action::make('reset')
                ->label(__('filament-tables::table.filters.actions.reset.label'))
                ->color('danger')
                ->action("removeTableFilter({$removeTableFilter})")
                ->extraAttributes([
                    'x-on:click' => 'close',
                ])
        );
    }
}
