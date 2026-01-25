<?php

namespace Archilex\AdvancedTables\Components\Concerns;

use Closure;

trait CanBeDefault
{
    protected bool | Closure $isDefault = false;

    protected bool $isCurrentDefault = false;

    public function default(bool | Closure $condition = true): static
    {
        $this->isDefault = $condition;

        return $this;
    }

    public function currentDefault(bool $condition = true): static
    {
        $this->isCurrentDefault = $condition;

        return $this;
    }

    public function isDefault(): bool
    {
        return (bool) $this->evaluate($this->isDefault);
    }

    public function isCurrentDefault(): bool
    {
        return (bool) $this->isCurrentDefault;
    }
}
