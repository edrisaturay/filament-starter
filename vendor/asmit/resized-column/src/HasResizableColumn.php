<?php

namespace Asmit\ResizedColumn;

use Asmit\ResizedColumn\Setup\Concerns\LoadResizedColumn;

trait HasResizableColumn
{
    use LoadResizedColumn;

    public function bootedHasResizableColumn(): void
    {
        $this->loadColumnWidths();

        foreach ($this->getCurrentTableColumns() as $columnName => $column) {
            $this->applyExtraAttributes($columnName, $column);
        }
    }
}
