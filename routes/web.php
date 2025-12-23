<?php

use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventSpaceController;
use App\Http\Controllers\Admin\EventStaffController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Staff\AssignmentController;
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

    // Admin routes
    Route::middleware(['role:superadmin,admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('event-spaces', EventSpaceController::class);
        Route::resource('events', EventController::class);
        Route::resource('staff', StaffController::class);

        // Event staff assignment routes
        Route::get('events/{event}/staff', [EventStaffController::class, 'index'])
            ->name('events.staff.index');
        Route::post('events/{event}/staff', [EventStaffController::class, 'store'])
            ->name('events.staff.store');
        Route::patch('events/{event}/staff/{staff}', [EventStaffController::class, 'update'])
            ->name('events.staff.update');
        Route::delete('events/{event}/staff/{staff}', [EventStaffController::class, 'destroy'])
            ->name('events.staff.destroy');
    });

    // Staff routes (for staff role users)
    Route::middleware(['role:staff,admin,superadmin'])->prefix('staff')->name('staff.')->group(function () {
        Route::get('assignments', [AssignmentController::class, 'index'])
            ->name('assignments.index');
        Route::get('assignments/calendar', [AssignmentController::class, 'calendar'])
            ->name('assignments.calendar');
        Route::get('assignments/{event}', [AssignmentController::class, 'show'])
            ->name('assignments.show');
    });
});

require __DIR__ . '/settings.php';
