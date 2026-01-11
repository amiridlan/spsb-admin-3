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
use Modules\Staff\Models\LeaveRequest;

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
            'head_of_department' => $this->headDashboard($user),
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

        // Leave Requests Data
        $pendingLeaveRequests = LeaveRequest::pending()->count();
        $recentLeaveRequests = LeaveRequest::with(['staff.user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Bookings Trend for Graph (last 6 months)
        $bookingsTrend = $this->calculateBookingsTrend();

        // Calendar Events - get events for past 3 months to future 3 months
        // This allows calendar navigation without losing event markers
        $rangeStart = Carbon::now()->subMonths(3)->startOfMonth();
        $rangeEnd = Carbon::now()->addMonths(3)->endOfMonth();

        $allCalendarEvents = $this->eventService->getCalendarEvents();

        // Filter events to include a wider range for calendar navigation
        $filteredEvents = array_filter($allCalendarEvents, function($event) use ($rangeStart, $rangeEnd) {
            $eventStart = Carbon::parse($event['start']);
            // FullCalendar end date is exclusive, so actual last day is end - 1 day
            $eventActualEnd = Carbon::parse($event['end'])->subDay();

            // Include if the event overlaps with our date range
            return $eventStart <= $rangeEnd && $eventActualEnd >= $rangeStart;
        });

        // Re-index array to ensure it's sent as JSON array, not object
        $calendarEvents = array_values($filteredEvents);

        return Inertia::render('Dashboard', [
            'role' => $user->role,
            'stats' => $stats,
            'upcomingEvents' => $upcomingEvents,
            'recentBookings' => $recentBookings,
            'eventsByStatus' => $eventsByStatus,
            'eventsByMonth' => $eventsByMonth,
            'spaceUtilization' => $spaceUtilization,
            'pendingActions' => $pendingActions,
            'bookingsTrend' => $bookingsTrend,
            'calendarEvents' => $calendarEvents,
            'pendingLeaveRequests' => $pendingLeaveRequests,
            'recentLeaveRequests' => $recentLeaveRequests,
        ]);
    }

    /**
     * Calculate bookings trend for the last 6 months
     */
    protected function calculateBookingsTrend(): array
    {
        $months = [];
        $values = [];

        // Get last 6 months data
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            // Count bookings for this month
            $count = Event::whereBetween('start_date', [$startOfMonth, $endOfMonth])
                ->where('status', '!=', 'cancelled')
                ->count();

            $months[] = $date->format('M');
            $values[] = $count;
        }

        // Calculate trend (compare last month vs previous month)
        $lastMonth = end($values);
        $previousMonth = $values[count($values) - 2] ?? 0;

        $change = 0;
        $changeType = 'stable';

        if ($previousMonth > 0) {
            $change = round((($lastMonth - $previousMonth) / $previousMonth) * 100, 1);
            $changeType = $change > 0 ? 'increase' : ($change < 0 ? 'decrease' : 'stable');
        } elseif ($lastMonth > 0) {
            $change = 100;
            $changeType = 'increase';
        }

        return [
            'labels' => $months,
            'values' => $values,
            'change' => abs($change),
            'changeType' => $changeType,
        ];
    }

    /**
     * Head of Department Dashboard
     */
    protected function headDashboard(User $user): Response
    {
        // For now, heads see a simple dashboard
        // In Sprint 3, this will show pending leave requests for their department
        return Inertia::render('Dashboard', [
            'role' => 'head_of_department',
            'stats' => [],
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

        // Leave Balance Data
        $leaveBalances = [
            'annual' => [
                'total' => $staff->annual_leave_total,
                'used' => $staff->annual_leave_used,
                'remaining' => $staff->annual_leave_remaining,
            ],
            'sick' => [
                'total' => $staff->sick_leave_total,
                'used' => $staff->sick_leave_used,
                'remaining' => $staff->sick_leave_remaining,
            ],
            'emergency' => [
                'total' => $staff->emergency_leave_total,
                'used' => $staff->emergency_leave_used,
                'remaining' => $staff->emergency_leave_remaining,
            ],
        ];

        // Leave Requests Data
        $pendingLeaveRequests = LeaveRequest::where('staff_id', $staff->id)
            ->pending()
            ->count();

        return Inertia::render('Dashboard', [
            'role' => 'staff',
            'staff' => $staff->load('user'),
            'stats' => $stats,
            'currentAssignments' => $currentAssignments,
            'upcomingAssignments' => $upcomingAssignments,
            'weekSchedule' => $weekSchedule,
            'todayEvents' => $todayEvents,
            'leaveBalances' => $leaveBalances,
            'pendingLeaveRequests' => $pendingLeaveRequests,
        ]);
    }
}
