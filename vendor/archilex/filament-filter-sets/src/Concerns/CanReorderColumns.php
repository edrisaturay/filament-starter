<?php

namespace Archilex\AdvancedTables\Concerns;

use Archilex\AdvancedTables\Support\Config;
use Filament\Forms\Components\Checkbox;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Column;

trait CanReorderColumns
{
    protected array $defaultTableColumnOrder = [];

    protected array $orderedToggledTableColumns = [];

    protected bool $loadingView = false;

    public function applyTableColumns(?array $tableColumns = null): void
    {
        $customTableColumnState = $tableColumns ?? $this->getTableColumnsFromPresetView();

        if ($customTableColumnState) {
            $this->resetActiveViews = false;
            $this->loadingView = true;

            // Reset the cache so that `mapTableColumnToArray()` will be
            // executed again and toggle off any new toggleable columns.
            $this->cachedDefaultTableColumnState = null;

            $this->applyTableColumnManager($customTableColumnState);

            return;
        }

        $this->resetTableColumnManager();
    }

    protected function hasReorderedTableColumns(): bool
    {
        if ($this->loadingView) {
            return true;
        }

        return parent::hasReorderedTableColumns();
    }

    protected function mapTableColumnToArray(Column $column): array
    {
        $toggled = $this->loadingView
            ? ! $column->isToggleable()
            : ! $column->isToggleable() || ! $column->isToggledHiddenByDefault();

        return [
            'type' => 'column',
            'name' => $column->getName(),
            'label' => (string) $column->getLabel(),
            'isHidden' => $column->isHidden(),
            'isToggled' => $toggled,
            'isToggleable' => $column->isToggleable(),
            'isToggledHiddenByDefault' => $column->isToggleable() ? $column->isToggledHiddenByDefault() : null,
        ];
    }

    // public function applyToggledTableColumnsOrder(): void
    // {
    //     if (! $this->canReorderColumns() || $this->getTable()->hasColumnsLayout()) {
    //         return;
    //     }

    //     $reorderedColumns = collect($this->orderedToggledTableColumns)
    //         ->merge($this->getTable()->getColumns())
    //         ->filter(fn ($column) => $column instanceof Column)
    //         ->toArray();

    //     $this->getTable()->columns($reorderedColumns);

    //     $this->persistOrderedTableColumnsToSession();
    // }

    // public function toggleOnAllTableColumns(): void
    // {
    //     $this->toggledTableColumns = $this->recursivelySetAllToTrue($this->toggledTableColumns);

    //     $this->orderedToggledTableColumns = $this->toggledTableColumns;

    //     $this->updatedToggledTableColumns();
    // }

    // public function reorderTableColumns(array $columns): void
    // {
    //     $this->orderedToggledTableColumns = collect($columns)
    //         ->mapWithKeys(function ($column) {
    //             return [$column => true];
    //         })
    //         ->union(
    //             collect($this->orderedToggledTableColumns)
    //                 ->mapWithKeys(function ($value, $column) {
    //                     return [$column => false];
    //                 })
    //         )
    //         ->toArray();

    //     $this->applyToggledTableColumnsOrder();

    //     $this->saveModifiedDefaultPresetViewColumnsToSession();

    //     $this->resetActiveViewsIfRequired();
    // }

    // public function getOrderedTableColumnToggleFormStateSessionKey(): string
    // {
    //     $table = class_basename($this::class);

    //     return "tables.{$table}_ordered_toggled_columns";
    // }

    // public function getTableColumnToggleForm(): \Filament\Schemas\Schema
    // {
    //     if (! $this->canReorderColumns()) {
    //         return parent::getTableColumnToggleForm();
    //     }

    //     return $this->makeSchema()
    //         ->schema($this->getTableColumnToggleFormSchema())
    //         ->columns($this->getTable()->getColumnToggleFormColumns())
    //         ->statePath('toggledTableColumns')
    //         ->live();
    // }

    // public function setDefaultToggledTableColumnsOrder(): void
    // {
    //     $this->defaultTableColumnOrder = collect($this->getTable()->getColumns())
    //         ->filter(fn ($column) => $column->isVisible())
    //         ->mapWithKeys(fn ($column) => [$column->getName() => ! $column->isToggledHiddenByDefault()])
    //         ->toArray();
    // }

    // public function getDefaultToggledTableColumnsOrder(): array
    // {
    //     return $this->defaultTableColumnOrder;
    // }

    // public function persistOrderedTableColumnsToSession(): void
    // {
    //     session()->put([
    //         $this->getOrderedTableColumnToggleFormStateSessionKey() => $this->orderedToggledTableColumns,
    //     ]);
    // }

    // protected function getDefaultTableColumnToggleState(): array
    // {
    //     if (! $this->canReorderColumns()) {
    //         return parent::getDefaultTableColumnToggleState();
    //     }

    //     $state = [];

    //     foreach ($this->getTable()->getColumns() as $column) {
    //         if ($column->isHidden()) {
    //             continue;
    //         }

    //         if (! $column->isToggleable()) {
    //             data_set($state, $column->getName(), true);

    //             continue;
    //         }

    //         data_set($state, $column->getName(), ! $column->isToggledHiddenByDefault());
    //     }

    //     return $state;
    // }

    // protected function getTableColumnToggleFormSchema(): array
    // {
    //     if (! $this->canReorderColumns()) {
    //         return parent::getTableColumnToggleFormSchema();
    //     }

    //     $schema = [];

    //     foreach ($this->getTable()->getColumns() as $column) {
    //         if ($column->isHidden()) {
    //             continue;
    //         }

    //         if (! $column->isToggleable()) {
    //             $schema[] = Checkbox::make($column->getName())
    //                 ->disabled()
    //                 ->label($column->getLabel());

    //             continue;
    //         }

    //         $schema[] = Checkbox::make($column->getName())
    //             ->label($column->getLabel());
    //     }

    //     return [
    //         \Filament\Schemas\Components\View::make('advanced-tables::forms.components.columns')
    //             ->schema($schema)
    //             ->columnSpanFull(),
    //     ];
    // }

    // protected function recursivelySetAllToTrue($array)
    // {
    //     foreach ($array as $key => $value) {
    //         $array[$key] = is_array($value) ? $this->recursivelySetAllToTrue($value) : true;
    //     }

    //     return $array;
    // }

    // public function canReorderColumns(): bool
    // {
    //     return Config::reorderableColumnsAreEnabled();
    // }
}
