<?php

namespace App\Http\Controllers;

use App\Models\DamageReport;
use Illuminate\Http\Request;

class DamageReportController extends Controller
{
    private function baseQuery()
    {
        return DamageReport::query()->with([
            'vehicle:id,license_plate',
            'driver:id,email',
        ]);
    }

    private function normalizeResolvedState(array $validated): array
    {
        if (!array_key_exists('status', $validated)) {
            return $validated;
        }

        if ($validated['status'] === 'resolved') {
            if (empty($validated['resolved_at'])) {
                $validated['resolved_at'] = now();
            }

            return $validated;
        }

        $validated['resolved_at'] = null;

        return $validated;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json($this->baseQuery()->latest()->get());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'driver_id' => ['nullable', 'integer', 'exists:drivers,id'],
            'reported_date' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'damage_type' => ['required', 'string', 'max:120'],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'description' => ['required', 'string'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:open,in_progress,resolved,rejected'],
            'resolved_at' => ['nullable', 'date'],
        ]);

        $validated = $this->normalizeResolvedState($validated);

        $damageReport = DamageReport::create($validated);

        return response()->json($this->baseQuery()->findOrFail($damageReport->id), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DamageReport $damageReport)
    {
        return response()->json($this->baseQuery()->findOrFail($damageReport->id));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DamageReport $damageReport)
    {
        $validated = $request->validate([
            'vehicle_id' => ['sometimes', 'nullable', 'integer', 'exists:vehicles,id'],
            'driver_id' => ['sometimes', 'nullable', 'integer', 'exists:drivers,id'],
            'reported_date' => ['sometimes', 'required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'damage_type' => ['sometimes', 'required', 'string', 'max:120'],
            'severity' => ['sometimes', 'required', 'in:low,medium,high,critical'],
            'description' => ['sometimes', 'required', 'string'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'required', 'in:open,in_progress,resolved,rejected'],
            'resolved_at' => ['nullable', 'date'],
        ]);

        $validated = $this->normalizeResolvedState($validated);

        $damageReport->update($validated);

        return response()->json($this->baseQuery()->findOrFail($damageReport->id));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DamageReport $damageReport)
    {
        $damageReport->delete();

        return response()->json([
            'message' => 'Damage report deleted successfully.',
        ]);
    }
}
