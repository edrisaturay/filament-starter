<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Closure;
use Filament\Support\Concerns\EvaluatesClosures;

trait HasQuickFilters
{
    use EvaluatesClosures;

    protected bool | Closure $quickFiltersAreEnabled = false;

    protected int | Closure | null $defaultIndicatorLabelsLimit = null;

    public function quickFiltersEnabled(bool | Closure $condition = true): static
    {
        $this->quickFiltersAreEnabled = $condition;

        return $this;
    }

    public function defaultIndicatorLabelsLimit(int | Closure $limit): static
    {
        $this->defaultIndicatorLabelsLimit = $limit;

        return $this;
    }

    public function quickFiltersAreEnabled(): bool
    {
        return $this->evaluate($this->quickFiltersAreEnabled);
    }

    public function getDefaultIndicatorLabelsLimit(): ?int
    {
        return $this->evaluate($this->defaultIndicatorLabelsLimit);
    }
}
