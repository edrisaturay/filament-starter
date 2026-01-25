<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Closure;
use Filament\Support\Concerns\EvaluatesClosures;

trait HasSupport
{
    use EvaluatesClosures;

    protected bool | Closure $convertsIcons = false;

    protected bool | Closure $usesMinimalTheme = false;

    public function convertIcons(bool | Closure $condition = true): static
    {
        $this->convertsIcons = $condition;

        return $this;
    }

    public function minimalTheme(bool | Closure $condition = true): static
    {
        $this->usesMinimalTheme = $condition;

        return $this;
    }

    public function convertsIcons(): bool
    {
        return $this->evaluate($this->convertsIcons);
    }

    public function usesMinimalTheme(): bool
    {
        return $this->evaluate($this->usesMinimalTheme);
    }
}
