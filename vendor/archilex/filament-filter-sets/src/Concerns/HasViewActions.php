<?php

namespace Archilex\AdvancedTables\Concerns;

use Archilex\AdvancedTables\Enums\ViewType;
use Archilex\AdvancedTables\Support\Authorize;
use Archilex\AdvancedTables\Support\Config;
use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait HasViewActions
{
    protected bool $resetActiveViews = false;

    public function addViewToFavoritesAction(): Action
    {
        return Action::make('addViewToFavorites')
            ->label(__('advanced-tables::advanced-tables.view_manager.actions.add_view_to_favorites'))
            ->extraAttributes(['class' => 'advanced-tables-add-view-to-favorites'])
            ->icon('heroicon-o-star')
            ->action(function (array $arguments) {
                if (Arr::get($arguments, 'presetView')) {
                    $existingPresetView = [
                        'name' => $arguments['presetView'],
                        'resource' => $this->getResourceName(),
                    ];

                    if (Config::hasTenancy()) {
                        $existingPresetView[Config::getTenantColumn()] = Config::getTenantId();
                    }

                    return Config::auth()->user()
                        ->managedPresetViews()
                        ->updateOrCreate(
                            $existingPresetView,
                            ['is_favorite' => true]
                        );
                }

                return Config::auth()->user()
                    ->managedUserViews()
                    ->syncWithPivotValues($arguments['userView'], [
                        'is_visible' => true,
                        'sort_order' => Config::getUserView()::query()->favoritedByCurrentUser()->count() + 1,
                    ], false);
            });
    }

    public function applyViewAction(): Action
    {
        return Action::make('applyView')
            ->label(__('advanced-tables::advanced-tables.view_manager.actions.apply_view'))
            ->extraAttributes(['class' => 'advanced-tables-apply-view'])
            ->icon('heroicon-s-arrow-small-right')
            ->action(
                fn (array $arguments) => $arguments['presetView'] ?? null
                    ? $this->loadPresetView($arguments['presetView'])
                    : $this->loadUserView($arguments['userView'], $arguments['filters'])
            );
    }

    public function setAsManagedDefaultViewAction(): Action
    {
        return Action::make('setAsManagedDefaultView')
            ->label(__('advanced-tables::advanced-tables.view_manager.actions.set_as_managed_default_view'))
            ->extraAttributes(function (array $arguments) {
                $key = ($userView = Arr::get($arguments, 'userView'))
                    ? 'set-managed-default-user-view-' . $userView
                    : 'set-managed-default-preset-view-' . Arr::get($arguments, 'presetView');

                return [
                    'class' => 'advanced-tables-set-as-managed-default-view',
                    'wire:key' => $key,
                ];
            })
            ->icon(Config::getManagedDefaultViewSetIcon())
            ->action(function (array $arguments) {
                $this->removeCurrentManagedDefaultView();

                $presetView = Arr::get($arguments, 'presetView');
                $userView = Arr::get($arguments, 'userView');

                if (
                    Arr::get($arguments, 'isDefault') &&
                    $presetView === $this->getDefaultPresetViewName()
                ) {
                    if ($this->activePresetView !== $presetView) {
                        $this->loadPresetView($presetView);
                    }

                    return;
                }

                Config::auth()->user()
                    ->managedDefaultViews()
                    ->create([
                        'resource' => $this->getResourceName(),
                        'view_type' => $presetView ? ViewType::PresetView : ViewType::UserView,
                        'view' => $presetView ?? Arr::get($arguments, 'userView'),
                    ]);

                $viewIsActive = filled($presetView)
                    ? $this->activePresetView === $presetView
                    : $this->activeUserView === $userView;

                if ($viewIsActive) {
                    return;
                }

                filled($presetView)
                    ? $this->loadPresetView($presetView)
                    : $this->loadUserView(Arr::get($arguments, 'userView'), Arr::get($arguments, 'filters'));
            });
    }

    public function removeAsManagedDefaultViewAction(): Action
    {
        return Action::make('removeAsManagedDefaultView')
            ->label(__('advanced-tables::advanced-tables.view_manager.actions.remove_as_managed_default_view'))
            ->extraAttributes(function (array $arguments) {
                $key = ($userView = Arr::get($arguments, 'userView'))
                    ? 'remove-managed-default-user-view-' . $userView
                    : 'remove-managed-default-preset-view-' . Arr::get($arguments, 'presetView');

                return [
                    'class' => 'advanced-tables-remove-as-managed-default-view',
                    'wire:key' => $key,
                ];
            })
            ->icon(Config::getManagedDefaultViewRemoveIcon())
            ->action(function () {
                $this->removeCurrentManagedDefaultView();
            });
    }

    public function saveUserViewAction(): Action
    {
        return Action::make('saveUserView')
            ->label(fn () => Config::isQuickSaveInFavoritesBar() ? __('advanced-tables::advanced-tables.view_manager.actions.save_view') : __('advanced-tables::advanced-tables.view_manager.actions.save'))
            ->extraAttributes(['class' => 'advanced-tables-save-view'])
            ->view(Config::isQuickSaveInFavoritesBar() || Config::isQuickSaveInTable() ? Action::ICON_BUTTON_VIEW : Action::LINK_VIEW)
            ->icon(fn () => Config::isQuickSaveInFavoritesBar() || Config::isQuickSaveInTable() ? Config::getQuickSaveIcon() : null)
            ->iconSize(fn () => Config::isQuickSaveInFavoritesBar() || Config::isQuickSaveInTable() ? 'md' : null)
            ->color(Config::isQuickSaveInTable() ? 'gray' : 'primary')
            ->schema(fn () => $this->getSaveOptionFormSchema())
            ->slideOver(fn () => Config::showQuickSaveAsSlideOver())
            ->modalHeading(__('advanced-tables::advanced-tables.quick_save.save.modal_heading'))
            ->modalSubmitActionLabel(__('advanced-tables::advanced-tables.quick_save.save.submit_label'))
            ->modalWidth(fn () => Config::showQuickSaveAsSlideOver() ? 'md' : '4xl')
            ->visible(Authorize::canPerformAction('create'))
            ->action(function (array $data) {
                $view = $this->saveUserView($data);

                Notification::make()
                    ->success()
                    ->title(__('advanced-tables::advanced-tables.notifications.save_view.saved.title'))
                    ->send();

                $this->activeUserView = $view?->id;
            })
            ->afterFormFilled(function (Action $action) {
                if (filled($this->activePresetView) && ! Config::canCreateUsingPresetView()) {
                    Notification::make()
                        ->warning()
                        ->title(__('advanced-tables::advanced-tables.notifications.preset_views.title'))
                        ->body(__('advanced-tables::advanced-tables.notifications.preset_views.body'))
                        ->persistent()
                        ->send();

                    $action->cancel();
                }
            });
    }

    public function deleteViewAction(): Action
    {
        return Action::make('deleteView')
            ->label(__('advanced-tables::advanced-tables.view_manager.actions.delete_view'))
            ->extraAttributes(['class' => 'advanced-tables-delete-view'])
            ->icon('heroicon-s-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription(function (array $arguments) {
                // Currently throwing Error: Undefined array key "type"
                // due to multiple LW requests? Will investigate later.

                // return in_array($arguments['type'], ['public', 'global'])
                //     ? __('advanced-tables::advanced-tables.view_manager.actions.delete_view_description', ['type' => $arguments['type']])
                //     : __('filament-actions::modal.confirmation');

                return __('filament-actions::modal.confirmation');
            })
            ->modalSubmitActionLabel(__('advanced-tables::advanced-tables.view_manager.actions.delete_view_modal_submit_label'))
            ->action(function (array $arguments) {
                $view = Config::getUserView()::find($arguments['userView']);

                $view->userManagedUserViews()->detach(Config::auth()->id());

                $view->delete();

                Notification::make()
                    ->success()
                    ->title(__('filament-actions::delete.single.notifications.deleted.title'))
                    ->send();
            });
    }

    public function editViewAction(): Action
    {
        return Action::make('editView')
            ->label(__('advanced-tables::advanced-tables.view_manager.actions.edit_view'))
            ->extraAttributes(['class' => 'advanced-tables-edit-view'])
            ->icon('heroicon-s-pencil-square')
            ->slideOver(fn () => Config::showQuickSaveAsSlideOver())
            ->modalWidth(fn () => Config::showQuickSaveAsSlideOver() ? 'md' : '4xl')
            ->modalSubmitActionLabel(__('filament-actions::edit.single.modal.actions.save.label'))
            ->schema(fn () => $this->getSaveOptionFormSchema())
            ->fillForm(function (array $arguments) {
                return [
                    ...(Config::getUserView()::find($arguments['userView'])->attributesToArray()),
                    ...(['is_managed_by_current_user' => $arguments['isFavorite']]),
                ];
            })
            ->action(function (array $arguments, array $data) {
                $view = Config::getUserView()::find($arguments['userView']);

                if ($data['is_managed_by_current_user']) {
                    $view->userManagedUserViews()->syncWithoutDetaching(Config::auth()->id());
                } else {
                    $view->userManagedUserViews()->detach(Config::auth()->id());
                }

                $view->update(Arr::except($data, ['is_managed_by_current_user']));

                Notification::make()
                    ->success()
                    ->title(__('advanced-tables::advanced-tables.notifications.edit_view.saved.title'))
                    ->send();
            });
    }

    public function replaceViewAction(): Action
    {
        return Action::make('replaceView')
            ->label(__('advanced-tables::advanced-tables.view_manager.actions.replace_view'))
            ->extraAttributes(['class' => 'advanced-tables-replace-view'])
            ->icon('heroicon-s-arrows-right-left')
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription(__('advanced-tables::advanced-tables.view_manager.actions.replace_view_modal_description'))
            ->modalSubmitActionLabel(__('advanced-tables::advanced-tables.view_manager.actions.replace_view_modal_submit_label'))
            ->action(function (array $arguments) {
                $view = Config::getUserView()::findOrFail($arguments['userView']);

                $view->update([
                    'filters' => $this->getFilters(),
                    'indicators' => $this->getMergedFilterIndicators(),
                ]);

                Notification::make()
                    ->success()
                    ->title(__('advanced-tables::advanced-tables.notifications.replace_view.replaced.title'))
                    ->send();

                $this->activeUserView = $view?->id;
            });
    }

    public function removeViewFromFavoritesAction(): Action
    {
        return Action::make('removeViewFromFavorites')
            ->label(__('advanced-tables::advanced-tables.view_manager.actions.remove_view_from_favorites'))
            ->extraAttributes(['class' => 'advanced-tables-remove-view-from-favorites'])
            ->icon('heroicon-o-minus-circle')
            ->action(function (array $arguments) {
                if (Arr::get($arguments, 'presetView')) {
                    $existingPresetView = [
                        'name' => $arguments['presetView'],
                        'resource' => $this->getResourceName(),
                    ];

                    if (Config::hasTenancy()) {
                        $existingPresetView[Config::getTenantColumn()] = Config::getTenantId();
                    }

                    return Config::auth()->user()
                        ->managedPresetViews()
                        ->updateOrCreate(
                            $existingPresetView,
                            ['is_favorite' => false]
                        );
                }

                if (Arr::get($arguments, 'shouldHide', false)) {
                    return Config::auth()->user()
                        ->managedUserViews()
                        ->syncWithPivotValues($arguments['userView'], [
                            'is_visible' => false,
                        ], false);
                }

                return Config::auth()->user()
                    ->managedUserViews()
                    ->detach($arguments['userView']);
            });
    }

    public function showViewManagerAction(): Action
    {
        return Action::make('showViewManager')
            ->label(__('advanced-tables::advanced-tables.view_manager.actions.show_view_manager'))
            ->extraAttributes(['class' => 'advanced-tables-show-view-manager'])
            ->modalHeading(__('advanced-tables::advanced-tables.view_manager.heading'))
            ->iconButton()
            ->icon(Config::getViewManagerIcon())
            ->iconSize('md')
            ->color(Config::isViewManagerInTable() ? 'gray' : 'primary')
            ->slideOver()
            ->modalWidth('md')
            ->modalCancelAction(false)
            ->modalSubmitAction(false)
            ->registerModalActions([
                $this->addViewToFavoritesAction(),
                $this->applyViewAction(),
                $this->setAsManagedDefaultViewAction(),
                $this->removeAsManagedDefaultViewAction(),
                $this->removeViewFromFavoritesAction(),
                $this->editViewAction(),
                $this->replaceViewAction(),
                $this->deleteViewAction(),
            ])
            ->modalContent(fn (Action $action): View => view(
                'advanced-tables::components.view-manager.index',
                ['action' => $action],
            ));
    }

    public function getPresetViewActions(?Action $action, string $viewKey, bool $isFavorite, bool $canBeManaged, bool $isCurrentDefault, bool $isDefault): array
    {
        $getAction = $this->getActionResolver($action);

        $actions = [];

        if (Config::hasApplyButtonInViewManager()) {
            $actions[] = ($getAction('applyView'))(['presetView' => $viewKey]);
        }

        if (Config::managedDefaultViewsAreEnabled() && ! $isDefault && $isCurrentDefault) {
            $actions[] = ($getAction('removeAsManagedDefaultView'))([
                'presetView' => $viewKey,
                'isFavorite' => $isFavorite,
            ]);
        }

        if (Config::managedDefaultViewsAreEnabled() && ! $isCurrentDefault) {
            $actions[] = ($getAction('setAsManagedDefaultView'))([
                'presetView' => $viewKey,
                'isDefault' => $isDefault,
                'isFavorite' => $isFavorite,
            ]);
        }

        if ($isFavorite && $canBeManaged) {
            $actions[] = ($getAction('removeViewFromFavorites'))(['presetView' => $viewKey]);
        }

        if (! $isFavorite && $canBeManaged) {
            $actions[] = ($getAction('addViewToFavorites'))(['presetView' => $viewKey]);
        }

        return $actions;
    }

    public function getUserViewActions(?Action $action, string $viewKey, bool $isFavorite, bool $canBeManaged, bool $isCurrentDefault, bool $belongsToCurrentUser, array $filters, ?string $visibility = null): array
    {
        $getAction = $this->getActionResolver($action);

        $actions = [];

        if (Config::hasApplyButtonInViewManager()) {
            $actions[] = ($getAction('applyView'))(['userView' => $viewKey, 'filters' => $filters]);
        }

        if (Config::managedDefaultViewsAreEnabled() && $isCurrentDefault) {
            $actions[] = ($getAction('removeAsManagedDefaultView'))(['userView' => $viewKey]);
        }

        if (Config::managedDefaultViewsAreEnabled() && ! $isCurrentDefault) {
            $actions[] = ($getAction('setAsManagedDefaultView'))(['userView' => $viewKey]);
        }

        if ($isFavorite && $canBeManaged) {
            $actions[] = ($getAction('removeViewFromFavorites'))([
                'userView' => $viewKey,
                'shouldHide' => (
                    Config::managedDefaultViewsAreEnabled() ||
                    (! $belongsToCurrentUser && $visibility === 'global')
                ),
            ]);
        }

        if (! $isFavorite && $canBeManaged) {
            $actions[] = ($getAction('addViewToFavorites'))(['userView' => $viewKey]);
        }

        if ($belongsToCurrentUser) {
            $actions[] = ($getAction('editView'))(['userView' => $viewKey, 'isFavorite' => $isFavorite]);
        }

        if ($belongsToCurrentUser) {
            return [
                \Filament\Actions\ActionGroup::make(
                    $actions
                )->dropdown(false),
                ($getAction('replaceView'))([
                    'userView' => $viewKey,
                    'type' => $visibility,
                ]),
                ($getAction('deleteView'))([
                    'userView' => $viewKey,
                    'type' => $visibility,
                ]),
            ];
        }

        return $actions;
    }

    protected function getActionResolver(?Action $action): Closure
    {
        return fn (string $name) => $action
            ? $action->getModalAction($name)
            : $this->getAction($name);
    }

    protected function getActivePresetViewIndicators(): ?array
    {
        $presetView = $this->getActivePresetView();

        if (! $presetView || ! $presetView->modifiesQuery()) {
            return null;
        }

        return collect(__('advanced-tables::advanced-tables.forms.preset_view.label') . ': ' . $this->getActivePresetViewLabel())
            ->when(
                $presetView->getIndicator(),
                fn (Collection $collection, string $indicator) => $collection->merge(__('advanced-tables::advanced-tables.forms.preset_view.query_label') . ': ' . $indicator)
            )
            ->toArray();
    }

    protected function getFilterIndicators(): array
    {
        return collect($this->getTable()->getFilters())
            ->mapWithKeys(function (\Filament\Tables\Filters\BaseFilter $filter): array {
                $indicators = [];

                foreach ($filter->getIndicators() as $indicator) {
                    $indicators[] = $indicator->getLabel();
                }

                return [$filter->getName() => $indicators];
            })
            ->filter(fn (array $indicators): bool => count($indicators) > 0)
            ->flatten()
            ->toArray();
    }

    protected function getTableColumnSearchQueryIndicators(): array
    {
        return collect($this->getTableColumnSearches())
            ->map(fn (string $searchQuery, string $column) => $this->getTable()->getColumns()[$column]->getLabel() . ': ' . $searchQuery)
            ->values()
            ->toArray();
    }

    protected function getTableGroupingIndicator(): ?string
    {
        return filled($this->tableGrouping) && $this->tableGrouping !== 'null'
            ? __('filament-tables::table.grouping.fields.group.label') . ': ' . $this->getTable()->getGrouping()->getLabel()
            : null;
    }

    protected function getTableGroupingDirectionIndicator(): ?string
    {
        $direction = $this->getTableGroupingDirection();

        if (blank($direction)) {
            return null;
        }

        return __('filament-tables::table.grouping.fields.direction.label') . ': ' . __('filament-tables::table.grouping.fields.direction.options.' . $direction);
    }

    protected function getTableSortColumnIndicator(): ?string
    {
        if (! $this->tableSortColumnIsValid() || $this->hasMultipleSorts()) {
            return null;
        }

        $column = str($this->tableSort)->before(':')->toString();

        return __('filament-tables::table.sorting.fields.column.label') . ': ' . $this->getTable()->getColumns()[$column]->getLabel();
    }

    protected function getTableSortColumnDirectionIndicator(): ?string
    {
        $direction = $this->getTableSortDirection();

        if (blank($direction)) {
            return null;
        }

        return __('filament-tables::table.sorting.fields.direction.label') . ': ' . __('filament-tables::table.sorting.fields.direction.options.' . $direction);
    }

    protected function getTableMultiSortIndicators(): ?array
    {
        if (blank($this->tableMultiSort) || ! $this->hasMultipleSorts()) {
            return null;
        }

        return collect($this->tableMultiSort['multiSort'])
            ->map(function (array $sortBy) {
                return __('advanced-tables::advanced-tables.multi_sort.label') . ': ' . $this->getTable()->getColumns()[$sortBy['type']]->getLabel() . ' (' . __('filament-tables::table.sorting.fields.direction.options.' . $sortBy['data']['direction']) . ')';
            })
            ->toArray();
    }

    protected function getMergedFilterIndicators(): array
    {
        return collect()
            ->when(
                $this->getActivePresetViewIndicators(),
                fn (Collection $collection, array $indicators) => $collection->merge($indicators)
            )
            ->merge($this->getFilterIndicators())
            ->when(
                $this->getTableColumnSearchQueryIndicators(),
                fn (Collection $collection, array $indicators) => $collection->merge($indicators)
            )
            ->when(
                $this->getTableGroupingIndicator(),
                fn (Collection $collection, string $indicator) => $collection->merge($indicator)
            )
            ->when(
                $this->getTableGroupingDirectionIndicator(),
                fn (Collection $collection, string $indicator) => $collection->merge($indicator)
            )
            ->when(
                $this->tableSearch,
                fn (Collection $collection, string $searchQuery) => $collection->merge([__('filament-tables::table.fields.search.label') . ': ' . $searchQuery])
            )
            ->when(
                $this->getTableSortColumnIndicator(),
                fn (Collection $collection, string $indicator) => $collection->merge($indicator)
            )
            ->when(
                $this->getTableSortColumnDirectionIndicator(),
                fn (Collection $collection, string $indicator) => $collection->merge($indicator)
            )
            ->when(
                $this->getTableMultiSortIndicators(),
                fn (Collection $collection, array $indicators) => $collection->merge($indicators)
            )
            ->toArray();
    }

    protected function saveUserView(array $data): Model
    {
        $existingView = ['name' => $data['name'], 'resource' => $this->getResourceName(), 'user_id' => Config::auth()?->id()];

        if (Config::hasTenancy()) {
            $existingView[Config::getTenantColumn()] = Config::getTenantId();
        }

        $view = Config::getUserView()::updateOrCreate($existingView, [
            'filters' => $this->getFilters(),
            'indicators' => $this->getMergedFilterIndicators(),
            'is_public' => $data['is_public'] ?? false,
            'is_global_favorite' => $data['is_global_favorite'] ?? false,
            'icon' => $data['icon'] ?? null,
            'color' => $data['color'] ?? null,
            'status' => Config::getInitialStatus(),
        ]);

        if ($data['is_managed_by_current_user'] ?? false) {
            $view->userManagedUserViews()
                ->syncWithPivotValues(Config::auth()->id(), [
                    'sort_order' => Config::getUserView()::query()->favoritedByCurrentUser()->count() + 1,
                ], false);
        } else {
            $view->userManagedUserViews()->detach(Config::auth()->id());
        }

        return $view;
    }

    protected function getFilters(): array
    {
        return collect()
            ->when(
                $this->getActivePresetView()?->modifiesQuery(),
                fn (Collection $collection) => $collection->merge(['activeSet' => $this->activePresetView])
            )
            ->when(
                $this->tableFilters,
                fn (Collection $collection, array $filters) => $collection->merge(['tableFilters' => collect($filters)->forget('userView')->filter(fn (?array $filter): bool => collect($filter)?->contains(fn ($value) => ! is_null($value)))])
            )
            ->when(
                $this->tableColumnSearches,
                fn (Collection $collection, array $searchQueries) => $collection->merge(['tableColumnSearchQueries' => $searchQueries])
            )
            ->when(
                $this->tableGrouping,
                fn (Collection $collection, string $grouping) => $collection->merge(['tableGrouping' => $grouping])
            )
            ->when(
                $this->tableSearch,
                fn (Collection $collection, string $searchQuery) => $collection->merge(['tableSearchQuery' => $searchQuery])
            )
            ->when(
                $this->tableSort,
                fn (Collection $collection, string $sort) => $collection->merge(['tableSort' => $sort])
            )
            ->when(
                $this->tableColumns,
                fn (Collection $collection, array $columns) => $collection->merge(['tableColumns' => $columns])
            )
            // ->when(
            //     $this->orderedToggledTableColumns,
            //     fn (Collection $collection, array $columns) => $collection->merge(['orderedToggledTableColumns' => $this->buildOrderedToggledTableColumns($columns)])
            // )
            ->toArray();
    }

    protected function getActivePresetViewLabel(): ?string
    {
        $presetView = $this->getActivePresetView();

        if (! $presetView) {
            return null;
        }

        return $presetView->getLabel() ?? $this->generatePresetViewLabel($this->activePresetView);
    }

    // protected function buildOrderedToggledTableColumns(array $columns): array
    // {
    //     return collect($columns)
    //         ->map(function ($isVisible, $column) {
    //             return [
    //                 'column' => $column,
    //                 'isVisible' => $isVisible,
    //             ];
    //         })
    //         ->values()
    //         ->toArray();
    // }

    protected function tableSortColumnIsValid(): bool
    {
        if (blank($this->tableSort) || $this->tableSort === 'null') {
            return false;
        }

        $column = str($this->tableSort)->before(':')->toString();

        return isset($this->getTable()->getColumns()[$column]);
    }

    protected function hasMultipleSorts(): bool
    {
        return count($this->tableMultiSort['multiSort'] ?? []) > 1;
    }
}
