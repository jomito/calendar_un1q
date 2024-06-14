<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EventsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('events', [EventsController::class, 'addEvent']);
Route::put('events/{eventId}', [EventsController::class, 'updateEvent']);
Route::get('events', [EventsController::class, 'listEvents']);
Route::get('events/schedule', [EventsController::class, 'getSchedule']);
Route::delete('events/{eventId}', [EventsController::class, 'deleteEvent']);
