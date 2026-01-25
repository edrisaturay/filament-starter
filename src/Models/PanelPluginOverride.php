<?php

namespace Raison\FilamentStarter\Models;

use Illuminate\Database\Eloquent\Model;
use Raison\FilamentStarter\Support\PluginStateResolver;

class PanelPluginOverride extends Model
{
    protected $table = 'starter_panel_plugin_overrides';

    protected $fillable = [
        'panel_id',
        'plugin_key',
        'enabled',
        'options',
        'options_version',
        'tenant_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'options' => 'array',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (\Raison\FilamentStarter\Support\PluginRegistry::isDangerous($model->plugin_key) && $model->enabled === false) {
                // Prevent disabling dangerous plugins
                $model->enabled = true;

                // In a real scenario, we might want to throw an exception or log a warning
                \Illuminate\Support\Facades\Log::warning("Attempted to disable dangerous plugin: {$model->plugin_key}");
            }
        });

        static::saved(function ($model) {
            PluginStateResolver::invalidate($model->panel_id, $model->tenant_id);
        });

        static::deleted(function ($model) {
            PluginStateResolver::invalidate($model->panel_id, $model->tenant_id);
        });
    }
}
