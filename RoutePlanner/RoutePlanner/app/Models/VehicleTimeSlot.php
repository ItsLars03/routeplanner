<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VehicleTimeSlot extends Model
{
    public const SLOT_WINDOWS = [
        '06_08' => ['06:00:00', '08:00:00'],
        '08_10' => ['08:00:00', '10:00:00'],
        '10_12' => ['10:00:00', '12:00:00'],
        '12_14' => ['12:00:00', '14:00:00'],
        '14_16' => ['14:00:00', '16:00:00'],
        '16_18' => ['16:00:00', '18:00:00'],
        '18_20' => ['18:00:00', '20:00:00'],
        '20_22' => ['20:00:00', '22:00:00'],
    ];

    protected $table = 'vehicle_time_slots';

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'slot_date',
        'slot_key',
        'start_time',
        'end_time',
        'returned_to_branch_at',
        'finished_at',
    ];

    protected $casts = [
        'slot_date' => 'date:Y-m-d',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'returned_to_branch_at' => 'datetime:Y-m-d H:i:s',
        'finished_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicles::class, 'vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }

    public function stops(): BelongsToMany
    {
        return $this->belongsToMany(Stop::class, 'stop_vehicle_time_slot', 'vehicle_time_slot_id', 'stop_id')
            ->withPivot(['sequence', 'arrived_at', 'delivered_late'])
            ->withTimestamps();
    }
}
