<?php

namespace Archilex\AdvancedTables;

use Archilex\AdvancedTables\Components\PresetView;
use Archilex\AdvancedTables\Concerns\CanPersistViews;
use Archilex\AdvancedTables\Concerns\CanReorderColumns;
use Archilex\AdvancedTables\Concerns\CanReorderViews;
use Archilex\AdvancedTables\Concerns\HasDefaultView;
use Archilex\AdvancedTables\Concerns\HasFormSchemas;
use Archilex\AdvancedTables\Concerns\HasMultiSort;
use Archilex\AdvancedTables\Concerns\HasPresetViews;
use Archilex\AdvancedTables\Concerns\HasQuickFilters;
use Archilex\AdvancedTables\Concerns\HasUserViews;
use Archilex\AdvancedTables\Concerns\HasViewActions;
use Archilex\AdvancedTables\Forms\Components\AdvancedFilterBuilder;
use Archilex\AdvancedTables\Support\Config;
use Filament\Forms\Components\Select;
use Filament\QueryBuilder\Forms\Components\RuleBuilder;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Component;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Filters\BaseFilter as FilamentBaseFilter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\View\TablesRenderHook;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

trait AdvancedTables
{
    use CanPersistViews;
    use CanReorderColumns;
    use CanReorderViews;
    use HasDefaultView;
    use HasFormSchemas;
    use HasMultiSort;
    use HasPresetViews;
    use HasQuickFilters;
    use HasUserViews;
    use HasViewActions;

    public bool $isMounted = false;

    public function bootedAdvancedTables()
    {
        $this->resetActiveViews = true;

        $this->cacheQuickFiltersForms();

        $this->cacheMultiSortForm();

        $this->normalizeActiveViews();

        if (static::favoritesBarIsEnabled()) {
            FilamentView::registerRenderHook(
                $this->getHookName(),
                fn (): View => view('advanced-tables::components.favorites-bar.index'),
                $this->getResourceName()
            );
        }

        if (static::quickSaveIsEnabled() && Config::isQuickSaveInTable()) {
            FilamentView::registerRenderHook(
                Config::quickSaveTablePosition(),
                fn (): View => view('advanced-tables::components.quick-save.button'),
                get_class($this)
            );
        }

        if (static::multiSortIsEnabled() && $this->hasSortableColumns()) {
            FilamentView::registerRenderHook(
                Config::multiSortTablePosition(),
                fn (): View => view('advanced-tables::components.multi-sort.button'),
                get_class($this)
            );
        }

        if (static::viewManagerIsEnabled() && Config::isViewManagerInTable()) {
            FilamentView::registerRenderHook(
                Config::viewManagerTablePosition(),
                fn (): View => view('advanced-tables::components.view-manager.button'),
                get_class($this)
            );
        }

        if (static::quickFiltersAreEnabled()) {
            FilamentView::registerRenderHook(
                TablesRenderHook::FILTER_INDICATORS,
                fn (array $data): View => view('advanced-tables::components.quick-filters.index', ['filterIndicators' => $data['filterIndicators']]),
                get_class($this)
            );
        }

        // if ($this->canReorderColumns() && ! $this->getTable()->hasColumnsLayout()) {
        //     $orderedColumnsSessionKey = $this->getOrderedTableColumnToggleFormStateSessionKey();

        //     if (empty($this->orderedToggledTableColumns) && session()->has($orderedColumnsSessionKey)) {
        //         $this->orderedToggledTableColumns = [
        //             ...($this->orderedToggledTableColumns ?? []),
        //             ...(session()->get($orderedColumnsSessionKey) ?? []),
        //         ];
        //     }

        //     $this->setDefaultToggledTableColumnsOrder();

        //     $this->applyToggledTableColumnsOrder();
        // }

        if ($this->isMounted) {
            return;
        }

        if ($this->hasMultipleInstances()) {
            $this->activePresetView = null;
            $this->activeUserView = null;
            $this->currentPresetView = null;
            $this->currentUserView = null;
        }

        $shouldPersistActiveViewInSession = Config::persistsActiveViewInSession();

        $activePresetViewSessionKey = $this->getActivePresetViewSessionKey();

        if (
            blank($this->activePresetView) &&
            $shouldPersistActiveViewInSession &&
            session()->has($activePresetViewSessionKey)
        ) {
            $this->activePresetView = session()->get($activePresetViewSessionKey);

            $this->applyPresetViewConfiguration();
        }

        $currentPresetViewSessionKey = $this->getCurrentPresetViewSessionKey();

        if (
            blank($this->activePresetView) &&
            blank($this->currentPresetView) &&
            $shouldPersistActiveViewInSession &&
            session()->has($currentPresetViewSessionKey)
        ) {
            $this->currentPresetView = session()->get($currentPresetViewSessionKey);
        }

        $activeUserViewSessionKey = $this->getActiveUserViewSessionKey();

        if (
            blank($this->activeUserView) &&
            $shouldPersistActiveViewInSession &&
            session()->has($activeUserViewSessionKey)
        ) {
            $this->activeUserView = session()->get($activeUserViewSessionKey);
        }

        $currentUserViewSessionKey = $this->getCurrentUserViewSessionKey();

        if (
            blank($this->activeUserView) &&
            blank($this->currentUserView) &&
            $shouldPersistActiveViewInSession &&
            session()->has($currentUserViewSessionKey)
        ) {
            $this->currentUserView = session()->get($currentUserViewSessionKey);
        }

        if (filled($activeUserView = $this->activeUserView)) {
            $this->loadUserView(userView: $activeUserView, resetTable: false);
        } elseif (filled($currentUserView = $this->currentUserView)) {
            $this->loadUserView(userView: $currentUserView, resetTable: false, isActive: false);
        } elseif (filled($activePresetView = $this->activePresetView)) {
            $this->loadPresetView(presetView: $activePresetView, resetTable: false);
        } elseif (filled($currentPresetView = $this->currentPresetView)) {
            $this->loadPresetView(presetView: $currentPresetView, resetTable: false, isActive: false);
        } elseif (filled($this->getDefaultViewConfiguration())) {
            $this->loadDefaultView();
        } else {
            $this->loadDefaultTable();
        }

        $this->isMounted = true;
    }

