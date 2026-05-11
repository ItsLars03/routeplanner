<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drivers extends Model
{
    protected $table = 'drivers';

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
    ];

    public function timeSlots(): HasMany
    {
        return $this->hasMany(VehicleTimeSlot::class, 'driver_id');
    }
}
