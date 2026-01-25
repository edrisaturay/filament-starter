<?php

namespace Archilex\AdvancedTables\Forms\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Support\Enums\Size;
use Livewire\Component;

class MultiSortBuilder extends Builder
{
    protected string | Closure | null $blockPickerMaxHeight = null;

    protected bool | Closure $blockPickerHasSearch = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generateUuidUsing(false);

        $this->addAction(
            fn (Action $action) => $action
                ->after(function (Component $livewire) {
                    $livewire->updatedTableMultiSort();
                })
        );

        $this->deleteAction(
            fn (Action $action) => $action
                ->action(function (array $arguments, Builder $component, Component $livewire): void {
                    $items = $component->getState();
                    unset($items[$arguments['item']]);

                    $items = array_values($items);

                    $component->state($items);

                    $component->callAfterStateUpdated();

                    $livewire->updatedTableMultiSort();
                })
        );

        $this->reorderAction(
            fn (Action $action) => $action
                ->action(function (array $arguments, Builder $component, Component $livewire): void {
                    $items = array_values(array_map(
                        fn (int $index) => $component->getState()[$index],
                        $arguments['items']
                    ));

                    $component->state($items);

                    $component->callAfterStateUpdated();

                    $livewire->updatedTableMultiSort();
                })
        );

        $this->registerActions([
            fn (Builder $component): Action => $component->getSortAscendingAction(),
            fn (Builder $component): Action => $component->getSortDescendingAction(),
        ]);
    }

    public function getView(): string
    {
        return 'advanced-tables::forms.components.multi-sort-builder';
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

    public function getBlockPickerMaxHeight(): ?string
    {
        return $this->evaluate($this->blockPickerMaxHeight);
    }

    public function blockPickerHasSearch(): bool
    {
        return $this->evaluate($this->blockPickerHasSearch);
    }

    public function getSortAscendingAction(): Action
    {
        return Action::make('sortAscending')
            ->iconButton()
            ->icon('heroicon-s-arrow-up')
            ->label(__('filament-tables::table.sorting.fields.direction.options.asc'))
            ->color(function (array $arguments, Builder $component) {
                $items = $component->getState();

                return data_get($items, $arguments['item'] . '.data.direction') === 'asc' ? 'primary' : 'gray';
            })
            ->action(function (array $arguments, Builder $component, Component $livewire): void {
                $uuid = $arguments['item'];

                $items = $component->getState();

                $items[$uuid]['data']['direction'] = 'asc';

                $component->state($items);

                $livewire->updatedTableMultiSort();
            })
            ->size(Size::Small);
    }

    public function getSortDescendingAction(): Action
    {
        return Action::make('sortDescending')
            ->iconButton()
            ->icon('heroicon-s-arrow-down')
            ->label(__('filament-tables::table.sorting.fields.direction.options.desc'))
            ->color(function (array $arguments, Builder $component) {
                $items = $component->getState();

                return data_get($items, $arguments['item'] . '.data.direction') === 'desc' ? 'primary' : 'gray';
            })
            ->action(function (array $arguments, Builder $component, Component $livewire): void {
                $uuid = $arguments['item'];

                $items = $component->getState();

                $items[$uuid]['data']['direction'] = 'desc';

                $component->state($items);

                $livewire->updatedTableMultiSort();
            })
            ->size(Size::Small);
    }
}
