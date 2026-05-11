<?php

namespace App\Http\Controllers;

use App\Models\TimeSlotWindow;
use Illuminate\Http\Request;

class TimeSlotController extends Controller
{
    /**
     * Display the timeslots management page.
     */
    public function index()
    {
        $timeSlotWindows = TimeSlotWindow::orderBy('sort_order')
            ->orderBy('slot_key')
            ->get();

        $allTimeSlots = TimeSlotWindow::orderBy('sort_order')
            ->paginate(20);

        return view('timeslots', compact('timeSlotWindows', 'allTimeSlots'));
    }

    /**
     * Store a newly created timeslot.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slot_key' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_\-]+$/'],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $existing = TimeSlotWindow::where('slot_key', $validated['slot_key'])->exists();

        if ($existing) {
            return redirect()->back()->withErrors(['slot_key' => 'Dit tijdslot bestaat al.']);
        }

        TimeSlotWindow::create([
            'slot_key' => $validated['slot_key'],
            'start_time' => $validated['start_time'] . ':00',
            'end_time' => $validated['end_time'] . ':00',
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->back()->with('success', 'Tijdslot toegestaan en opgeslagen.');
    }

    /**
     * Delete the specified timeslot.
     */
    public function destroy(TimeSlotWindow $timeSlotWindow)
    {
        $timeSlotWindow->delete();
        return redirect()->back()->with('success', 'Tijdslot verwijderd.');
    }
}
