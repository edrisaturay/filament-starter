<?php

namespace Archilex\AdvancedTables\Concerns;

use Archilex\AdvancedTables\Forms\Components\MultiSortBuilder;
use Archilex\AdvancedTables\Support\Config;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Column;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;

trait HasMultiSort
{
    #[Locked]
    public ?array $tableMultiSort = null;

    public static function multiSortIsEnabled(): bool
    {
        return Config::multiSortIsEnabled();
    }

    public function cacheMultiSortForm(): void
    {
        if (! static::multiSortIsEnabled()) {
            return;
        }

        $this->cacheSchema('tableMultiSortForm', $this->getTableMultiSortForm());
    }

    public function getTableMultiSortForm(): Schema
    {
        if ((! $this->isCachingSchemas) && $this->hasCachedSchema('tableMultiSortForm')) {
            return $this->getSchema('tableMultiSortForm');
        }

        return $this->makeSchema()
            ->schema([
                MultiSortBuilder::make('multiSort')
                    ->hiddenLabel()
                    ->blockNumbers(false)
                    ->blockPickerSearch(true)
                    ->blockPickerColumns(2)
                    ->blocks(function () {
                        return collect($this->getTable()->getColumns())
                            ->filter(function (Column $column) {
                                return $column->isSortable();
                            })
                            ->sortBy(function (Column $column) {
                                return $column->getLabel();
                            })
                            ->map(function (Column $column) {
                                return Block::make($column->getName())
                                    ->label($column->getLabel())
                                    ->maxItems(1)
                                    ->schema([
                                        Hidden::make('direction')
                                            ->default('asc'),
                                    ]);
                            })
                            ->toArray();
                    })
                    ->addActionLabel(__('advanced-tables::advanced-tables.multi_sort.add_column_label')),
            ])
            ->model($this->getTable()->getModel())
            ->statePath('tableMultiSort')
            ->live();
    }

    public function updatedTableMultiSort(): void
    {
        $multiSortBy = $this->tableMultiSort['multiSort'] ?? [];

        $this->tableSort = collect($multiSortBy)
            ->map(function (array $sortBy) {
                $column = $sortBy['type'] ?? null;

                if (! $column) {
                    return null;
                }

                $direction = $sortBy['data']['direction'] ?? 'asc';

                return "{$column}:{$direction}";
            })
            ->filter()
            ->join(',');

        $this->resetActiveViewsIfRequired();

        if ($this->getTable()->persistsSortInSession()) {
            session()->put(
                $this->getTableSortSessionKey(),
                $this->tableSort,
            );
        }

        $this->resetPage();
    }

    public function hasSortableColumns(): bool
    {
        foreach ($this->getTable()->getColumns() as $column) {
            if (! $column->isSortable()) {
                continue;
            }

            return true;
        }

        return false;
    }

    protected function applyMultiSort(): void
    {
        if (! static::multiSortIsEnabled()) {
            return;
        }

        $this->tableMultiSort = [
            'multiSort' => $this->getParsedTableSort(),
        ];

        $this->getTableMultiSortForm()->fill($this->tableMultiSort);
    }

    protected function applySortingToTableQuery(Builder $query): Builder
    {
        $multiSortBy = $this->tableMultiSort['multiSort'] ?? [];

        if (blank($multiSortBy)) {
            return parent::applySortingToTableQuery($query);
        }

        foreach ($multiSortBy as $sortBy) {
            $multiSortColumn = $sortBy['type'] ?? null;

            if (! $multiSortColumn) {
                continue;
            }

            $column = $this->getTable()->getSortableVisibleColumn($multiSortColumn);

            if (! $column) {
                continue;
            }

            $sortDirection = $sortBy['data']['direction'] === 'desc' ? 'desc' : 'asc';

            $column->applySort($query, $sortDirection);
        }

        if (count($multiSortBy) > 1) {
            $this->tableSortColumn = null;
        }

        return $query;
    }

    protected function getParsedTableSort(): array
    {
        $result = [];
        $sorts = explode(',', (string) $this->tableSort);

        foreach ($sorts as $sort) {
            $exploded = explode(':', trim($sort), 2);

            if (count($exploded) !== 2) {
                continue;
            }

            [$column, $direction] = $exploded;
            $direction = strtolower(trim($direction));

            $result[] = [
                'type' => trim($column),
                'data' => [
                    'direction' => in_array($direction, ['asc', 'desc']) ? $direction : 'asc',
                ],
            ];
        }

        return $result;
    }
}
