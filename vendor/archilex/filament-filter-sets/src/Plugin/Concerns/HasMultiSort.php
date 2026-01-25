<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Closure;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Enums\IconPosition;

trait HasMultiSort
{
    use EvaluatesClosures;

    protected bool | Closure $multiSortIsEnabled = true;

    protected string | Closure $multiSortTablePosition = 'tables::toolbar.search.after';

    protected string | Closure $multiSortIcon = 'heroicon-s-arrows-up-down';

    protected string | IconPosition | Closure $multiSortIconPosition = IconPosition::Before;

    protected bool | Closure $showMultiSortAsButton = false;

    protected string | Closure $multiSortButtonLabel = 'Sorting';

    protected string | Closure $multiSortButtonSize = 'md';

    protected bool | Closure $showMultiSortButtonOutlined = false;

    protected bool | Closure $hasMultiSortBadge = true;

    public function multiSortEnabled(bool | Closure $condition = true): static
    {
        $this->multiSortIsEnabled = $condition;

        return $this;
    }

    public function multiSortButton(bool | Closure $condition = true, string | Closure $label = 'Sorting'): static
    {
        $this->showMultiSortAsButton = $condition;

        $this->multiSortButtonLabel = $label;

        return $this;
    }

    public function multiSortButtonSize(string | Closure $size = 'md'): static
    {
        $this->multiSortButtonSize = $size;

        return $this;
    }

    public function multiSortButtonOutlined(bool | Closure $condition = true): static
    {
        $this->showMultiSortButtonOutlined = $condition;

        return $this;
    }

    public function multiSortIcon(string | Closure $icon): static
    {
        $this->multiSortIcon = $icon;

        return $this;
    }

    public function multiSortIconPosition(string | IconPosition | Closure $position = IconPosition::Before): static
    {
        $this->multiSortIconPosition = $position;

        return $this;
    }

    public function multiSortBadge(bool | Closure $condition = true): static
    {
        $this->hasMultiSortBadge = $condition;

        return $this;
    }

    public function multiSortIsEnabled(): bool
    {
        return $this->evaluate($this->multiSortIsEnabled);
    }

    public function multiSortTablePosition(): string
    {
        return $this->evaluate($this->multiSortTablePosition);
    }

    public function showMultiSortAsButton(): bool
    {
        return $this->evaluate($this->showMultiSortAsButton);
    }

    public function getMultiSortButtonLabel(): string
    {
        return $this->evaluate($this->multiSortButtonLabel);
    }

    public function getMultiSortButtonSize(): string
    {
        return $this->evaluate($this->multiSortButtonSize);
    }

    public function showMultiSortButtonOutlined(): bool
    {
        return $this->evaluate($this->showMultiSortButtonOutlined);
    }

    public function hasMultiSortBadge(): bool
    {
        return $this->evaluate($this->hasMultiSortBadge);
    }

    public function getMultiSortIcon(): string
    {
        return $this->evaluate($this->multiSortIcon);
    }

    public function getMultiSortIconPosition(): string | IconPosition | null
    {
        return $this->evaluate($this->multiSortIconPosition);
    }
}
