<?php

namespace Raison\FilamentStarter\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'starter_audit_logs';

    protected $fillable = [
        'actor_user_id',
        'action',
        'panel_id',
        'plugin_key',
        'before',
        'after',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];
}
