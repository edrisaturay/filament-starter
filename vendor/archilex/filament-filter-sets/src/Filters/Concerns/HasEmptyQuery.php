<?php

namespace Archilex\AdvancedTables\Filters\Concerns;

use Archilex\AdvancedTables\Filters\Operators\DateOperator;
use Illuminate\Database\Eloquent\Builder;

trait HasEmptyQuery
{
    protected function applyEmptyQuery(Builder $query, array $data, string $column): Builder
    {
        if ($data['operator'] === DateOperator::IS_EMPTY) {
            return $query->whereNull($column);
        }

        return $query->whereNotNull($column);
    }

    protected function isEmptyQuery(array $data): bool
    {
        return in_array($data['operator'], [DateOperator::IS_EMPTY, DateOperator::IS_NOT_EMPTY]);
    }
}
