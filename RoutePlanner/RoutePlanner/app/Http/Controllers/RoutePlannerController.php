<?php

namespace App\Http\Controllers;

use App\Models\Drivers;
use App\Models\BranchSetting;
use App\Models\Stop;
use App\Models\TimeSlotWindow;
use App\Models\VehicleTimeSlot;
use App\Models\Vehicles;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RoutePlannerController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        $this->ensureSlotsForDate($date);

        $vehicles = Vehicles::query()
            ->with(['timeSlots' => function ($query) use ($date) {
                $query->where('slot_date', $date)
                    ->with(['driver', 'stops'])
                    ->orderBy('start_time');
            }])
            ->orderBy('name')
            ->get();

        return response()->json([
            'date' => $date,
            'slot_windows' => TimeSlotWindow::options(),
            'vehicles' => $vehicles,
            'drivers' => Drivers::query()->orderBy('firstname')->orderBy('lastname')->get(),
            'stops' => Stop::query()
                ->where('is_active', true)
                ->where(function($q) use ($date) {
                    $q->whereNull('date')->orWhere('date', $date);
                })
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function showTimeSlot(VehicleTimeSlot $timeSlot)
    {
        $timeSlot->load(['vehicle', 'driver', 'stops']);
        return response()->json($timeSlot);
    }

    public function routeGeometry(VehicleTimeSlot $timeSlot)
    {
        $timeSlot->load(['stops']);
        $branchSetting = BranchSetting::current();

        $orderedStops = $timeSlot->stops
            ->sortBy(function ($stop) {
                return $stop->pivot->sequence ?? $stop->id;
            })
            ->values();

        $points = $orderedStops
            ->filter(function ($stop) {
                return $stop->latitude && $stop->longitude;
            })
            ->map(function ($stop) {
                return [
                    'stop_id' => $stop->id,
                    'latitude' => (float) $stop->latitude,
                    'longitude' => (float) $stop->longitude,
                ];
            })
            ->values();

        $branchPoint = null;
        if ($branchSetting->latitude && $branchSetting->longitude) {
            $branchPoint = [
                'type' => 'branch',
                'latitude' => (float) $branchSetting->latitude,
                'longitude' => (float) $branchSetting->longitude,
            ];
        }

        if (!$branchPoint || $points->isEmpty()) {
            return response()->json([
                'source' => 'insufficient-points',
                'branch' => $branchPoint,
                'points' => $points,
                'coordinates' => [],
            ]);
        }

        $routePoints = collect([$branchPoint])
            ->merge($points)
            ->push($branchPoint)
            ->values();

        $coordinateString = $routePoints
            ->map(function ($point) {
                return $point['longitude'] . ',' . $point['latitude'];
            })
            ->implode(';');

        $coordinates = [];
        $source = 'fallback';

        try {
            $response = Http::timeout(10)->get('https://router.project-osrm.org/route/v1/driving/' . $coordinateString, [
                'overview' => 'full',
                'geometries' => 'geojson',
                'steps' => 'false',
                'alternatives' => 'false',
            ]);

            if ($response->ok()) {
                $coordinates = data_get($response->json(), 'routes.0.geometry.coordinates', []);
                if (count($coordinates) > 1) {
                    $source = 'osrm';
                } else {
                    $coordinates = [];
                }
            }
        } catch (\Throwable $exception) {
            $coordinates = [];
        }

        if (!$coordinates) {
            try {
                $valhallaResponse = Http::timeout(10)->get('https://valhalla1.openstreetmap.de/route', [
                    'json' => json_encode([
                        'locations' => $routePoints->map(function ($point) {
                            return [
                                'lat' => (float) $point['latitude'],
                                'lon' => (float) $point['longitude'],
                            ];
                        })->values()->all(),
                        'costing' => 'auto',
                        'directions_options' => [
                            'units' => 'kilometers',
                        ],
                    ], JSON_UNESCAPED_SLASHES),
                ]);

                if ($valhallaResponse->ok()) {
                    $trip = data_get($valhallaResponse->json(), 'trip', []);
                    $legs = data_get($trip, 'legs', []);
                    $coordinates = collect($legs)
                        ->map(function ($leg) {
                            return data_get($leg, 'shape');
                        })
                        ->filter()
                        ->flatMap(function ($shape) {
                            return $this->decodePolyline($shape, 6);
                        })
                        ->values()
                        ->all();

                    if (count($coordinates) > 1) {
                        $source = 'valhalla';
                    } else {
                        $coordinates = [];
                    }
                }
            } catch (\Throwable $exception) {
                $coordinates = [];
            }
        }

        if (!$coordinates) {
            $coordinates = $points->map(function ($point) {
                return [(float) $point['longitude'], (float) $point['latitude']];
            })->values()->all();
        }

        return response()->json([
            'source' => $source,
            'branch' => $branchPoint,
            'points' => $points,
            'coordinates' => $coordinates,
        ]);
    }

    private function decodePolyline(string $encoded, int $precision = 6): array
    {
        $index = 0;
        $latitude = 0;
        $longitude = 0;
        $coordinates = [];
        $factor = pow(10, $precision);

        while ($index < strlen($encoded)) {
            $shift = 0;
            $result = 0;

            do {
                $byte = ord($encoded[$index++]) - 63;
                $result |= ($byte & 0x1f) << $shift;
                $shift += 5;
            } while ($byte >= 0x20 && $index < strlen($encoded));

            $deltaLat = ($result & 1) ? ~($result >> 1) : ($result >> 1);
            $latitude += $deltaLat;

            $shift = 0;
            $result = 0;

            do {
                $byte = ord($encoded[$index++]) - 63;
                $result |= ($byte & 0x1f) << $shift;
                $shift += 5;
            } while ($byte >= 0x20 && $index < strlen($encoded));

            $deltaLng = ($result & 1) ? ~($result >> 1) : ($result >> 1);
            $longitude += $deltaLng;

            $coordinates[] = [
                $longitude / $factor,
                $latitude / $factor,
            ];
        }

        return $coordinates;
    }

    public function initialize(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $this->ensureSlotsForDate($validated['date']);

        return response()->json([
            'message' => 'Tijdsloten aangemaakt voor alle voertuigen.',
        ]);
    }

    public function assignDriver(Request $request, VehicleTimeSlot $timeSlot)
    {
        $validated = $request->validate([
            'driver_id' => ['nullable', 'exists:drivers,id'],
        ]);

        $driverId = $validated['driver_id'] ?? null;

        if ($driverId !== null) {
            $conflict = VehicleTimeSlot::query()
                ->where('id', '!=', $timeSlot->id)
                ->where('slot_date', $timeSlot->slot_date)
                ->where('slot_key', $timeSlot->slot_key)
                ->where('driver_id', $driverId)
                ->exists();

            if ($conflict) {
                return response()->json([
                    'message' => 'Deze chauffeur is al toegewezen aan een ander voertuig in dit tijdslot.',
                ], 422);
            }
        }

        try {
            $timeSlot->update(['driver_id' => $driverId]);
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return response()->json([
                    'message' => 'Deze chauffeur is al toegewezen in dit tijdslot.',
                ], 422);
            }

            throw $exception;
        }

        return response()->json($timeSlot->load(['driver', 'stops']));
    }

    public function syncStops(Request $request, VehicleTimeSlot $timeSlot)
    {
        $validated = $request->validate([
            'stop_ids' => ['array'],
            'stop_ids.*' => ['integer', 'exists:stops,id'],
        ]);

        $stopIds = $validated['stop_ids'] ?? [];
        $syncData = [];
        foreach ($stopIds as $idx => $stopId) {
            $syncData[$stopId] = ['sequence' => $idx + 1];
        }

        if (!empty($stopIds)) {
            $timeSlot->stops()->newPivotStatement()
                ->whereIn('stop_id', $stopIds)
                ->where('vehicle_time_slot_id', '!=', $timeSlot->id)
                ->delete();
        }

        $timeSlot->stops()->sync($syncData);

        return response()->json($timeSlot->load(['driver', 'stops']));
    }

    public function markStopArrived(Request $request, VehicleTimeSlot $timeSlot, \App\Models\Stop $stop)
    {
        // Ensure this stop is attached to the slot
        $attached = $timeSlot->stops()->where('stops.id', $stop->id)->exists();
        if (!$attached) {
            return response()->json(['message' => 'Stop not assigned to this timeslot.'], 404);
        }

        // Check if arrived time is outside the stop's time window (use stop's custom window if present)
        $arrivedAt = now();
        $arrivedTime = $arrivedAt->format('H:i');

        // Prefer stop-specific custom window; fall back to the parent timeslot window
        $stopStart = $stop->custom_start_time ? substr($stop->custom_start_time, 0, 5) : $timeSlot->start_time->format('H:i');
        $stopEnd = $stop->custom_end_time ? substr($stop->custom_end_time, 0, 5) : $timeSlot->end_time->format('H:i');

        // Convert times to minutes for comparison
        [$arrH, $arrM] = explode(':', $arrivedTime);
        [$startH, $startM] = explode(':', $stopStart);
        [$endH, $endM] = explode(':', $stopEnd);

        $arrivalMin = intval($arrH) * 60 + intval($arrM);
        $startMin = intval($startH) * 60 + intval($startM);
        $endMin = intval($endH) * 60 + intval($endM);

        $isLate = $arrivalMin < $startMin || $arrivalMin > $endMin;

        // Update pivot with arrived_at and delivered_late flag
        $timeSlot->stops()->updateExistingPivot($stop->id, [
            'arrived_at' => $arrivedAt,
            'delivered_late' => $isLate,
        ]);

        return response()->json([
            'arrived_at' => $arrivedAt->toDateTimeString(),
            'delivered_late' => $isLate,
        ]);
    }

    public function finishTimeSlot(Request $request, VehicleTimeSlot $timeSlot)
    {
        if (!$timeSlot->returned_to_branch_at) {
            return response()->json([
                'message' => 'Je moet eerst terug zijn bij het filiaal voordat je de rit kunt afronden.',
            ], 422);
        }

        $timeSlot->finished_at = now();
        $timeSlot->save();

        return response()->json(['finished_at' => $timeSlot->finished_at->toDateTimeString()]);
    }

    public function markReturnedToBranch(Request $request, VehicleTimeSlot $timeSlot)
    {
        $timeSlot->returned_to_branch_at = now();
        $timeSlot->save();

        return response()->json([
            'returned_to_branch_at' => $timeSlot->returned_to_branch_at->toDateTimeString(),
        ]);
    }

    private function ensureSlotsForDate(string $date): void
    {
        $vehicles = Vehicles::query()->select('id')->get();
        $slotWindows = TimeSlotWindow::map();

        foreach ($vehicles as $vehicle) {
            foreach ($slotWindows as $slotKey => [$startTime, $endTime]) {
                VehicleTimeSlot::firstOrCreate(
                    [
                        'vehicle_id' => $vehicle->id,
                        'slot_date' => $date,
                        'slot_key' => $slotKey,
                    ],
                    [
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                    ]
                );
            }
        }
    }
}
