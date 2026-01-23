<?php

namespace Raison\FilamentStarter\Filament\Resources\PanelPluginOverrideResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Raison\FilamentStarter\Filament\Resources\PanelPluginOverrideResource;

class ListPanelPluginOverrides extends ListRecords
{
    protected static string $resource = PanelPluginOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
