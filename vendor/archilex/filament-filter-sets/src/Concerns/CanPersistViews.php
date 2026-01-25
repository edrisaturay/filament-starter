<?php

namespace Archilex\AdvancedTables\Concerns;

use Archilex\AdvancedTables\Support\Config;

trait CanPersistViews
{
    public function hasMultipleInstances(): bool
    {
        return false;
    }

    public function getActivePresetViewSessionKey(): string
    {
        $table = class_basename($this::class);

        return "tables.{$table}_active_preset_view";
    }

    public function getActiveUserViewSessionKey(): string
    {
        $table = class_basename($this::class);

        return "tables.{$table}_active_user_view";
    }

    public function getCurrentPresetViewSessionKey(): string
    {
        $table = class_basename($this::class);

        return "tables.{$table}_current_preset_view";
    }

    public function getCurrentUserViewSessionKey(): string
    {
        $table = class_basename($this::class);

        return "tables.{$table}_current_user_view";
    }

    protected function persistToSessionIfEnabled($resetTableToDefault = false): void
    {
        // Since Filament doesn't have a "shouldPersistToggledTableColumns" nor a
        // "shouldPersistGrouping" method, when any of the other persist methods are
        // enabled it best to go ahead and set the tableColumns in the session to
        // what is configured in the View so that when returning to the resource the
        // grouping and columns are configured correctly. However, if we are resetting
        // the table to its default state, because persistActiveView is not enabled, then
        // everything should be reset so we need to reset the toggle columns
        // to the defaults as well, regardless of whether should persist is enabled.

        if (
            ! $resetTableToDefault &&
            (
                Config::persistsActiveViewInSession() ||
                $this->getTable()->persistsFiltersInSession() ||
                $this->getTable()->persistsSearchInSession() ||
                $this->getTable()->persistsSortInSession() ||
                $this->getTable()->persistsColumnSearchesInSession()
            )
        ) {
            $this->resetActiveViews = false;

            $this->applyTableColumnManager();
        }

        if ($resetTableToDefault) {
            $this->resetTableColumnManager();
        }

        if (Config::persistsActiveViewInSession()) {
            session()->put(
                $this->getActivePresetViewSessionKey(),
                $this->activePresetView,
            );

            session()->put(
                $this->getActiveUserViewSessionKey(),
                $this->activeUserView,
            );

            session()->put(
                $this->getCurrentPresetViewSessionKey(),
                $this->currentPresetView,
            );

            session()->put(
                $this->getCurrentUserViewSessionKey(),
                $this->currentUserView,
            );
        }

        if ($this->getTable()->persistsFiltersInSession()) {
            session()->put(
                $this->getTableFiltersSessionKey(),
                $this->tableFilters,
            );
        }

        if ($this->getTable()->persistsSearchInSession()) {
            session()->put(
                $this->getTableSearchSessionKey(),
                $this->tableSearch,
            );
        }

        if ($this->getTable()->persistsSortInSession()) {
            session()->put(
                $this->getTableSortSessionKey(),
                $this->tableSort,
            );
        }

        if ($this->getTable()->persistsColumnSearchesInSession()) {
            session()->put(
                $this->getTableColumnSearchesSessionKey(),
                $this->tableColumnSearches,
            );
        }
    }
}
