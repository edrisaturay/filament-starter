<?php

namespace Archilex\AdvancedTables\Filament;

use Archilex\AdvancedTables\Filament\Concerns\CanBeFavorite;
use Filament\Tables\Filters\TrashedFilter as FilamentTrashedFilter;

class TrashedFilter extends FilamentTrashedFilter
{
    use CanBeFavorite;
}
