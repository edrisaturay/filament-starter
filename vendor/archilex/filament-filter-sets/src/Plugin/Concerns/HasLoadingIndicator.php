<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Closure;
use Filament\Support\Concerns\EvaluatesClosures;

trait HasLoadingIndicator
{
    use EvaluatesClosures;

    protected bool | Closure $favoritesBarHasLoadingIndicator = false;

    protected bool | Closure $tableHasLoadingOverlay = false;

    public function favoritesBarLoadingIndicator(bool | Closure $condition = true): static
    {
        $this->favoritesBarHasLoadingIndicator = $condition;

        return $this;
    }

    public function tableLoadingOverlay(bool | Closure $condition = true): static
    {
        $this->tableHasLoadingOverlay = $condition;

        return $this;
    }

    public function favoritesBarHasLoadingIndicator(): bool
    {
        return $this->evaluate($this->favoritesBarHasLoadingIndicator);
    }

    public function tableHasLoadingOverlay(): bool
    {
        return $this->evaluate($this->tableHasLoadingOverlay);
    }
}
