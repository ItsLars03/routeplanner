<?php

namespace App\Http\Controllers;

use App\Models\Stop;
use App\Models\TimeSlotWindow;
use Illuminate\Http\Request;

class StopController extends Controller
{
    /**
     * Display a listing of stops.
     */
    public function index()
    {
        return Stop::all();
    }

    /**
     * Store a newly created stop.
     */
    public function store(Request $request)
    {
        $allowedSlots = TimeSlotWindow::allowedKeys();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'is_active' => 'nullable|boolean',
            'date' => 'date_format:Y-m-d|after:1970-01-01',
            'slot_key' => ['nullable', 'string', 'in:' . implode(',', $allowedSlots)],
            'custom_start_time' => 'nullable|date_format:H:i',
            'custom_end_time' => 'nullable|date_format:H:i',
        ]);

        $slotKey = $validated['slot_key'] ?? null;
        $customStartTime = $validated['custom_start_time'] ?? null;
        $customEndTime = $validated['custom_end_time'] ?? null;

        if (empty($slotKey)) {
            if (empty($customStartTime) || empty($customEndTime)) {
                return response()->json([
                    'message' => 'Kies een vast tijdslot of vul beide custom tijden in.',
                ], 422);
            }
        } else {
            $validated['custom_start_time'] = null;
            $validated['custom_end_time'] = null;
        }

        $validated['is_active'] = true;

        \Log::info('Stop created', ['data' => $validated]);

        return response()->json(Stop::create($validated), 201);
    }

    /**
     * Display the specified stop.
     */
    public function show(Stop $stop)
    {
        return $stop;
    }

    /**
     * Update the specified stop.
     */
    public function update(Request $request, Stop $stop)
    {
        $allowedSlots = TimeSlotWindow::allowedKeys();

        $validated = $request->validate([
            'name' => 'string|max:255',
            'address' => 'string|max:255',
            'latitude' => 'numeric|between:-90,90',
            'longitude' => 'numeric|between:-180,180',
            'is_active' => 'nullable|boolean',
            'date' => 'nullable|date_format:Y-m-d|after:1970-01-01',
            'slot_key' => ['nullable', 'string', 'in:' . implode(',', $allowedSlots)],
            'custom_start_time' => 'nullable|date_format:H:i',
            'custom_end_time' => 'nullable|date_format:H:i',
        ]);

        $slotKey = $validated['slot_key'] ?? null;
        $customStartTime = $validated['custom_start_time'] ?? null;
        $customEndTime = $validated['custom_end_time'] ?? null;

        if (!empty($slotKey)) {
            $validated['custom_start_time'] = null;
            $validated['custom_end_time'] = null;
        }

        if (empty($slotKey)) {
            if (empty($customStartTime) xor empty($customEndTime)) {
                return response()->json([
                    'message' => 'Custom tijdslot vereist zowel begin- als eindtijd.',
                ], 422);
            }
        }

        $stop->update($validated);
        \Log::info('Stop updated', ['id' => $stop->id, 'data' => $validated]);
        return response()->json($stop);
    }

    /**
     * Delete the specified stop.
     */
    public function destroy(Stop $stop)
    {
        $stop->delete();
        return response()->noContent();
    }
}
