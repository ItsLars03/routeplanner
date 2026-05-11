<?php

namespace App\Http\Controllers;

use App\Models\Drivers;
use Illuminate\Http\Request;

class DrivingController extends Controller
{
    public function show(Request $request)
    {
        $driverId = $request->session()->get('driver_id');
        $driver = $driverId ? Drivers::find($driverId) : null;

        return view('deliver', [
            'driver' => $driver,
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $driver = Drivers::where('email', $validated['email'])->first();
        if (!$driver) {
            return back()->withErrors(['email' => 'Geen chauffeur gevonden met dit e-mailadres.'])->withInput();
        }

        $request->session()->put('driver_id', $driver->id);
        return redirect()->route('deliver.page');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('driver_id');
        return redirect()->route('deliver.page');
    }
}
