<?php

namespace App\Http\Controllers;

use App\Models\Vehicles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VehicleController extends Controller
{
    /**
     * Fetch all data for a license plate from RDW Open Data.
     */
    public function infoByLicensePlate(string $licensePlate)
    {
        $normalizedPlate = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $licensePlate));

        if ($normalizedPlate === '') {
            return response()->json([
                'message' => 'Ongeldig kenteken.',
            ], 422);
        }

        $response = Http::acceptJson()
            ->timeout(10)
            ->get('https://opendata.rdw.nl/resource/m9d7-ebf2.json', [
                'kenteken' => $normalizedPlate,
                '$limit' => 1,
            ]);

        if (!$response->ok()) {
            return response()->json([
                'message' => 'RDW request mislukt.',
                'status' => $response->status(),
            ], 502);
        }

        $data = $response->json();

        if (!is_array($data) || count($data) === 0) {
            return response()->json([
                'message' => 'Geen gegevens gevonden voor dit kenteken.',
            ], 404);
        }

        return response()->json($data[0]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Vehicles::latest()->get());
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
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'fuel_type' => ['required', 'string', 'max:100'],
            'license_plate' => ['required', 'string', 'max:50', 'unique:vehicles,license_plate'],
        ]);

        $vehicle = Vehicles::create($validated);

        return response()->json($vehicle, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicles $vehicle)
    {
        return response()->json($vehicle);
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
    public function update(Request $request, Vehicles $vehicle)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'brand' => ['sometimes', 'required', 'string', 'max:255'],
            'model' => ['sometimes', 'required', 'string', 'max:255'],
            'fuel_type' => ['sometimes', 'required', 'string', 'max:100'],
            'license_plate' => ['sometimes', 'required', 'string', 'max:50', 'unique:vehicles,license_plate,' . $vehicle->id],
        ]);

        $vehicle->update($validated);

        return response()->json($vehicle);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicles $vehicle)
    {
        $vehicle->delete();

        return response()->json([
            'message' => 'Vehicle deleted successfully.',
        ]);
    }

    /**
     * Refresh the API token for a vehicle.
     */
    public function refreshToken(Vehicles $vehicle)
    {
        $vehicle->api_token = Vehicles::generateApiToken();
        $vehicle->save();

        return response()->json([
            'api_token' => $vehicle->api_token,
            'message' => 'API token vernieuwd.',
        ]);
    }
}
