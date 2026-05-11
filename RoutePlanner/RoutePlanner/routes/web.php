<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchSettingsController;
use App\Http\Controllers\PlannerUserController;
use App\Http\Controllers\RoutePageController;
use App\Http\Controllers\TimeSlotController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('vehicles.page');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/routes', [RoutePageController::class, 'show'])->name('routes.page');
    Route::get('/routes/{date}', [RoutePageController::class, 'show'])->name('routes.page.date');
    Route::get('/route/{date?}', function ($date = null) {
        return redirect()->route('routes.page.date', ['date' => $date ?? now()->format('d-m-Y')]);
    });
    Route::view('/vehicles', 'vehicles')->name('vehicles.page');
    Route::view('/drivers', 'drivers')->name('drivers.page');
    Route::view('/damages', 'damages')->name('damages.page');
    Route::get('/settings', [BranchSettingsController::class, 'edit'])->name('settings.page');
    Route::post('/settings', [BranchSettingsController::class, 'update'])->name('settings.update');

    Route::get('/planners/users', [PlannerUserController::class, 'index'])->name('planners.users.index');
    Route::post('/planners/users', [PlannerUserController::class, 'store'])->name('planners.users.store');
    Route::put('/planners/users/{user}', [PlannerUserController::class, 'update'])->name('planners.users.update');
    Route::delete('/planners/users/{user}', [PlannerUserController::class, 'destroy'])->name('planners.users.destroy');

    Route::get('/timeslots', [TimeSlotController::class, 'index'])->name('timeslots.index');
    Route::post('/timeslots', [TimeSlotController::class, 'store'])->name('timeslots.store');
    Route::delete('/timeslots/{timeSlotWindow}', [TimeSlotController::class, 'destroy'])->name('timeslots.destroy');
});

// Driver-facing delivery management page
Route::get('/deliver', [\App\Http\Controllers\DrivingController::class, 'show'])->name('deliver.page');
Route::post('/deliver/login', [\App\Http\Controllers\DrivingController::class, 'login'])->name('deliver.login');
Route::post('/deliver/logout', [\App\Http\Controllers\DrivingController::class, 'logout'])->name('deliver.logout');
Route::get('/deliver/slot/{timeSlot}', function ($timeSlot) {
    // blade expects route param name `timeSlot` for JS
    return view('deliver_slot', [
        'branchSetting' => \App\Models\BranchSetting::current(),
    ]);
})->name('deliver.slot');
