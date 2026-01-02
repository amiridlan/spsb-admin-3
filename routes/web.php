<?php

use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventSpaceController;
use App\Http\Controllers\Admin\EventStaffController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Staff\AssignmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Calendar route - accessible to all authenticated users
    Route::get('/calendar', [CalendarController::class, 'index'])
        ->name('calendar.index')
        ->middleware(['auth', 'verified']);

    // Admin routes
    Route::middleware(['role:superadmin,admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('event-spaces', EventSpaceController::class);
        Route::resource('events', EventController::class);
        Route::resource('staff', StaffController::class);

        // Metrics and statistics
        Route::get('metrics', [\App\Http\Controllers\Admin\MetricsController::class, 'index'])
            ->name('metrics.index');

        // Reports
        Route::get('reports', [\App\Http\Controllers\Admin\ReportsController::class, 'index'])
            ->name('reports.index');
        Route::post('reports/generate', [\App\Http\Controllers\Admin\ReportsController::class, 'generate'])
            ->name('reports.generate');
        Route::get('reports/export/csv', [\App\Http\Controllers\Admin\ReportsController::class, 'exportCsv'])
            ->name('reports.export.csv');
        Route::get('reports/export/pdf', [\App\Http\Controllers\Admin\ReportsController::class, 'exportPdf'])
            ->name('reports.export.pdf');

        // Data Exports
        Route::prefix('export')->name('export.')->group(function () {
            Route::get('events', [\App\Http\Controllers\Admin\ExportController::class, 'events'])
                ->name('events');
            Route::get('spaces', [\App\Http\Controllers\Admin\ExportController::class, 'spaces'])
                ->name('spaces');
            Route::get('staff', [\App\Http\Controllers\Admin\ExportController::class, 'staff'])
                ->name('staff');
            Route::get('calendar', [\App\Http\Controllers\Admin\ExportController::class, 'calendar'])
                ->name('calendar');
            Route::get('json', [\App\Http\Controllers\Admin\ExportController::class, 'json'])
                ->name('json');
        });

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
