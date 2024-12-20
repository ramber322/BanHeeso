<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\AuthController;

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});


//new below
Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'showUserInfo']);
Route::middleware('auth:sanctum')->put('/user', [AuthController::class, 'updateUser']);

// Event-related routes
Route::middleware('auth:sanctum')->prefix('events')->group(function () {
    Route::get('upcomingEvents', [EventController::class, 'getUpcomingEvents']);
    Route::get('registeredEvents', [EventController::class, 'getRegisteredEvents']);
    Route::get('calendarEvents', [EventController::class, 'getEventsthroughCalendar']);

    Route::post('{event}/feedback', [EventController::class, 'submitFeedback']);
    Route::post('{event}/register', [EventController::class, 'registerEvent']);
    Route::get('{event}/feedback', [EventController::class, 'getFeedback']);
});



// CRUD ROUTES, current store,index
Route::middleware('auth:sanctum')->resource('events', EventController::class)->except([
    'create', 'edit'
]);