    public function applyTableColumnManager(?array $state = null, bool $wasReordered = false): void
    {
        // $this->orderedToggledTableColumns = array_merge(
        //     $this->orderedToggledTableColumns,
        //     Arr::dot($this->toggledTableColumns)
        // );

        // $this->persistOrderedTableColumnsToSession();

        if ($state) {
            $this->saveModifiedDefaultPresetViewColumnsToSession();
        }

        if ($this->resetActiveViews) {
            $this->resetActiveViewsIfRequired();

            $this->applyFavoriteQuickFilters();
        }

        parent::applyTableColumnManager($state, $wasReordered);
    }

    public function updatedQuickFilters(): void
    {
        $this->tableFilters = array_merge($this->tableFilters, $this->quickFilters);

        $this->updatedTableFilters();
    }

    public function applyTableFilters(): void
    {
        $this->resetActiveViewsIfRequired();

        $this->applyQuickFilters(forDeferredFilters: true);

        parent::applyTableFilters();
    }

    public function handleTableFilterUpdates(): void
    {
        $this->resetActiveViewsIfRequired();

        $this->applyQuickFilters();

        parent::handleTableFilterUpdates();
    }

    public function updatedTableGrouping(): void
    {
        $this->resetActiveViewsIfRequired();

        $this->applyFavoriteQuickFilters();
    }

    public function updatedTableSort(): void
    {
        $this->applyMultiSort();

        $this->resetActiveViewsIfRequired();

        parent::updatedTableSort();
    }

    public function updatedTableSearch(): void
    {
        $this->resetActiveViewsIfRequired();

        $this->applyFavoriteQuickFilters();

        parent::updatedTableSearch();
    }

    /**
     * @param  string | null  $value
     */
    public function updatedTableColumnSearches($value = null, ?string $key = null): void
    {
        $this->resetActiveViewsIfRequired();

        $this->applyFavoriteQuickFilters();

        parent::updatedTableColumnSearches($value, $key);
    }

