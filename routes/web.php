<?php

use App\Http\Controllers\Admin\ApiTokenController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventSpaceController;
use App\Http\Controllers\Admin\EventStaffController;
use App\Http\Controllers\Admin\LeaveRequestController as AdminLeaveRequestController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Head\LeaveRequestController as HeadLeaveRequestController;
use App\Http\Controllers\Staff\AssignmentController;
use App\Http\Controllers\Staff\LeaveRequestController as StaffLeaveRequestController;
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
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');

    // Admin routes
    Route::middleware(['role:superadmin,admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('departments', DepartmentController::class);
        Route::resource('event-spaces', EventSpaceController::class);
        Route::resource('events', EventController::class);
        Route::resource('staff', StaffController::class);

        // Staff leave management
        Route::post('staff/{staff}/adjust-leave', [StaffController::class, 'adjustLeave'])
            ->name('staff.adjust-leave');
        Route::patch('staff/{staff}/leave-notes', [StaffController::class, 'updateLeaveNotes'])
            ->name('staff.leave-notes');

        // Leave request management
        Route::prefix('leave')->name('leave.')->group(function () {
            Route::get('requests', [AdminLeaveRequestController::class, 'index'])
                ->name('requests.index');
            Route::get('requests/{id}', [AdminLeaveRequestController::class, 'show'])
                ->name('requests.show');
            Route::post('requests/{id}/approve', [AdminLeaveRequestController::class, 'approve'])
                ->name('requests.approve');
            Route::post('requests/{id}/reject', [AdminLeaveRequestController::class, 'reject'])
                ->name('requests.reject');

            // HR approval routes (first step in multi-level approval)
            Route::prefix('hr')->name('hr.')->group(function () {
                Route::get('pending', [AdminLeaveRequestController::class, 'hrIndex'])
                    ->name('pending');
                Route::post('{id}/approve', [AdminLeaveRequestController::class, 'hrApprove'])
                    ->name('approve');
                Route::post('{id}/reject', [AdminLeaveRequestController::class, 'hrReject'])
                    ->name('reject');
            });
        });

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

        // API Token management
        Route::get('api-tokens', [ApiTokenController::class, 'index'])
            ->name('api-tokens.index');
        Route::post('api-tokens', [ApiTokenController::class, 'store'])
            ->name('api-tokens.store');
        Route::delete('api-tokens/all', [ApiTokenController::class, 'destroyAll'])
            ->name('api-tokens.destroy-all');
        Route::delete('api-tokens/{tokenId}', [ApiTokenController::class, 'destroy'])
            ->name('api-tokens.destroy');
    });

    // Staff routes (for staff role users and heads of department with staff profiles)
    Route::middleware(['role:staff,head_of_department,admin,superadmin'])->prefix('staff')->name('staff.')->group(function () {
        Route::get('assignments', [AssignmentController::class, 'index'])
            ->name('assignments.index');
        Route::get('assignments/calendar', [AssignmentController::class, 'calendar'])
            ->name('assignments.calendar');
        Route::get('assignments/{event}', [AssignmentController::class, 'show'])
            ->name('assignments.show');

        // Leave request routes
        Route::prefix('leave')->name('leave.')->group(function () {
            Route::get('requests', [StaffLeaveRequestController::class, 'index'])
                ->name('requests.index');
            Route::get('requests/create', [StaffLeaveRequestController::class, 'create'])
                ->name('requests.create');
            Route::post('requests', [StaffLeaveRequestController::class, 'store'])
                ->name('requests.store');
            Route::get('requests/{id}', [StaffLeaveRequestController::class, 'show'])
                ->name('requests.show');
            Route::post('requests/{id}/cancel', [StaffLeaveRequestController::class, 'cancel'])
                ->name('requests.cancel');
            Route::get('balance', [StaffLeaveRequestController::class, 'balance'])
                ->name('balance');
        });
    });

    // Department Head routes (for leave approval)
    Route::middleware(['role:head_of_department'])->prefix('head')->name('head.')->group(function () {
        Route::prefix('leave')->name('leave.')->group(function () {
            Route::get('requests', [HeadLeaveRequestController::class, 'index'])
                ->name('requests.index');
            Route::get('requests/{id}', [HeadLeaveRequestController::class, 'show'])
                ->name('requests.show');
            Route::post('requests/{id}/approve', [HeadLeaveRequestController::class, 'approve'])
                ->name('requests.approve');
            Route::post('requests/{id}/reject', [HeadLeaveRequestController::class, 'reject'])
                ->name('requests.reject');
        });
    });
});

require __DIR__ . '/settings.php';

// API Documentation - restricted to non-production environments
Route::middleware(['restrict.docs'])->group(function () {
    Route::get('/docs', function () {
        return file_get_contents(public_path('docs/index.html'));
    })->name('api.docs');
});

