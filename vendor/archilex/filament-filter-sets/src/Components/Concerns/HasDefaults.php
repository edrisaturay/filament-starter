<?php

namespace Archilex\AdvancedTables\Components\Concerns;

use Closure;

trait HasDefaults
{
    protected array | Closure $defaultColumns = [];

    protected array | Closure | null $defaultFilters = null;

    protected array | Closure | null $defaultFavoriteFilters = null;

    protected string | Closure | null $defaultGrouping = null;

    protected string | Closure | null $defaultGroupingDirection = null;

    protected string | array | Closure | null $defaultSort = null;

    protected string | Closure | null $defaultSortDirection = null;

    public function defaultColumns(array | Closure $columns): static
    {
        $this->defaultColumns = $columns;

        return $this;
    }

    public function defaultGrouping(string | Closure | null $group, string | Closure | null $direction = 'asc'): static
    {
        $this->defaultGrouping = $group;
        $this->defaultGroupingDirection = $direction;

        return $this;
    }

    public function defaultFilters(array | Closure $filters): static
    {
        $this->defaultFilters = $filters;

        return $this;
    }

    public function defaultFavoriteFilters(array | Closure $filters): static
    {
        $this->defaultFavoriteFilters = $filters;

        return $this;
    }

    public function defaultSort(string | array | Closure | null $sort, string | Closure | null $direction = 'asc'): static
    {
        $this->defaultSort = $sort;
        $this->defaultSortDirection = $direction;

        return $this;
    }

    public function getDefaultColumns(): ?array
    {
        return $this->evaluate($this->defaultColumns);
    }

    public function getDefaultFilters(): ?array
    {
        return $this->evaluate($this->defaultFilters);
    }

    public function getDefaultFavoriteFilters(): ?array
    {
        return $this->evaluate($this->defaultFavoriteFilters);
    }

    public function getDefaultGrouping(): ?string
    {
        return $this->evaluate($this->defaultGrouping);
    }

    public function getDefaultGroupingDirection(): ?string
    {
        $direction = $this->evaluate($this->defaultGroupingDirection);

        if (blank($direction)) {
            return null;
        }

        return strtolower($direction);
    }

    public function getDefaultSort(): string | array | null
    {
        return $this->evaluate($this->defaultSort);
    }

    public function getDefaultSortDirection(): ?string
    {
        $direction = $this->evaluate($this->defaultSortDirection);

        if (blank($direction)) {
            return null;
        }

        return strtolower($direction);
    }
}
