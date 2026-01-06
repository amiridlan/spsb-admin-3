<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventSpace;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Events\Contracts\EventAnalyticsServiceInterface;
use Modules\Events\Contracts\EventServiceInterface;
use Modules\Events\Contracts\EventSpaceServiceInterface;
use Modules\Staff\Contracts\StaffAnalyticsServiceInterface;

class DashboardController extends Controller
{
    public function __construct(
        protected EventAnalyticsServiceInterface $eventAnalytics,
        protected EventServiceInterface $eventService,
        protected EventSpaceServiceInterface $eventSpaceService,
        protected StaffAnalyticsServiceInterface $staffAnalytics
    ) {}
    /**
     * Display the dashboard based on user role
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        return match ($user->role) {
            'superadmin', 'admin' => $this->adminDashboard($user),
            'staff' => $this->staffDashboard($user),
            default => abort(403, 'Unauthorized'),
        };
    }

    /**
     * Admin/Superadmin Dashboard
     */
    protected function adminDashboard(User $user): Response
    {
        $today = Carbon::today();

        // Overview Statistics (using service)
        $stats = $this->eventAnalytics->getDashboardStats('admin');
        $stats['total_staff'] = Staff::count(); // Simple count, no complex logic
        $stats['total_users'] = User::count(); // Simple count, no complex logic
        $stats['month_revenue'] = 0; // Placeholder for future revenue tracking

        // Upcoming Events (using service)
        $upcomingEvents = $this->eventService->getUpcoming(limit: 10);

        // Recent Bookings (using service)
        $recentBookings = $this->eventService->getRecentBookings(limit: 10);

        // Events by Status (using service)
        $dateRange = ['start' => Carbon::now()->startOfYear(), 'end' => Carbon::now()->endOfYear()];
        $eventsByStatus = $this->eventAnalytics->getStatusMetrics($dateRange);

        // Events by Month (using service)
        $eventsByMonth = $this->eventAnalytics->getEventsByMonth(months: 6);

        // Space Utilization (using service)
        $spaceUtilization = $this->eventSpaceService->getSpaceUtilization(limit: 5);

        // Pending Actions (using service)
        $pendingActions = $this->eventAnalytics->getPendingActions();

        return Inertia::render('Dashboard', [
            'role' => $user->role,
            'stats' => $stats,
            'upcomingEvents' => $upcomingEvents,
            'recentBookings' => $recentBookings,
            'eventsByStatus' => $eventsByStatus,
            'eventsByMonth' => $eventsByMonth,
            'spaceUtilization' => $spaceUtilization,
            'pendingActions' => $pendingActions,
        ]);
    }

    /**
     * Staff Dashboard
     */
    protected function staffDashboard(User $user): Response
    {
        if (!$user->hasStaffProfile()) {
            return Inertia::render('Dashboard', [
                'role' => 'staff',
                'noProfile' => true,
            ]);
        }

        $staff = $user->staffProfile;
        $today = Carbon::today();

        // Staff Statistics (using service)
        $stats = $this->staffAnalytics->getStaffDashboardStats($staff->id);

        // Current Assignments (happening now) - Keep model scope as it's well-encapsulated
        $currentAssignments = $staff->currentAssignments()
            ->with(['eventSpace', 'creator'])
            ->get();

        // Upcoming Assignments (next 30 days) - Keep model query as it's specific to staff context
        $upcomingAssignments = $staff->events()
            ->with(['eventSpace', 'creator'])
            ->where('start_date', '>', $today)
            ->where('start_date', '<=', $today->copy()->addDays(30))
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        // This Week's Schedule - Keep model query as it's specific to staff context
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $weekSchedule = $staff->events()
            ->with(['eventSpace'])
            ->where(function ($query) use ($weekStart, $weekEnd) {
                $query->whereBetween('start_date', [$weekStart, $weekEnd])
                    ->orWhereBetween('end_date', [$weekStart, $weekEnd])
                    ->orWhere(function ($q) use ($weekStart, $weekEnd) {
                        $q->where('start_date', '<=', $weekStart)
                            ->where('end_date', '>=', $weekEnd);
                    });
            })
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_date')
            ->get();

        // Today's Events - Keep model query as it's specific to staff context
        $todayEvents = $staff->events()
            ->with(['eventSpace'])
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->where('status', '!=', 'cancelled')
            ->get();

        return Inertia::render('Dashboard', [
            'role' => 'staff',
            'staff' => $staff->load('user'),
            'stats' => $stats,
            'currentAssignments' => $currentAssignments,
            'upcomingAssignments' => $upcomingAssignments,
            'weekSchedule' => $weekSchedule,
            'todayEvents' => $todayEvents,
        ]);
    }
}
