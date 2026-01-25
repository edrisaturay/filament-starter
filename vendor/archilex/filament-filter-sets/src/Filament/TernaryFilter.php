<?php

namespace Archilex\AdvancedTables\Filament;

use Archilex\AdvancedTables\Filament\Concerns\CanBeFavorite;
use Filament\Tables\Filters\TernaryFilter as FilamentTernaryFilter;

class TernaryFilter extends FilamentTernaryFilter
{
    use CanBeFavorite;
}
