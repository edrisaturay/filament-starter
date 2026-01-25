<?php

namespace Asmit\ResizedColumn\Setup;

use Asmit\ResizedColumn\Setup\Concerns\CanResizedColumn;

class Setup
{
    use CanResizedColumn;

    public static function resizedColumnPlugged(): bool
    {
        return filament()->hasPlugin('asmit-resized-column') && filament()->getCurrentPanel();
    }
}
