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

class DashboardController extends Controller
{
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
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Overview Statistics
        $stats = [
            'total_events' => Event::count(),
            'total_spaces' => EventSpace::where('is_active', true)->count(),
            'total_staff' => Staff::count(),
            'total_users' => User::count(),

            // This month statistics
            'month_bookings' => Event::whereBetween('start_date', [$startOfMonth, $endOfMonth])->count(),
            'month_revenue' => 0, // Placeholder for future revenue tracking

            // Status breakdown
            'pending_bookings' => Event::where('status', 'pending')->count(),
            'confirmed_bookings' => Event::where('status', 'confirmed')->count(),
            'completed_bookings' => Event::where('status', 'completed')->count(),
            'cancelled_bookings' => Event::where('status', 'cancelled')->count(),
        ];

        // Upcoming Events (next 30 days)
        $upcomingEvents = Event::with(['eventSpace', 'creator'])
            ->where('start_date', '>=', $today)
            ->where('start_date', '<=', $today->copy()->addDays(30))
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        // Recent Bookings (last 10)
        $recentBookings = Event::with(['eventSpace', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Events by Status (for chart)
        $eventsByStatus = Event::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status => $item->count]);

        // Events by Month (last 6 months)
        $eventsByMonth = Event::select(
            DB::raw('DATE_FORMAT(start_date, "%Y-%m-01") as month'),
            DB::raw('count(*) as count')
        )
            ->where('start_date', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($item) => [
                'month' => Carbon::parse($item->month)->format('M Y'),
                'count' => $item->count,
            ]);

        // Space Utilization (most booked spaces)
        $spaceUtilization = EventSpace::withCount([
            'events' => fn($query) => $query->where('status', '!=', 'cancelled')
        ])
            ->where('is_active', true)
            ->orderBy('events_count', 'desc')
            ->limit(5)
            ->get();

        // Pending Actions
        $pendingActions = [
            'pending_approvals' => Event::where('status', 'pending')->count(),
            'unassigned_staff' => Event::where('status', 'confirmed')
                ->whereDoesntHave('staff')
                ->count(),
            'today_events' => Event::whereDate('start_date', $today)
                ->where('status', '!=', 'cancelled')
                ->count(),
        ];

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

        // Staff Statistics
        $stats = [
            'total_assignments' => $staff->events()->count(),
            'upcoming_assignments' => $staff->upcomingAssignments()->count(),
            'current_assignments' => $staff->currentAssignments()->count(),
            'completed_assignments' => $staff->pastAssignments()->where('status', 'completed')->count(),
        ];

        // Current Assignments (happening now)
        $currentAssignments = $staff->currentAssignments()
            ->with(['eventSpace', 'creator'])
            ->get();

        // Upcoming Assignments (next 30 days)
        $upcomingAssignments = $staff->events()
            ->with(['eventSpace', 'creator'])
            ->where('start_date', '>', $today)
            ->where('start_date', '<=', $today->copy()->addDays(30))
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        // This Week's Schedule
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

        // Today's Events
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
