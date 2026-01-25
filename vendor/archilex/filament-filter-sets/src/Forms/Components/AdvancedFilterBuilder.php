<?php

namespace Archilex\AdvancedTables\Forms\Components;

use Archilex\AdvancedTables\Filters\Concerns\HasFiltersLayout;
use Closure;
use Filament\Forms\Components\Builder;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Support\Str;
use Livewire\Component;

class AdvancedFilterBuilder extends Builder
{
    use HasFiltersLayout;

    protected $filters = [];

    protected string | Closure | null $blockPickerMaxHeight = null;

    protected bool | Closure $blockPickerHasSearch = false;

    protected bool $hasOrGroups = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(static function (Builder $component, ?array $state): void {
            $items = [];

            foreach ($state ?? [] as $itemData) {
                $items[(string) Str::random(3)] = $itemData;
            }

            $component->state($items);
        });

        $this->registerActions([
            fn (Builder $component): \Filament\Actions\Action => $component->getAddAction(),
            fn (Builder $component): \Filament\Actions\Action => $component->getDeleteAction(),
            fn (Builder $component): \Filament\Actions\Action => $component->getDeleteIconAction(),
            fn (Builder $component): \Filament\Actions\Action => $component->getExpandViewAction(),
        ]);
    }

    /**
     * @return view-string
     */
    public function getView(): string
    {
        return 'advanced-tables::forms.components.advanced-filter-builder';
    }

    public function getAddAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('add')
            ->label(fn (Builder $component) => $component->getAddActionLabel())
            ->color('gray')
            ->action(function (array $arguments, Builder $component): void {
                $items = $component->getRawState();

                $newUuid = (string) Str::random(3);

                $items[$newUuid] = [
                    'type' => $arguments['block'],
                    'data' => [],
                ];

                $component->rawState($items);

                $component->getChildSchema($newUuid)->fill();

                if (! $this->hasOrGroups()) {
                    return;
                }

                $parentUuid = Str::of($component->getStatePath())
                    ->between('advanced_filter_builder.or_group.', '.data.and_group')
                    ->toString();

                $orGroupState = $component->getContainer()->getParentComponent()->getContainer()->getParentComponent()->getRawState();

                if (
                    $arguments['block'] !== 'filter_group' &&
                    $arguments['block'] !== 'and_group' &&
                    count($orGroupState[$parentUuid]['data']['and_group']) === 1
                ) {
                    $newOrGroupUuid = (string) Str::random(3);

                    $orGroupState[$newOrGroupUuid] = [
                        'type' => 'filter_group',
                        'data' => [],
                    ];

                    $component->getContainer()->getParentComponent()->getContainer()->getParentComponent()->rawState($orGroupState);
                    $component->getContainer()->getParentComponent()->getContainer()->getParentComponent()->getChildSchema($newOrGroupUuid)->fill();
                }
            })
            ->livewireClickHandlerEnabled(false)
            ->button()
            ->size(\Filament\Support\Enums\Size::Small);
    }

    public function getDeleteAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('delete')
            ->label(__('advanced-tables::filter-builder.form.remove_filter'))
            ->link()
            ->color('danger')
            ->action(function (array $arguments, Builder $component, $livewire): void {
                $this->removeFilter($arguments, $component, $livewire);

                if ($this->hasOrGroups()) {
                    return;
                }

                $orGroupState = $component->getContainer()->getParentComponent()->getContainer()->getParentComponent()->getRawState();

                if (empty($orGroupState)) {
                    $newOrGroupUuid = (string) Str::random(3);

                    $orGroupState[$newOrGroupUuid] = [
                        'type' => 'filter_group',
                        'data' => [],
                    ];

                    $component->getContainer()->getParentComponent()->getContainer()->getParentComponent()->rawState($orGroupState);
                    $component->getContainer()->getParentComponent()->getContainer()->getParentComponent()->getChildSchema($newOrGroupUuid)->fill();
                }
            })
            ->size(\Filament\Support\Enums\Size::Small);
    }

    public function getDeleteIconAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('deleteIcon')
            ->iconButton()
            ->icon('heroicon-s-trash')
            ->color('danger')
            ->action(function (array $arguments, Builder $component, $livewire): void {
                $this->removeFilter($arguments, $component, $livewire);
            })
            ->size(\Filament\Support\Enums\Size::Small);
    }

    public function getExpandViewAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('expandView')
            ->label(__('advanced-tables::filter-builder.form.expand_view'))
            ->link()
            ->color('primary')
            ->fillForm(fn (Component $livewire) => $livewire->getTable()->hasDeferredFilters() ? $livewire->tableDeferredFilters : $livewire->tableFilters)
            ->schema(fn (Component $livewire) => $livewire->getTable()->getFiltersForm())
            ->slideOver()
            ->modalHeading(__('filament-tables::table.filters.heading'))
            ->modalWidth('md')
            ->modalSubmitAction(false)
            ->extraModalFooterActions([
                \Filament\Actions\Action::make('resetFilters')
                    ->label(__('filament-tables::table.filters.actions.reset.label'))
                    ->color('danger')
                    ->action('resetTableFiltersForm'),
            ])
            ->modalCancelActionLabel(__('filament::components/modal.actions.close.label'))
            ->size(\Filament\Support\Enums\Size::Small);
    }

    public function defaultFilters(array | Closure $filters): static
    {
        $this->default(static function (AdvancedFilterBuilder $component) use ($filters): array {
            $filters = $component->evaluate($filters);

            if (! $filters) {
                return [[
                    'type' => 'filter_group',
                ]];
            }

            $defaultFilters = [];

            foreach ($filters as $index => $orGroup) {
                $defaultFilters[$index] = [
                    'type' => 'filter_group',
                ];

                foreach ($orGroup as $filter) {
                    $defaultFilters[$index]['data']['and_group'][] = [
                        'type' => $filter,
                        'data' => null,
                    ];
                }
            }

            return $defaultFilters;
        });

        return $this;
    }

    public function blockPickerMaxHeight(string | Closure | null $height): static
    {
        $this->blockPickerMaxHeight = $height;

        return $this;
    }

    public function blockPickerSearch(bool | Closure $condition): static
    {
        $this->blockPickerHasSearch = $condition;

        return $this;
    }

    public function orGroups(bool $condition): static
    {
        $this->hasOrGroups = $condition;

        return $this;
    }

    public function getBlockPickerMaxHeight(): ?string
    {
        return $this->evaluate($this->blockPickerMaxHeight);
    }

    public function blockPickerHasSearch(): bool
    {
        return $this->evaluate($this->blockPickerHasSearch);
    }

    public function hasWideFilterLayout(): bool
    {
        return $this->hasWideLayout($this->getLivewire());
    }

    public function hasOrGroups(): bool
    {
        return $this->hasOrGroups;
    }

    public function isModalLayout(): bool
    {
        return $this->getLivewire()->getTable()->getFiltersLayout() === FiltersLayout::Modal;
    }

    protected function removeFilter(array $arguments, Builder $component, $livewire): void
    {
        $uuid = $arguments['item'];

        $parentUuid = Str::of($component->getStatePath())
            ->between('advanced_filter_builder.or_group.', '.data.and_group')
            ->toString();

        $items = $component->getRawState();

        unset($items[$uuid]);

        $component->rawState($items);

        $livewire->resetActiveViewsIfRequired();

        invade($livewire)->handleTableFilterUpdates();

        $orGroupState = $component->getContainer()->getParentComponent()->getContainer()->getParentComponent()->getRawState();

        if (empty($orGroupState[$parentUuid]['data']['and_group'])) {
            unset($orGroupState[$parentUuid]);
            $component->getContainer()->getParentComponent()->getContainer()->getParentComponent()->rawState($orGroupState);
        }
    }

    /**
     * Override to fix state path for deferred filters to prevent path switching during polling
     */
    public function getStatePath(bool $isAbsolute = true): ?string
    {
        if (! $isAbsolute) {
            return parent::getStatePath($isAbsolute);
        }

        $path = parent::getStatePath($isAbsolute);

        if ($path && $this->getLivewire()->getTable()->hasDeferredFilters()) {
            if (str_starts_with($path, 'tableFilters.')) {
                $path = 'tableDeferredFilters.' . substr($path, strlen('tableFilters.'));

                $this->cachedAbsoluteStatePath = $path;
            }
        }

        return $path;
    }
}
