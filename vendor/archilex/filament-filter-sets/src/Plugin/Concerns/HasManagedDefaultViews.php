<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Archilex\AdvancedTables\Models\ManagedDefaultView;
use Closure;
use Filament\Support\Concerns\EvaluatesClosures;

trait HasManagedDefaultViews
{
    use EvaluatesClosures;

    protected bool | Closure $managedDefaultViewsAreEnabled = false;

    protected string | Closure $managedDefaultView = ManagedDefaultView::class;

    protected string | Closure $managedDefaultViewSetIcon = 'heroicon-o-bolt';

    protected string | Closure $managedDefaultViewRemoveIcon = 'heroicon-o-bolt-slash';

    public function managedDefaultViewsEnabled(bool | Closure $condition = true): static
    {
        $this->managedDefaultViewsAreEnabled = $condition;

        return $this;
    }

    public function managedDefaultView(string | Closure $managedDefaultView): static
    {
        $this->managedDefaultView = $managedDefaultView;

        return $this;
    }

    public function managedDefaultViewSetIcon(string | Closure $icon): static
    {
        $this->managedDefaultViewSetIcon = $icon;

        return $this;
    }

    public function managedDefaultViewRemoveIcon(string | Closure $icon): static
    {
        $this->managedDefaultViewRemoveIcon = $icon;

        return $this;
    }

    public function managedDefaultViewsAreEnabled(): bool
    {
        return $this->evaluate($this->managedDefaultViewsAreEnabled);
    }

    public function getManagedDefaultView(): string
    {
        return $this->evaluate($this->managedDefaultView);
    }

    public function getManagedDefaultViewSetIcon(): string
    {
        return $this->evaluate($this->managedDefaultViewSetIcon);
    }

    public function getManagedDefaultViewRemoveIcon(): string
    {
        return $this->evaluate($this->managedDefaultViewRemoveIcon);
    }
}
