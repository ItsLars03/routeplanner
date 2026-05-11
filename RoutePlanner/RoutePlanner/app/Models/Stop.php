<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Stop extends Model
{
    protected $table = 'stops';

    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'is_active',
        'date',
        'slot_key',
        'custom_start_time',
        'custom_end_time',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'date' => 'date',
    ];

    public function timeSlots(): BelongsToMany
    {
        return $this->belongsToMany(VehicleTimeSlot::class, 'stop_vehicle_time_slot', 'stop_id', 'vehicle_time_slot_id')
            ->withPivot(['sequence', 'arrived_at', 'delivered_late'])
            ->withTimestamps();
    }
}
