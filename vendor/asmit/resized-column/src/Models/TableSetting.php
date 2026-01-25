<?php

namespace Asmit\ResizedColumn\Models;

use Illuminate\Database\Eloquent\Model;

class TableSetting extends Model
{
    protected $fillable = [
        'user_id',
        'resource',
        'styles',
    ];

    protected $casts = [
        'styles' => 'array',
    ];

    public function getFillable(): array
    {
        return $this->fillable;
    }

    public function getCasts(): array
    {
        return $this->casts;
    }
}
