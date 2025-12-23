<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\CalendarApiController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\EventSpaceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/login', [AuthController::class, 'login']);

    // Event spaces (public)
    Route::get('/event-spaces', [EventSpaceController::class, 'index']);
    Route::get('/event-spaces/{eventSpace}', [EventSpaceController::class, 'show']);

    // Events (public read)
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    Route::post('/events/check-availability', [EventController::class, 'checkAvailability']);

    // Calendar endpoints (public, FullCalendar compatible)
    Route::get('/events/calendar', [CalendarApiController::class, 'index']);
    Route::get('/events/calendar/month', [CalendarApiController::class, 'month']);

    // Booking staff (public read - restricted data)
    Route::get('/bookings/{event}/staff', [BookingController::class, 'staff']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        // Booking creation
        Route::post('/bookings', [EventController::class, 'store']);
    });
});
