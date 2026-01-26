<?php

namespace EdrisaTuray\FilamentStarter\Filament\Resources\SystemStatusResource\Pages;

use EdrisaTuray\FilamentStarter\Filament\Resources\SystemStatusResource;
use EdrisaTuray\FilamentStarter\Support\Doctor;
use Filament\Resources\Pages\ListRecords;

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
