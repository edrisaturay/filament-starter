<?php

namespace Archilex\AdvancedTables\Filament;

use Archilex\AdvancedTables\Filament\Concerns\CanBeFavorite;
use Filament\Tables\Filters\Filter as FilamentFilter;

class Filter extends FilamentFilter
{
    use CanBeFavorite;
}
