<?php

namespace App\Http\Controllers;

use App\Models\BranchSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BranchSettingsController extends Controller
{
    public function edit()
    {
        $branchSetting = BranchSetting::current();

        return view('settings', compact('branchSetting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'branch_address' => ['required', 'string', 'max:255'],
        ]);

        $coordinates = $this->geocodeAddress($validated['branch_address']);

        if (!$coordinates) {
            return back()
                ->withErrors(['branch_address' => 'Kon dit adres niet omzetten naar een locatie.'])
                ->withInput();
        }

        $branchSetting = BranchSetting::current();
        $branchSetting->fill([
            'branch_address' => $validated['branch_address'],
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
        ]);
        $branchSetting->save();

        return back()->with('success', 'Startadres opgeslagen.');
    }

    private function geocodeAddress(string $address): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'RoutePlanner/1.0',
                    'Accept' => 'application/json',
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'format' => 'jsonv2',
                    'limit' => 1,
                    'q' => $address,
                ]);

            if (!$response->ok()) {
                return null;
            }

            $result = $response->json()[0] ?? null;

            if (!$result || !isset($result['lat'], $result['lon'])) {
                return null;
            }

            return [
                'latitude' => (float) $result['lat'],
                'longitude' => (float) $result['lon'],
            ];
        } catch (\Throwable $exception) {
            return null;
        }
    }
}