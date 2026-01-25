<?php

namespace Archilex\AdvancedTables\Components\Concerns;

use Closure;

trait CanPreserve
{
    protected bool | Closure $shouldPreserveFilters = false;

    protected bool | Closure $shouldPreserveGrouping = false;

    protected bool | Closure $shouldPreserveGroupingDirection = false;

    protected bool | Closure $shouldPreserveSort = false;

    protected bool | Closure $shouldPreserveColumns = false;

    public function preserveAll(bool | Closure $condition = true): static
    {
        $this->shouldPreserveFilters = $condition;
        $this->shouldPreserveGrouping = $condition;
        $this->shouldPreserveGroupingDirection = $condition;
        $this->shouldPreserveSort = $condition;
        $this->shouldPreserveColumns = $condition;

        return $this;
    }

    public function preserveFilters(bool | Closure $condition = true): static
    {
        $this->shouldPreserveFilters = $condition;

        return $this;
    }

    public function preserveGrouping(bool | Closure $condition = true): static
    {
        $this->shouldPreserveGrouping = $condition;

        return $this;
    }

    public function preserveGroupingDirection(bool | Closure $condition = true): static
    {
        $this->shouldPreserveGroupingDirection = $condition;

        return $this;
    }

    public function preserveSort(bool | Closure $condition = true): static
    {
        $this->shouldPreserveSort = $condition;

        return $this;
    }

    /**
     * @deprecated Use `preserveSort()` instead.
     */
    public function preserveSortColumn(bool | Closure $condition = true): static
    {
        $this->shouldPreserveSort = $condition;

        return $this;
    }

    /**
     * @deprecated Use `preserveSort()` instead.
     */
    public function preserveSortDirection(bool | Closure $condition = true): static
    {
        return $this;
    }

    public function preserveToggledColumns(bool | Closure $condition = true): static
    {
        $this->shouldPreserveColumns = $condition;

        return $this;
    }

    public function shouldPreserveFilters(): bool
    {
        return $this->evaluate($this->shouldPreserveFilters);
    }

    public function shouldPreserveGrouping(): bool
    {
        return $this->evaluate($this->shouldPreserveGrouping);
    }

    public function shouldPreserveGroupingDirection(): bool
    {
        return $this->evaluate($this->shouldPreserveGroupingDirection);
    }

    public function shouldPreserveSort(): bool
    {
        return $this->evaluate($this->shouldPreserveSort);
    }

    /**
     * @deprecated Use `shouldPreserveSort()` instead.
     */
    public function shouldPreserveSortColumn(): bool
    {
        return $this->evaluate($this->shouldPreserveSort);
    }

    /**
     * @deprecated Use `shouldPreserveSort()` instead.
     */
    public function shouldPreserveSortDirection(): bool
    {
        return $this->evaluate($this->shouldPreserveSort);
    }

    public function shouldPreserveColumns(): bool
    {
        return $this->evaluate($this->shouldPreserveColumns);
    }
}
