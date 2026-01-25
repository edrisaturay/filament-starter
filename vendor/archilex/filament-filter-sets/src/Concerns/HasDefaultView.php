<?php

namespace Archilex\AdvancedTables\Concerns;

use Archilex\AdvancedTables\Components\PresetView;
use Archilex\AdvancedTables\Enums\ViewType;
use Archilex\AdvancedTables\Models\ManagedDefaultView;
use Archilex\AdvancedTables\Support\Config;

trait HasDefaultView
{
    protected ?array $cachedDefaultViewConfiguration = null;

    public function hasDefaultView(): bool
    {
        return Config::favoritesBarHasDefaultView();
    }

    public function getDefaultView(): PresetView
    {
        return PresetView::make()
            ->icon(Config::getFavoritesBarDefaultIcon())
            ->iconPosition(Config::getFavoritesBarIconPosition())
            ->label(__('advanced-tables::advanced-tables.tables.favorites.default'))
            ->favorite()
            ->default();
    }

    public function getManagedDefaultView(): ?ManagedDefaultView
    {
        return once(
            fn () => Config::auth()->user()
                ->managedDefaultViews()
                ->resource($this->getResourceName())
                ->first()
        );
    }

    public function removeCurrentManagedDefaultView(): void
    {
        Config::auth()->user()
            ->managedDefaultViews()
            ->resource($this->getResourceName())
            ->delete();
    }

    protected function getDefaultViewConfiguration(): ?array
    {
        return $this->cachedDefaultViewConfiguration ??= $this->cacheDefaultViewConfiguration();
    }

    protected function cacheDefaultViewConfiguration(): ?array
    {
        if (Config::managedDefaultViewsAreEnabled()) {
            $managedDefaultView = $this->getManagedDefaultView();

            if ($managedDefaultView instanceof ManagedDefaultView) {
                return $this->configureFromManagedDefaultView($managedDefaultView);
            }
        }

        $defaultPresetViewName = $this->getDefaultPresetViewName();

        if (filled($defaultPresetViewName)) {
            return $this->configureFromDefaultPresetView($defaultPresetViewName);
        }

        return null;
    }

    protected function configureFromManagedDefaultView(ManagedDefaultView $managedDefaultView): array
    {
        if ($managedDefaultView->isPresetView()) {
            $this->activePresetView = $managedDefaultView->view;
        } elseif ($managedDefaultView->isUserView()) {
            $this->activeUserView = $managedDefaultView->view;
        }

        return [
            'view_type' => $managedDefaultView->view_type->value,
            'view' => $managedDefaultView->view,
        ];
    }

    protected function configureFromDefaultPresetView(string $defaultPresetViewName): array
    {
        $this->activePresetView = $defaultPresetViewName;

        return [
            'view_type' => ViewType::PresetView->value,
            'view' => $defaultPresetViewName,
        ];
    }

    public function loadDefaultView(): void
    {
        $defaultViewConfiguration = $this->getDefaultViewConfiguration();

        if (! $defaultViewConfiguration) {
            return;
        }

        $viewType = $defaultViewConfiguration['view_type'] ?? null;
        $view = $defaultViewConfiguration['view'] ?? null;

        $isActive = blank(request()->getQueryString());

        if ($viewType === ViewType::PresetView->value && $view) {
            $this->loadPresetView(presetView: $view, resetTable: false, isActive: $isActive);

            return;
        }

        if ($viewType === ViewType::UserView->value && $view) {
            $this->loadUserView(userView: $view, resetTable: false, isActive: $isActive);

            return;
        }

        $this->loadDefaultTable();
    }
}
