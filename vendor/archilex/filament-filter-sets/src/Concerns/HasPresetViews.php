<?php

namespace Archilex\AdvancedTables\Concerns;

use Archilex\AdvancedTables\Components\PresetView;
use Archilex\AdvancedTables\Models\ManagedDefaultView;
use Archilex\AdvancedTables\Support\Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

trait HasPresetViews
{
    #[Url]
    public ?string $activePresetView = null;

    #[Url]
    public ?string $currentPresetView = null;

    /**
     * @var array<string | int, PresetView>
     */
    protected array $cachedPresetViews;

    protected bool $resetTableFiltersForm = true;

    /**
     * @var array<string | int, PresetView>
     */
    protected array $cachedMergedPresetViews;

    // Update on dropdown select
    public function updatedActivePresetView($value): void
    {
        $this->removeTableFilters();

        $this->activeUserView = null;

        if (! $value) {
            $this->loadDefaultView();

            return;
        }

        $this->activePresetView = $value;

        $this->applyPresetViewConfiguration();
    }

    public function loadPresetView(string $presetView, ?array $filters = null, bool $resetTable = true, bool $isActive = true): void
    {
        if (
            Arr::exists($this->getCachedPresetViews(), $presetView)
            && $resetTable
        ) {
            $this->resetTableFiltersForm = ! $this->getCachedPresetViews()[$presetView]->shouldPreserveFilters();

            $this->resetTable();
        }

        $this->currentPresetView = $presetView;

        $this->activePresetView = $isActive ? $presetView : null;

        $this->applyPresetViewConfiguration();

        $this->saveModifiedDefaultPresetViewColumnsToSession(false);

        if (Config::showViewManagerAsSlideOver() && method_exists($this, 'forceRender')) {
            $this->forceRender();
        }
    }

    /**
     * @return array<string | int, PresetView>
     */
    public function getPresetViews(): array
    {
        return [];
    }

    /**
     * @return array<string | int, PresetView>
     */
    public function getCachedPresetViews(): array
    {
        $presetViews = $this->getPresetViews();

        return $this->cachedPresetViews ??= collect()
            ->when($this->hasDefaultView() && ! array_key_exists('default', $presetViews), function (Collection $collection) {
                return $collection->merge([
                    'default' => $this->getDefaultView(),
                ]);
            })
            ->merge($this->getPresetViews())
            ->map(function (PresetView $presetView, $key): PresetView {
                return $presetView->name($key);
            })
            ->toArray();
    }

    /**
     * @return array<string | int, PresetView>
     */
    public function getCachedMergedPresetViews(): array
    {
        return $this->cachedMergedPresetViews ??= $this->getMergedPresetViews();
    }

    public function getPresetViewsArray(): array
    {
        $presetViews = collect($this->getCachedMergedPresetViews());

        return [
            'hiddenPresetViews' => $this->buildHiddenPresetViewsFrom($presetViews),
            'favoritePresetViews' => $this->buildFavoritePresetViewsFrom($presetViews),
        ];
    }

    public function generatePresetViewLabel(string $key): string
    {
        return (string) str($key)
            ->replace(['_', '-'], ' ')
            ->ucfirst();
    }

    public function getModifiedDefaultPresetViewColumnsSessionKey(): string
    {
        $table = class_basename($this::class);

        return "tables.{$table}_modified_default_preset_view_columns";
    }

    protected function applyPresetViewConfiguration(): void
    {
        if (! $this->activePresetView) {
            $this->applyMultiSort();
            $this->applyQuickFilters();

            return;
        }

        $this->tableSearch = '';
        $this->tableFilters = $this->getFiltersFromPresetView() ?? $this->tableFilters;
        $this->tableSort = $this->getSortFromPresetView() ?? $this->getDefaultTableSort();
        $this->tableGrouping = $this->getGroupingFromPresetView() ?? $this->getDefaultTableGrouping();
        // $this->orderedToggledTableColumns = $this->getOrderedToggledColumnsFromPresetView() ?? $this->getDefaultToggledTableColumnsOrder();

        $this->applyMultiSort();

        $this->applyTableColumns();

        $this->getTableFiltersForm()->fill($this->tableFilters);

        if ($this->getTable()->hasDeferredFilters()) {
            $this->tableFilters = $this->tableDeferredFilters;
        }

        $this->applyQuickFilters();

        $this->persistToSessionIfEnabled();
    }

