<?php

namespace EdrisaTuray\FilamentStarter\Filament\Resources\PanelPluginOverrideResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use EdrisaTuray\FilamentStarter\Filament\Resources\PanelPluginOverrideResource;

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
