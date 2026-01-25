<?php

namespace Archilex\AdvancedTables\Components\Concerns;

trait HasName
{
    protected string | int $name;

    public function name(string | int $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string | int
    {
        return $this->name;
    }
}
