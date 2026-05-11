<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamageReport extends Model
{
    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'reported_date',
        'location',
        'damage_type',
        'severity',
        'description',
        'estimated_cost',
        'status',
        'resolved_at',
    ];

    protected $casts = [
        'reported_date' => 'date',
        'resolved_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicles::class, 'vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }
}
