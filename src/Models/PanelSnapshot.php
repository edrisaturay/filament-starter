<?php

namespace EdrisaTuray\FilamentStarter\Models;

use Illuminate\Database\Eloquent\Model;

class PanelSnapshot extends Model
{
    protected $table = 'starter_panel_snapshots';

    protected $fillable = [
        'panel_id',
        'meta',
        'last_seen_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'last_seen_at' => 'datetime',
    ];
}