    protected function getDefaultPresetViewName(): ?string
    {
        return collect($this->getCachedPresetViews())
            ->filter(fn (PresetView $presetView) => $presetView->isDefault())
            ->keys()
            ->first();
    }

    protected function getMergedPresetViews(): array
    {
        $presetViews = $this->getCachedPresetViews();

        $managedDefaultView = Config::managedDefaultViewsAreEnabled()
            ? $this->getManagedDefaultView()
            : null;

        $presetViewsManagedByCurrentUser = Config::canManagePresetViews()
            ? $this->getPresetViewsManagedByCurrentUser()
            : null;

        return collect($presetViews)
            ->map(function (PresetView $presetView, $presetViewName) use ($managedDefaultView, $presetViewsManagedByCurrentUser) {
                $isDefault = $presetView->isDefault() &&
                    $presetView->getName() === $this->getDefaultPresetViewName();

                $isCurrentDefault = $managedDefaultView instanceof ManagedDefaultView &&
                    $managedDefaultView->isPresetView() &&
                    $managedDefaultView->view === $presetViewName;

                $presetView
                    ->default($isDefault)
                    ->currentDefault($isCurrentDefault || ! $managedDefaultView instanceof ManagedDefaultView && $isDefault);

                $presetViewManagedByCurrentUser = $presetViewsManagedByCurrentUser?->firstWhere('name', $presetViewName);

                if (! $presetViewManagedByCurrentUser) {
                    return $presetView;
                }

                return $presetView
                    ->managedByCurrentUser(true)
                    ->managedByCurrentUserId($presetViewManagedByCurrentUser->id)
                    ->favoritedByCurrentUser($presetViewManagedByCurrentUser->is_favorite)
                    ->managedByCurrentUserSortOrder($presetViewManagedByCurrentUser->sort_order);
            })
            ->sortBy($this->getPresetViewSortByArray())
            ->toArray();
    }

    public function getFavoritePresetViewsFromPresetViews(): Collection
    {
        $presetViews = collect($this->getCachedMergedPresetViews());

        return $this->buildFavoritePresetViewsFrom($presetViews);
    }

    public function saveModifiedDefaultPresetViewColumnsToSession(bool $condition = true): void
    {
        if ($this->activePresetView === $this->getDefaultPresetViewName()) {
            session()->put($this->getModifiedDefaultPresetViewColumnsSessionKey(), $condition);
        }
    }

    protected function buildFavoritePresetViewsFrom(Collection $presetViews): Collection
    {
        return $presetViews->filter(function (PresetView $presetView) {
            return
                $presetView->isVisible() &&
                (
                    (! $presetView->isManagedByCurrentUser() && $presetView->isFavorite()) ||
                    ($presetView->isManagedByCurrentUser() && $presetView->isFavoritedByCurrentUser())
                );
        })
            ->sortBy($this->getPresetViewSortByArray());
    }

    protected function buildHiddenPresetViewsFrom(Collection $presetViews): Collection
    {
        return $presetViews->filter(function (PresetView $presetView) {
            return
                $presetView->isVisible() &&
                (
                    (! $presetView->isManagedByCurrentUser() && ! $presetView->isFavorite()) ||
                    ($presetView->isManagedByCurrentUser() && ! $presetView->isFavoritedByCurrentUser())
                );
        })
            ->sortBy($this->getPresetViewSortByArray());
    }

    public function getActivePresetView(): ?PresetView
    {
        $presetViews = $this->getCachedPresetViews();

        if (
            filled($this->activePresetView) &&
            Arr::exists($this->getCachedPresetViews(), $this->activePresetView)
        ) {
            return $presetViews[$this->activePresetView];
        }

        return null;
    }

    public function getCurrentPresetView(): ?PresetView
    {
        $presetViews = $this->getCachedPresetViews();

        if (
            filled($this->currentPresetView) &&
            Arr::exists($this->getCachedPresetViews(), $this->currentPresetView)
        ) {
            return $presetViews[$this->currentPresetView];
        }

        return null;
    }

    protected function getPresetViewsManagedByCurrentUser()
    {
        return once(
            fn () => Config::getManagedPresetView()::query()
                ->belongsToCurrentUser()
                ->resource($this->getResourceName())
                ->orderBy('sort_order', 'asc')
                ->get()
        );
    }

