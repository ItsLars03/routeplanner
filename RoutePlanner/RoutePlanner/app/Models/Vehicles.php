<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Vehicles extends Model
{
    protected $table = 'vehicles';

    protected $fillable = [
        'name',
        'brand',
        'model',
        'fuel_type',
        'license_plate',
        'token',
        'api_token',
        'last_latitude',
        'last_longitude',
        'last_temperature',
        'last_telemetry_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->api_token) {
                $model->api_token = self::generateApiToken();
            }
        });
    }

    public static function generateApiToken(): string
    {
        return hash('sha256', Str::random(32) . time());
    }

    public function timeSlots(): HasMany
    {
        return $this->hasMany(VehicleTimeSlot::class, 'vehicle_id');
    }
}
