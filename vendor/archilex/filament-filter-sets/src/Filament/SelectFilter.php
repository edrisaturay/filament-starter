<?php

namespace Archilex\AdvancedTables\Filament;

use Archilex\AdvancedTables\Filament\Concerns\CanBeFavorite;
use Archilex\AdvancedTables\Support\Config;
use Closure;
use Filament\Tables\Filters\SelectFilter as FilamentSelectFilter;
use Illuminate\Database\Eloquent\Builder;

class SelectFilter extends FilamentSelectFilter
{
    use CanBeFavorite;

    protected int | Closure | null $indicatorLabelsLimit = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->indicateUsing(function (SelectFilter $filter, array $state): array {
            if ($filter->isMultiple()) {
                if (blank($state['values'] ?? null)) {
                    return [];
                }

                if ($filter->queriesRelationships()) {
                    $relationshipQuery = $filter->getRelationshipQuery();

                    $labels = $relationshipQuery
                        ->when(
                            $filter->getRelationship() instanceof \Znck\Eloquent\Relations\BelongsToThrough,
                            fn (Builder $query) => $query->distinct(),
                        )
                        ->when(
                            $this->getRelationshipKey(),
                            fn (Builder $query, string $relationshipKey) => $query->whereIn($relationshipKey, $state['values']),
                            fn (Builder $query) => $query->whereKey($state['values'])
                        )
                        ->pluck($relationshipQuery->qualifyColumn($filter->getRelationshipTitleAttribute()))
                        ->all();
                } else {
                    $labels = collect($filter->getOptions())
                        ->mapWithKeys(fn (string | array $label, string $value): array => is_array($label) ? $label : [$value => $label])
                        ->only($state['values'])
                        ->all();
                }

                if (! count($labels)) {
                    return [];
                }

                $indicator = $filter->getIndicator();

                if (! $indicator instanceof Indicator) {
                    $indicator = Indicator::make("{$indicator}")
                        ->labels($labels)
                        ->limitLabels($this->getIndicatorLabelsLimit());
                }

                return [$indicator];
            }

            if (blank($state['value'] ?? null)) {
                return [];
            }

            if ($filter->queriesRelationships()) {
                $label = $filter->getRelationshipQuery()
                    ->when(
                        $this->getRelationshipKey(),
                        fn (Builder $query, string $relationshipKey) => $query->where($relationshipKey, $state['value']),
                        fn (Builder $query) => $query->whereKey($state['value'])
                    )
                    ->first()
                    ?->getAttributeValue($filter->getRelationshipTitleAttribute());
            } else {
                $label = collect($filter->getOptions())
                    ->mapWithKeys(fn (string | array $label, string $value): array => is_array($label) ? $label : [$value => $label])
                    ->get($state['value']);
            }

            if (blank($label)) {
                return [];
            }

            $indicator = $filter->getIndicator();

            if (! $indicator instanceof Indicator) {
                $indicator = Indicator::make("{$indicator}: {$label}");
            }

            return [$indicator];
        });
    }

    public function limitIndicatorLabels(int | Closure $limit): static
    {
        $this->indicatorLabelsLimit = $limit;

        return $this;
    }

    public function getIndicatorLabelsLimit(): ?int
    {
        return $this->evaluate($this->indicatorLabelsLimit) ?? Config::getDefaultIndicatorLabelsLimit();
    }
}
