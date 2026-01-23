<?php

namespace Raison\FilamentStarter\Filament\Resources\SystemStatusResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Raison\FilamentStarter\Filament\Resources\SystemStatusResource;
use Raison\FilamentStarter\Support\Doctor;

class ListSystemStatuses extends ListRecords
{
    protected static string $resource = SystemStatusResource::class;

    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        return null;
    }

    protected function getTableData(): array
    {
        return app(Doctor::class)->check();
    }
}
