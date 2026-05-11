<?php

namespace App\Http\Controllers;

use App\Models\Vehicles;
use Illuminate\Http\Request;

class ArduinoWebhookController extends Controller
{
    /**
     * Receive telemetry data from Arduino on vehicle
     * 
     * POST /api/webhook/vehicle-telemetry
     * Headers: Authorization: Bearer <api_token>
     * Body: { latitude, longitude, temperature }
     */
    public function receiveTelemetry(Request $request)
    {
        $token = $this->extractBearerToken($request);
        
        if (!$token) {
            return response()->json(['error' => 'Missing authorization token'], 401);
        }

        // Find vehicle by API token
        $vehicle = Vehicles::where('api_token', $token)->first();
        
        if (!$vehicle) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }

        // Validate telemetry data
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'temperature' => ['nullable', 'numeric'],
        ]);

        // Store telemetry in cache or database
        // For now, we'll store it in the vehicle as latest telemetry
        $vehicle->update([
            'last_latitude' => $validated['latitude'],
            'last_longitude' => $validated['longitude'],
            'last_temperature' => $validated['temperature'] ?? null,
            'last_telemetry_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Telemetry received',
            'vehicle_id' => $vehicle->id,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Extract Bearer token from request header
     */
    private function extractBearerToken(Request $request): ?string
    {
        $auth = $request->header('Authorization');
        
        if (!$auth || !str_starts_with($auth, 'Bearer ')) {
            return null;
        }

        return substr($auth, 7); // Remove 'Bearer ' prefix
    }
}
