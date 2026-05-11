<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DamageReportController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\RoutePlannerController;
use App\Http\Controllers\StopController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\ArduinoWebhookController;

Route::post('/webhook', [WebhookController::class, 'handle']);
Route::post('/webhook/vehicle-telemetry', [ArduinoWebhookController::class, 'receiveTelemetry']);
Route::get('/vehicles-info/{licensePlate}', [VehicleController::class, 'infoByLicensePlate']);
Route::post('/vehicles/{vehicle}/refresh-token', [VehicleController::class, 'refreshToken']);
Route::apiResource('/vehicles', VehicleController::class);
Route::apiResource('/drivers', DriverController::class);
Route::apiResource('/stops', StopController::class);
Route::apiResource('/damage-reports', DamageReportController::class);
Route::get('/route-planner', [RoutePlannerController::class, 'index']);
Route::get('/route-planner/time-slots/{timeSlot}', [RoutePlannerController::class, 'showTimeSlot']);
Route::get('/route-planner/time-slots/{timeSlot}/route', [RoutePlannerController::class, 'routeGeometry']);
Route::post('/route-planner/initialize', [RoutePlannerController::class, 'initialize']);
Route::put('/route-planner/time-slots/{timeSlot}/driver', [RoutePlannerController::class, 'assignDriver']);
Route::put('/route-planner/time-slots/{timeSlot}/stops', [RoutePlannerController::class, 'syncStops']);
Route::post('/route-planner/time-slots/{timeSlot}/stops/{stop}/arrive', [RoutePlannerController::class, 'markStopArrived']);
Route::post('/route-planner/time-slots/{timeSlot}/return', [RoutePlannerController::class, 'markReturnedToBranch']);
Route::post('/route-planner/time-slots/{timeSlot}/finish', [RoutePlannerController::class, 'finishTimeSlot']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::get('/route/{route_date}', function (Request $request){
//     return $request->route();
// })->middleware()