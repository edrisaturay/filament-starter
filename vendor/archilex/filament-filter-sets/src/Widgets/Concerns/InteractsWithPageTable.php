<?php

namespace Archilex\AdvancedTables\Widgets\Concerns;

use Filament\Tables\Contracts\HasTable;
use Filament\Widgets\Concerns\InteractsWithPageTable as FilamentInteractsWithPageTable;
use Livewire\Attributes\Reactive;

use function Livewire\trigger;

trait InteractsWithPageTable
{
    use FilamentInteractsWithPageTable;

    #[Reactive]
    public ?string $activePresetView = null;

    #[Reactive]
    public ?string $currentPresetView = null;

    protected function getTablePageInstance(): HasTable
    {
        if (isset($this->tablePage)) {
            return $this->tablePage;
        }

        /** @var HasTable $tableComponent */
        $page = app('livewire')->new($this->getTablePage());

        trigger('mount', $page, [], null, null);

        foreach ([
            'activePresetView' => $this->activePresetView,
            'currentPresetView' => $this->currentPresetView,
            'activeTab' => $this->activeTab,
            'paginators' => $this->paginators,
            'parentRecord' => $this->parentRecord,
            'tableColumnSearches' => $this->tableColumnSearches,
            'tableFilters' => $this->tableFilters,
            'tableGrouping' => $this->tableGrouping,
            'tableRecordsPerPage' => $this->tableRecordsPerPage,
            'tableSearch' => $this->tableSearch,
            'tableSort' => $this->tableSort,
            ...$this->getTablePageMountParameters(),
        ] as $property => $value) {
            $page->{$property} = $value;
        }

        $page->bootedInteractsWithTable();

        return $this->tablePage = $page;
    }
}