    public function removeTableFilter(string $filterName, ?string $field = null, bool $isRemovingAllFilters = false): void
    {
        $filter = $this->getTable()->getFilter($filterName);
        $filterResetState = $filter->getResetState();

        $filterFormGroup = $this->getTableFiltersForm()->getComponentByStatePath($filterName);

        if (($filter instanceof QueryBuilder) && blank($field)) {
            $filterFormGroup->getChildSchema()->fill();
        } elseif ($filter instanceof QueryBuilder) {
            $ruleBuilder = $filterFormGroup?->getChildSchema()->getComponent(fn (Component $component): bool => $component instanceof RuleBuilder);

            $ruleBuilderRawState = $ruleBuilder?->getRawState() ?? [];
            unset($ruleBuilderRawState[$field]);
            $ruleBuilder?->rawState($ruleBuilderRawState);
        } else {
            $filterFields = $filterFormGroup?->getChildSchema()->getFlatFields();

            if (filled($field) && array_key_exists($field, $filterFields)) {
                $filterFields = [$field => $filterFields[$field]];
            }

            foreach ($filterFields as $fieldName => $field) {
                if ($field instanceof AdvancedFilterBuilder) {
                    continue;
                }

                $state = $field->getState();

                $field->state($filterResetState[$fieldName] ?? match (true) {
                    is_array($state) => [],
                    is_bool($state) => $field->hasNullableBooleanState() ? null : false,
                    default => null,
                });
            }
        }

        if ($isRemovingAllFilters) {
            return;
        }

        if ($this->getTable()->hasDeferredFilters()) {
            $this->applyTableFilters();

            return;
        }

        $this->updatedTableFilters();
    }

    protected function getFilterFields(string $filterName, ?string $field): ?array
    {
        $filterFormGroup = $this->getTableFiltersForm()->getComponent($filterName) ?? null;

        if (! $filterFormGroup) {
            return null;
        }

        $filterFields = $filterFormGroup?->getChildSchema()->getFlatFields();

        if (blank($field)) {
            return $filterFields;
        }

        $isSingleIndicator = collect($filterFields)->keys()->contains(function ($key) use ($field) {
            return Str::startsWith($key, Str::beforeLast($field, '.'));
        });

        if (! $isSingleIndicator && ! array_key_exists($field, $filterFields)) {
            return $filterFields;
        }

        if (Str::afterLast($field, '.') === 'operator' || (! array_key_exists($field, $filterFields) && $isSingleIndicator)) {
            $filterKey = Str::beforeLast($field, '.');

            return Arr::where(
                $filterFields,
                fn ($value, $key) => $filterKey === Str::beforeLast($key, '.')
            );
        }

        return [$field => $filterFields[$field]];
    }

    public function resetTable(): void
    {
        $this->resetTableViews();

        $this->bootedInteractsWithTable();

        if ($this->resetTableFiltersForm) {
            $this->resetTableFilterForm();
        }

        $this->resetPage();

        $this->flushCachedTableRecords();
    }

    public function resetTableViews(): void
    {
        $this->activePresetView = null;

        $this->currentPresetView = null;

        $this->activeUserView = null;

        $this->currentUserView = null;
    }

    public function resetTableFiltersForm(): void
    {
        $this->getTableFiltersForm()->fill();

        if ($this->getTable()->hasDeferredFilters()) {
            $this->applyTableFilters();

            return;
        }

        $this->resetActiveViewsIfRequired();

        $this->applyQuickFilters();

        $this->handleTableFilterUpdates();
    }

    public function loadDefaultTable(): void
    {
        $this->applyMultiSort();
        $this->applyQuickFilters();
        $this->persistToSessionIfEnabled();
    }

    public function resetTableSort(): void
    {
        $this->tableSort = null;

        $this->updatedTableSort();
    }

