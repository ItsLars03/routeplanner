<?php

namespace App\Http\Controllers;

use App\Models\Drivers;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Drivers::latest()->get());
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
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:drivers,email'],
        ]);

        $driver = Drivers::create($validated);

        return response()->json($driver, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Drivers $driver)
    {
        return response()->json($driver);
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
    public function update(Request $request, Drivers $driver)
    {
        $validated = $request->validate([
            'firstname' => ['sometimes', 'required', 'string', 'max:255'],
            'lastname' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:drivers,email,' . $driver->id],
        ]);

        $driver->update($validated);

        return response()->json($driver);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Drivers $driver)
    {
        $driver->delete();

        return response()->json([
            'message' => 'Driver deleted successfully.',
        ]);
    }
}
