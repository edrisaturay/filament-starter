<?php

namespace EdrisaTuray\FilamentStarter\Filament\Resources\PanelPluginOverrideResource\Pages;

use EdrisaTuray\FilamentStarter\Filament\Resources\PanelPluginOverrideResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPanelPluginOverride extends EditRecord
{
    protected static string $resource = PanelPluginOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
