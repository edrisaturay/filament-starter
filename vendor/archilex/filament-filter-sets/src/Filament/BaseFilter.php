<?php

namespace Archilex\AdvancedTables\Filament;

use Archilex\AdvancedTables\Filament\Concerns\CanBeFavorite;
use Filament\Tables\Filters\BaseFilter as FilamentBaseFilter;

class BaseFilter extends FilamentBaseFilter
{
    use CanBeFavorite;
}
