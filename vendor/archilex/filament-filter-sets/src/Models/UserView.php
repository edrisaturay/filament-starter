<?php

namespace Archilex\AdvancedTables\Models;

use Archilex\AdvancedTables\Enums\Status;
use Archilex\AdvancedTables\Models\Concerns\InteractsWithTenant;
use Archilex\AdvancedTables\Support\Config;
use Archilex\AdvancedTables\Support\ConvertIcon;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\RelationManagerConfiguration;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class UserView extends Model implements Sortable
{
    use HasFactory;
    use InteractsWithTenant;
    use SortableTrait;

    protected $table = 'filament_filter_sets';

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'filters' => 'array',
        'indicators' => 'array',
        'is_public' => 'bool',
        'is_global_favorite' => 'bool',
        'status' => Status::class,
    ];

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    public function getQueryString(): string
    {
        return Arr::query(['activeUserView' => $this->id, 'currentUserView' => $this->id]);
    }

    public function buildSortQuery()
    {
        return static::query()
            ->where('user_id', $this->user_id)
            ->when(Config::hasTenancy(), fn ($query) => $query->where(Config::getTenantColumn(), Config::getTenantId()));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Config::getUser(), 'user_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Config::getTenant(), Config::getTenantColumn());
    }

    public function managedUserViews(): HasMany
    {
        return $this->hasMany(Config::getManagedUserView());
    }

    public function userManagedUserViews(): BelongsToMany
    {
        return $this->belongsToMany(Config::getUser(), 'filament_filter_set_user', foreignPivotKey: 'filter_set_id', relatedPivotKey: 'user_id')
            ->withPivot('sort_order', 'is_visible', Config::getTenantColumn());
    }

    public function favoritedUserManagedUserViews(): BelongsToMany
    {
        return $this->userManagedUserViews()
            ->wherePivot('is_visible', true);
    }

    public function hiddenUserManagedUserViews(): BelongsToMany
    {
        return $this->userManagedUserViews()
            ->wherePivot('is_visible', false);
    }

    public function scopeDefault(Builder $query): void
    {
        $query->where('is_default', true);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', Status::Pending);
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', Status::Approved);
    }

    public function scopePendingOrApproved(Builder $query): void
    {
        $query->whereIn('status', [Status::Pending, Status::Approved]);
    }

    public function scopeRejected(Builder $query): void
    {
        $query->where('status', Status::Rejected);
    }

    public function scopeMeetsMinimumStatus(Builder $query): void
    {
        $minimumStatus = Config::getMinimumStatusForDisplay();

        if (in_array($minimumStatus, ['approved', Status::Approved])) {
            $query->approved();
        } elseif (in_array($minimumStatus, ['pending', Status::Pending])) {
            $query->pendingOrApproved();
        } else {

        }
    }

    public function scopeResource(Builder $query, string $resource): void
    {
        $query->where('resource', $resource);
    }

    public function scopeResourcePanels(Builder $query): void
    {
        $query->when(
            ! Config::getResourcePanels(),
            fn (Builder $query) => $query->whereIn('resource', static::getPanelResources())
        )
            ->when(
                filled($panels = Config::getResourcePanels()),
                fn (Builder $query) => $query->whereIn('resource', static::getPanelsResources($panels))
            );
    }

    public function scopePublic(Builder $query): void
    {
        $query->where('is_public', true);
    }

    public function scopePrivate(Builder $query): void
    {
        $query->where('is_public', false);
    }

    public function scopeGlobal(Builder $query): void
    {
        $query->where('is_global_favorite', true);
    }

    public function scopeLocal(Builder $query): void
    {
        $query->where('is_global_favorite', false);
    }

    public function scopeBelongsToCurrentUser(Builder $query): void
    {
        $query->where('user_id', Config::auth()->id());
    }

    public function scopeDoesntBelongToCurrentUser(Builder $query): void
    {
        $query->where('user_id', '!=', Config::auth()->id());
    }

    public function scopeManagedByCurrentUser(Builder $query): void
    {
        $query->whereRelation('userManagedUserViews', '' . Config::getUserTable() . '.' . Config::getUserTableKeyColumn() . '', Config::auth()->id());
    }

    public function scopeUnManagedByCurrentUser(Builder $query): void
    {
        $query->whereDoesntHave('userManagedUserViews', function ($query) {
            $query->where('' . Config::getUserTable() . '.' . Config::getUserTableKeyColumn() . '', Config::auth()->id());
        });
    }

    public function scopeFavoritedByCurrentUser(Builder $query): void
    {
        $query->whereRelation('favoritedUserManagedUserViews', '' . Config::getUserTable() . '.' . Config::getUserTableKeyColumn() . '', Config::auth()->id());
    }

    public function scopeHiddenByCurrentUser(Builder $query): void
    {
        $query->whereRelation('hiddenUserManagedUserViews', '' . Config::getUserTable() . '.' . Config::getUserTableKeyColumn() . '', Config::auth()->id());
    }

    public function isGlobal(): bool
    {
        return $this->is_global_favorite;
    }

    public function isLocal(): bool
    {
        return ! $this->isGlobal();
    }

    public function isPublic(): bool
    {
        return $this->is_public;
    }

    public function isPrivate(): bool
    {
        return ! $this->isPublic();
    }

    public function isCurrentDefault(): bool
    {
        return (bool) ($this->attributes['is_current_default'] ?? false);
    }

    public function belongsToCurrentUser(): bool
    {
        return $this->user_id === Config::auth()->id();
    }

    public function doesntBelongToCurrentUser(): bool
    {
        return ! $this->belongsToCurrentUser();
    }

    public function getLabel(): string
    {
        return $this->name;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    protected function icon(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => ConvertIcon::convert($value),
        );
    }

    protected function resourceName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getResourceName(),
        );
    }

    protected function getResourceName(): string
    {
        if ($this->isRelationManager()) {
            return $this->getRelationManagerResourceName();
        }

        if ($this->isTableWidget()) {
            return $this->getTableWidgetName();
        }

        if ($this->isManageRelatedRecords()) {
            return $this->getManageRelatedRecordsName();
        }

        return Str::of(Str::replace('Archilex\FilamentFilterSets', 'Archilex\AdvancedTables', $this->resource)::getPluralModelLabel())->ucfirst();
    }

    protected function isRelationManager(): bool
    {
        return is_subclass_of($this->resource, RelationManager::class);
    }

    protected function isTableWidget(): bool
    {
        return is_subclass_of($this->resource, TableWidget::class);
    }

    protected function isManageRelatedRecords(): bool
    {
        return is_subclass_of($this->resource, ManageRelatedRecords::class);
    }

    protected function getRelationManagerResourceName(): string
    {
        $basePath = Str::of($this->resource)
            ->beforeLast('\\RelationManagers\\');

        $possibleResource = $basePath->append('\\')
            ->append($basePath->afterLast('\\')->append('Resource'))
            ->toString();

        $location = match (true) {
            class_exists($basePath) => $basePath->toString()::getPluralModelLabel(),
            class_exists($possibleResource) => $possibleResource::getPluralModelLabel(),
            default => 'Unknown Resource',
        };

        $relationManager = $this->resource::getRelationshipTitle() ?? $basePath
            ->afterLast('\\RelationManagers\\')
            ->beforeLast('RelationManager')
            ->headline()
            ->toString();

        return Str::title($location . ' > ' . $relationManager);
    }

    protected function getTableWidgetName(): string
    {
        $basePath = Str::of($this->resource)
            ->beforeLast('\\Widgets\\');

        $possibleResource = $basePath->append('\\')
            ->append($basePath->afterLast('\\')->append('Resource'))
            ->toString();

        $location = match (true) {
            $basePath->toString() === 'App\Filament' => 'Dashboard',
            class_exists($basePath) => $basePath->toString()::getPluralModelLabel(),
            class_exists($possibleResource) => $possibleResource::getPluralModelLabel(),
            default => 'Unknown Resource',
        };

        $widget = Str::of($this->resource)
            ->afterLast('\\Widgets\\')
            ->headline()
            ->toString();

        return Str::title($location . ' > ' . $widget);
    }

    protected function getManageRelatedRecordsName(): string
    {
        $location = Str::of($this->resource)
            ->beforeLast('\\Pages\\')
            ->toString();

        $location = $location === 'App\Filament'
            ? 'Dashboard'
            : $this->resource::getResource()::getPluralModelLabel();

        $page = $this->resource::getNavigationLabel() ?? Str::of($this->resource)
            ->afterLast('\\Pages\\')
            ->headline()
            ->toString();

        return Str::title($location . ' > ' . $page);
    }

    protected static function getPanelsResources(array $panels): array
    {
        $resources = [];

        foreach ($panels as $panel) {
            $resources[] = static::getPanelResources($panel);
        }

        return Arr::flatten($resources);
    }

    protected static function getPanelResources($id = null): array
    {
        $panel = $id
            ? filament()->getPanel($id)
            : filament()->getCurrentOrDefaultPanel();

        $resources = $panel->getResources();

        $managers = [];

        foreach ($resources as $resource) {
            foreach ($resource::getRelations() as $relation) {
                if ($relation instanceof RelationGroup) {
                    $relationManagers = $relation->getManagers();

                    $managers[] = collect($relationManagers)
                        ->map(function ($relationManager) {
                            if ($relationManager instanceof RelationManagerConfiguration) {
                                return $relationManager->relationManager;
                            }

                            return $relationManager;
                        });

                    continue;
                }

                if ($relation instanceof RelationManagerConfiguration) {
                    $managers[] = $relation?->relationManager;

                    continue;
                }

                $managers[] = $relation;
            }

            foreach ($resource::getPages() as $page) {
                $managers[] = $page->getPage();
            }

            $managers[] = $resource::getWidgets();

            // Legacy support
            if (Str::contains($resource, '\AdvancedTables')) {
                $resources[] = Str::replace('Archilex\AdvancedTables', 'Archilex\FilamentFilterSets', $resource);
            }
        }

        return [
            ...$resources,
            ...Arr::flatten($managers),
            ...$panel->getWidgets(),
            ...$panel->getPages(),
        ];
    }
}
