<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TimeSlotWindow extends Model
{
    protected $table = 'time_slot_windows';

    protected $fillable = [
        'slot_key',
        'start_time',
        'end_time',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public const DEFAULT_WINDOWS = [
        '06_08' => ['06:00:00', '08:00:00'],
        '08_10' => ['08:00:00', '10:00:00'],
        '10_12' => ['10:00:00', '12:00:00'],
        '12_14' => ['12:00:00', '14:00:00'],
        '14_16' => ['14:00:00', '16:00:00'],
        '16_18' => ['16:00:00', '18:00:00'],
        '18_20' => ['18:00:00', '20:00:00'],
        '20_22' => ['20:00:00', '22:00:00'],
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function options(): array
    {
        $windows = static::active()->orderBy('sort_order')->orderBy('slot_key')->get();

        if ($windows->isNotEmpty()) {
            return $windows->map(function (self $window) {
                return [
                    'slot_key' => $window->slot_key,
                    'start_time' => $window->start_time,
                    'end_time' => $window->end_time,
                ];
            })->values()->all();
        }

        return collect(static::DEFAULT_WINDOWS)->map(function (array $times, string $slotKey) {
            return [
                'slot_key' => $slotKey,
                'start_time' => $times[0],
                'end_time' => $times[1],
            ];
        })->values()->all();
    }

    public static function allowedKeys(): array
    {
        return array_map(function (array $window) {
            return $window['slot_key'];
        }, static::options());
    }

    public static function map(): array
    {
        $map = [];

        foreach (static::options() as $window) {
            $map[$window['slot_key']] = [$window['start_time'], $window['end_time']];
        }

        return $map;
    }
}