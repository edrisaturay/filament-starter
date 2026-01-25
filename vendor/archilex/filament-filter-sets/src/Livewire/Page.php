<?php

namespace Archilex\AdvancedTables\Livewire;

use Filament\Pages\Page as FilamentPage;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

class Page extends FilamentPage implements HasTable
{
    use InteractsWithTable;
}
