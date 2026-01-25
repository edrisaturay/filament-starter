<?php

namespace Archilex\AdvancedTables\Models;

use Archilex\AdvancedTables\Enums\ViewType;
use Archilex\AdvancedTables\Models\Concerns\InteractsWithTenant;
use Archilex\AdvancedTables\Support\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagedDefaultView extends Model
{
    use HasFactory;
    use InteractsWithTenant;

    protected $table = 'filament_filter_sets_managed_default_views';

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'is_default' => 'bool',
        'view_type' => ViewType::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Config::getUser(), 'user_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Config::getTenant(), Config::getTenantColumn());
    }

    public function userView(): BelongsTo
    {
        return $this->belongsTo(Config::getUserView())
            ->where('view_type', ViewType::UserView);
    }

    public function scopeDefault(Builder $query): void
    {
        $query->where('is_default', true);
    }

    public function scopeResource(Builder $query, string $resource): void
    {
        $query->where('resource', $resource);
    }

    public function scopeBelongsToCurrentUser(Builder $query): void
    {
        $query->where('user_id', Config::auth()->id());
    }

    public function scopeDoesntBelongToCurrentUser(Builder $query): void
    {
        $query->whereNot('user_id', Config::auth()->id());
    }

    public function isPresetView(): bool
    {
        return $this->view_type === ViewType::PresetView;
    }

    public function isUserView(): bool
    {
        return $this->view_type === ViewType::UserView;
    }
}
