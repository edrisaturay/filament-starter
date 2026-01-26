<?php

namespace EdrisaTuray\FilamentStarter\Models;

use Illuminate\Database\Eloquent\Model;
use EdrisaTuray\FilamentStarter\Support\PluginStateResolver;

class PanelPluginOverride extends Model
{
    protected $table = 'starter_panel_plugin_overrides';

    protected $fillable = [
        'panel_id',
        'plugin_key',
        'enabled',
        'is_dangerous',
        'options',
        'options_version',
        'tenant_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'is_dangerous' => 'boolean',
        'options' => 'array',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            // Re-enforce dangerous plugins logic
            if ($model->is_dangerous && $model->enabled === false) {
                // If it's being disabled but is dangerous, we force it to true.
                $model->enabled = true;
            }
        });

        static::updated(function ($model) {
            // If is_dangerous was toggled ON, ensure enabled is also ON
            if ($model->isDirty('is_dangerous') && $model->is_dangerous && ! $model->enabled) {
                $model->update(['enabled' => true]);
            }
        });

        static::saved(function ($model) {
            PluginStateResolver::invalidate($model->panel_id, $model->tenant_id);

            \EdrisaTuray\FilamentStarter\Models\AuditLog::create([
                'actor_user_id' => auth()->id(),
                'action' => 'update_plugin_state',
                'panel_id' => $model->panel_id,
                'plugin_key' => $model->plugin_key,
                'before' => $model->getOriginal(),
                'after' => $model->getAttributes(),
            ]);
        });

        static::deleted(function ($model) {
            PluginStateResolver::invalidate($model->panel_id, $model->tenant_id);

            \EdrisaTuray\FilamentStarter\Models\AuditLog::create([
                'actor_user_id' => auth()->id(),
                'action' => 'delete_plugin_override',
                'panel_id' => $model->panel_id,
                'plugin_key' => $model->plugin_key,
                'before' => $model->getAttributes(),
            ]);
        });
    }
}
