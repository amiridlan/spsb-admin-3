<?php

use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventSpaceController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return inertia('Dashboard');
    })->name('dashboard');

    Route::middleware(['role:superadmin,admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('event-spaces', EventSpaceController::class);
        Route::resource('events', EventController::class);
    });
});

require __DIR__ . '/settings.php';
