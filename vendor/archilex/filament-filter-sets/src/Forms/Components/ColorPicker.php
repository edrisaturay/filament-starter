<?php

namespace Archilex\AdvancedTables\Forms\Components;

use Filament\Actions\Concerns\HasSize;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasAlignment;
use Filament\Support\Enums\Size;

class ColorPicker extends Field
{
    use HasAlignment;
    use HasSize;

    protected string $view = 'advanced-tables::forms.components.color-picker';

    protected bool $includeBlack = false;

    protected bool $includeGray = true;

    protected bool $includeCustomPicker = false;

    protected string $spacing = 'gap-2';

    public function includeBlack(bool $condition = true): static
    {
        $this->includeBlack = $condition;

        return $this;
    }

    public function includeGray(bool $condition = true): static
    {
        $this->includeGray = $condition;

        return $this;
    }

    public function includeCustomPicker(bool $condition = true): static
    {
        $this->includeCustomPicker = $condition;

        return $this;
    }

    public function spacing(string $spacing): static
    {
        $this->spacing = $spacing;

        return $this;
    }

    public function shouldIncludeBlack(): bool
    {
        return $this->includeBlack;
    }

    public function shouldIncludeGray(): bool
    {
        return $this->includeGray;
    }

    public function shouldIncludeCustomPicker(): bool
    {
        return $this->includeCustomPicker;
    }

    public function getSize(): Size | string | null
    {
        return $this->evaluate($this->size) ?? Size::Medium;
    }

    public function getSpacing(): string
    {
        return $this->spacing;
    }
}
