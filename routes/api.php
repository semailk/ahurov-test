<?php

use App\Http\Controllers\Api\ReservedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('reserved', [ReservedController::class, 'index']);
Route::get('reserved/block', [ReservedController::class, 'reservedBlock']);
Route::get('reserved/my-bookings', [ReservedController::class, 'reservedHistory']);
