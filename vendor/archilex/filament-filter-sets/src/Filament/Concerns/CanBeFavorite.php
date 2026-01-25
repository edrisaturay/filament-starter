<?php

namespace Archilex\AdvancedTables\Filament\Concerns;

use Archilex\AdvancedTables\Filament\Indicator;
use Closure;
use Filament\Tables\Filters\Indicator as FiltersIndicator;
use Illuminate\Support\Arr;
use ReflectionFunction;

trait CanBeFavorite
{
    protected bool | Closure $isFavorite = false;

    protected bool | Closure | null $hasMultipleIndicators = null;

    public function favorite(bool | Closure $condition = true): static
    {
        $this->isFavorite = $condition;

        return $this;
    }

    public function multipleIndicators(bool | Closure $condition = true): static
    {
        $this->hasMultipleIndicators = $condition;

        return $this;
    }

    public function isFavorite(): bool
    {
        return $this->evaluate($this->isFavorite);
    }

    public function hasMultipleIndicators(): bool
    {
        $hasMultipleIndicators = $this->evaluate($this->hasMultipleIndicators);

        if (is_bool($hasMultipleIndicators)) {
            return $hasMultipleIndicators;
        }

        $reflection = new ReflectionFunction($this->indicateUsing);
        $returnType = $reflection->getReturnType();

        return ! in_array($returnType?->getName(), ['string', FiltersIndicator::class]);
    }

    /**
     * @return array<Indicator>
     */
    public function getIndicators(): array
    {
        $state = $this->getState();

        $indicators = $this->evaluate($this->indicateUsing, [
            'data' => $state,
            'state' => $state,
        ]);

        if (blank($indicators)) {
            return [];
        }

        $indicators = Arr::wrap($indicators);

        foreach ($indicators as $field => $indicator) {
            if (! $indicator instanceof Indicator) {
                $indicator = Indicator::make($indicator);
            }

            if (is_string($field)) {
                $indicator = $indicator->removeField($field);
            }

            if (! $this->hasMultipleIndicators()) {
                $indicator = $indicator->removeField(null);
            }

            $indicators[$field] = $indicator;
        }

        return $indicators;
    }

    /**
     * @return array<Indicator>
     */
    public function getFavoriteIndicators(): array
    {
        $state = $this->getState();
        $formFields = $this->getFormSchema();

        $state = collect($formFields)
            ->mapWithKeys(function ($field) use ($state) {
                return [$field->getName() => $state[$field->getName()]];
            })
            ->all();

        $indicators = $this->evaluate($this->indicateUsing, [
            'data' => $state,
            'state' => $state,
        ]);

        if (! $this->hasMultipleIndicators()) {
            return [
                Indicator::make($this->getName())
                    ->active($indicators instanceof Indicator)
                    ->label($indicators instanceof Indicator ? $indicators->getLabel() : $this->getLabel())
                    ->removable(false)
                    ->removeField(null),
            ];
        }

        $newIndicatorArray = [];

        foreach ($state as $field => $value) {
            $formField = array_shift($formFields);

            if ((is_array($value) && empty($value)) || (! is_array($value) && ! strlen($value))) {
                $newIndicatorArray[] = Indicator::make($field)
                    ->active(false)
                    ->label($formField->getLabel())
                    ->removable(false)
                    ->removeField(null);

                continue;
            }

            if ($indicators instanceof Indicator) {
                $newIndicatorArray[] = $indicators;

                continue;
            }

            if (is_string($indicators)) {
                $newIndicatorArray[] = $indicators;
            } else {
                $newIndicatorArray[] = array_shift($indicators);
            }
        }

        $indicators = Arr::wrap($newIndicatorArray);

        foreach ($indicators as $field => $indicator) {
            if (! $indicator instanceof Indicator) {
                $indicator = Indicator::make($indicator);
            }

            if (is_string($field)) {
                $indicator = $indicator->removeField($field);
            }

            $indicators[$field] = $indicator->removable(false);
        }

        return $indicators;
    }
}
