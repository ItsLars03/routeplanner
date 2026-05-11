<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlannerUserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()->orderBy('name')->orderBy('email')->get();
        $editUser = null;

        if ($request->filled('edit')) {
            $editUser = User::query()->find($request->integer('edit'));
        }

        return view('planners.users', [
            'users' => $users,
            'editUser' => $editUser,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::query()->create($validated);

        return redirect()->route('planners.users.index')->with('status', 'Planner account aangemaakt.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('planners.users.index')->with('status', 'Planner account bijgewerkt.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $isCurrentUser = Auth::id() === $user->id;

        $user->delete();

        if ($isCurrentUser) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Je account is verwijderd.');
        }

        return redirect()->route('planners.users.index')->with('status', 'Planner account verwijderd.');
    }
}
