<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchSetting extends Model
{
    protected $table = 'branch_settings';

    protected $fillable = [
        'branch_address',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public static function current(): self
    {
        return static::query()->first() ?? static::query()->create([]);
    }
}