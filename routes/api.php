<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;

Route::post('/storeEvent', [AuthController::class, 'storeEvent']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::get('/fetchEvents', [AuthController::class, 'fetchEvents']);
Route::post('/registerEvent', [AuthController::class, 'registerEvent']);

Route::middleware('auth:sanctum')->get('/getUpcomingEvents', [AuthController::class, 'getUpcomingEvents']);

Route::middleware('auth:sanctum')->get('/getRegisteredEvents', [AuthController::class, 'getRegisteredEvents']);

Route::middleware('auth:sanctum')->post('/registerEvent', [AuthController::class, 'registerEvent']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