    public function getPresetViewsForm(Collection $presetViews): \Filament\Schemas\Schema
    {
        return $this->makeSchema()
            ->schema([
                Select::make('activePresetView')
                    ->hiddenLabel()
                    ->allowHtml()
                    ->searchable()
                    ->placeholder(__('advanced-tables::advanced-tables.select.placeholder'))
                    ->options(
                        $presetViews
                            ->map(
                                fn (PresetView $presetView, $presetViewName) => $presetView->getLabel() ?? $this->generatePresetViewLabel($presetViewName)
                            )
                            ->toArray()
                    ),
            ])
            ->reactive();
    }

    public static function favoritesBarIsEnabled(): bool
    {
        return Config::favoritesBarIsEnabled();
    }

    public static function quickSaveIsEnabled(): bool
    {
        return Config::isQuickSaveEnabled();
    }

    public static function viewManagerIsEnabled(): bool
    {
        return Config::isViewManagerEnabled();
    }

    protected function applyFiltersToTableQuery(Builder $query, bool $isResolvingRecord = false): Builder
    {
        if ($presetView = $this->getActivePresetView() ?? $this->getCurrentPresetView()) {
            $presetView->modifyQuery($query);
        }

        return parent::applyFiltersToTableQuery($query, $isResolvingRecord);
    }

    protected function getDefaultTableGrouping(): ?string
    {
        if ($this->getTable()->isDefaultGroupSelectable()) {
            return $this->getTable()->getDefaultGroup()->getId() . ':asc';
        }

        return null;
    }

    protected function getDefaultTableSort(): ?string
    {
        if ($sortColumn = $this->getTable()->getDefaultSortColumn()) {
            return $sortColumn . ':' . ($this->getTable()->getDefaultSortDirection() ?? 'asc');
        }

        return null;
    }

    protected function getHookName(): string
    {
        if ($this->isRelationManager()) {
            return 'panels::resource.relation-manager.before';
        }

        if ($this->isTableWidget()) {
            return 'widgets::table-widget.start';
        }

        if ($this->isManageRelatedRecords()) {
            return 'panels::resource.pages.manage-related-records.table.before';
        }

        if ($this->isRecordFinder()) {
            return 'record-finder::livewire.record-finder-table.before';
        }

        return 'panels::resource.pages.list-records.table.before';
    }

    protected function getResourceName(): string
    {
        if ($this->isResource()) {
            return $this->getResource();
        }

        return get_class($this);
    }

    protected function hasActiveFilters()
    {
        if ($this->getTable()->hasSearch()) {
            return true;
        }

        return collect($this->getTable()->getFilters())
            ->filter(fn (FilamentBaseFilter $filter) => $filter->getIndicators())
            ->isNotEmpty();
    }

    protected function isResource(): bool
    {
        return is_subclass_of($this, ListRecords::class);
    }

    protected function isRelationManager(): bool
    {
        return is_subclass_of($this, RelationManager::class);
    }

    protected function isTableWidget(): bool
    {
        return is_subclass_of($this, TableWidget::class);
    }

    protected function isManageRelatedRecords(): bool
    {
        return is_subclass_of($this, ManageRelatedRecords::class);
    }

    protected function isRecordFinder(): bool
    {
        return is_subclass_of($this, \RalphJSmit\Filament\RecordFinder\Livewire\RecordFinderTable::class);
    }

    public function resetActiveViewsIfRequired(): void
    {
        $this->activeUserView = null;

        $this->activePresetView = null;

        $persistActiveViewInSession = Config::persistsActiveViewInSession();

        if ($persistActiveViewInSession) {
            session()->forget([
                $this->getActivePresetViewSessionKey(),
                $this->getActiveUserViewSessionKey(),
            ]);
        }
    }

    protected function resetTableFilterForm(): void
    {
        $this->getTableFiltersForm()->fill();

        if ($this->getTable()->hasDeferredFilters()) {
            $this->applyTableFilters();
        }
    }

    // Temporary fix until LW fixes null values in query string
    protected function normalizeActiveViews(): void
    {
        if ($this->activePresetView === 'null') {
            $this->activePresetView = null;
        }

        if ($this->currentPresetView === 'null') {
            $this->currentPresetView = null;
        }

        if ($this->activeUserView === 'null') {
            $this->activeUserView = null;
        }

        if ($this->currentUserView === 'null') {
            $this->currentUserView = null;
        }
    }
}