    protected function getPresetViewSortByArray(): array
    {
        return [
            fn (PresetView $a, PresetView $b) => Config::getNewPresetViewSortPosition() === 'after' ? $b->isManagedByCurrentUser() <=> $a->isManagedByCurrentUser() : $a->isManagedByCurrentUser() <=> $b->isManagedByCurrentUser(),
            fn (PresetView $a, PresetView $b) => $a->getManagedByCurrentUserSortOrder() <=> $b->getManagedByCurrentUserSortOrder(),
        ];
    }

    protected function getFiltersFromPresetView(): ?array
    {
        if (! $this->tableFilters) {
            return null;
        }

        $presetView = $this->getCurrentPresetView();

        if (! $presetView) {
            return null;
        }

        if ($presetView->shouldPreserveFilters()) {
            return $this->tableFilters;
        }

        $filters = $presetView->getDefaultFilters();

        if (empty($filters)) {
            return null;
        }

        if (Arr::exists($filters, 'advanced_filter_builder')) {
            $advancedFilterBuilderArray = [];

            foreach ($filters['advanced_filter_builder'] as $index => $advancedFilters) {
                $advancedFilterBuilderArray['or_group'][$index] = [
                    'type' => 'filter_group',
                ];

                foreach ($advancedFilters as $filter => $value) {
                    $advancedFilterBuilderArray['or_group'][$index]['data']['and_group'][] = [
                        'type' => $filter,
                        'data' => $value,
                    ];
                }
            }

            $filters['advanced_filter_builder'] = $advancedFilterBuilderArray;

            // Add an additional array to show the next or_group
            $filters['advanced_filter_builder']['or_group'][] = [
                'type' => 'filter_group',
                'data' => [
                    'and_group' => [],
                ],
            ];
        }

        return $this->tableFilters = array_merge($this->tableFilters, $filters);
    }

    public function getDefaultFavoriteFiltersFromCurrentPresetView(): ?array
    {
        $presetView = $this->getCurrentPresetView();

        if (! $presetView) {
            return null;
        }

        return $presetView->getDefaultFavoriteFilters();
    }

    protected function getGroupingFromPresetView(): ?string
    {
        $presetView = $this->getActivePresetView();

        if (! $presetView) {
            return null;
        }

        if ($presetView->shouldPreserveGrouping()) {
            return $this->tableGrouping;
        }

        $defaultGrouping = $presetView->getDefaultGrouping();

        if (blank($defaultGrouping)) {
            return null;
        }

        return $defaultGrouping . ':' . ($presetView->getDefaultGroupingDirection() ?? 'asc');
    }

    protected function getSortFromPresetView(): ?string
    {
        $presetView = $this->getActivePresetView();

        if (! $presetView) {
            return null;
        }

        if ($presetView->shouldPreserveSort()) {
            return $this->tableSort;
        }

        $defaultSort = $presetView->getDefaultSort();

        if (blank($defaultSort)) {
            return null;
        }

        if (is_array($defaultSort)) {
            return collect($defaultSort)
                ->map(function (string $direction, string $column) {
                    return $column . ':' . $direction;
                })
                ->join(',');
        }

        return $defaultSort . ':' . ($presetView->getDefaultSortDirection() ?? 'asc');
    }

    protected function getTableColumnsFromPresetView(): ?array
    {
        $presetView = $this->getActivePresetView();

        if (! $presetView instanceof PresetView) {
            return null;
        }

        if ($presetView->shouldPreserveColumns()) {
            return $this->tableColumns;
        }

        $columns = $presetView->getDefaultColumns();

        if (empty($columns)) {
            return null;
        }

        $state = [];

        // TODO: This needs to be updated to support column groups
        foreach ($this->getTable()->getColumns() as $column) {
            if (! in_array($column->getName(), $columns)) {
                continue;
            }

            $state[$column->getName()] = [
                'type' => 'column',
                'name' => $column->getName(),
                'label' => $column->getLabel(),
                'isHidden' => $column->isHidden(),
                'isToggled' => true,
                'isToggleable' => $column->isToggleable(),
                'isToggledHiddenByDefault' => $column->isToggledHiddenByDefault(),
            ];
        }

        return array_values(
            array_merge(
                array_flip($columns),
                $state
            )
        );
    }
}
