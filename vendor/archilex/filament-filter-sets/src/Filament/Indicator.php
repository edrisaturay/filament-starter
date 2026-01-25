<?php

namespace Archilex\AdvancedTables\Filament;

use Closure;
use Filament\Tables\Filters\Indicator as FilamentIndicator;
use Illuminate\Contracts\Support\Htmlable;

class Indicator extends FilamentIndicator
{
    protected bool $isAdvanced = true;

    protected bool $isActive = true;

    protected ?string $filterName = null;

    protected ?string $quickFilterName = null;

    protected array | Closure | null $labels = null;

    protected int | Closure | null $labelsLimit = null;

    public function advanced(bool $condition = true): static
    {
        $this->isAdvanced = $condition;

        return $this;
    }

    public function active(bool $condition = true): static
    {
        $this->isActive = $condition;

        return $this;
    }

    public function quickFilterName(string $name): static
    {
        $this->quickFilterName = $name;

        return $this;
    }

    public function filterName(string $name): static
    {
        $this->filterName = $name;

        return $this;
    }

    public function labels(array | Closure $labels): static
    {
        $this->labels = $labels;

        return $this;
    }

    public function limitLabels(int | Closure | null $limit = 3): static
    {
        $this->labelsLimit = $limit;

        return $this;
    }

    public function isAdvanced(): bool
    {
        return $this->isAdvanced;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getFilterName(): ?string
    {
        return $this->filterName;
    }

    public function getQuickFilterName(): ?string
    {
        return $this->quickFilterName;
    }

    public function getLabels(): ?array
    {
        return $this->evaluate($this->labels);
    }

    public function getLabelsLimit(): ?int
    {
        return $this->evaluate($this->labelsLimit);
    }

    public function getLabel(): string | Htmlable
    {
        $label = $this->evaluate($this->label);
        $labels = $this->getLabels();
        $labelsLimit = $this->getLabelsLimit();

        if (! $labels) {
            return $label;
        }

        $formattedLabel = $this->formatLabel($label, $labels);

        if (! $labelsLimit) {
            return $formattedLabel;
        }

        $limitedLabels = array_slice($labels, 0, $labelsLimit);
        $limitedLabelsCount = count($labels) - count($limitedLabels);

        return $limitedLabelsCount > 0
            ? $this->formatLimitedLabel($label, $limitedLabels, $limitedLabelsCount)
            : $formattedLabel;
    }

    protected function formatLabel(string $label, array $labels): string
    {
        return $label . ': ' . collect($labels)->join(', ', ' & ');
    }

    protected function formatLimitedLabel(string $label, array $limitedLabels, int $limitedLabelsCount): string
    {
        return $label . ': ' . collect($limitedLabels)->join(', ') . ' ' . trans_choice('advanced-tables::advanced-tables.quick_filters.more_indicator_labels', $limitedLabelsCount);
    }
}
