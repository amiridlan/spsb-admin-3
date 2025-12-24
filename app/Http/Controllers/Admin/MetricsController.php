<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSpace;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MetricsController extends Controller
{
    /**
     * Display booking metrics and statistics
     */
    public function index(Request $request): Response
    {
        $dateRange = $this->getDateRange($request);

        return Inertia::render('admin/metrics/Index', [
            'dateRange' => [
                'start' => $dateRange['start']->format('Y-m-d'),
                'end' => $dateRange['end']->format('Y-m-d'),
            ],
            'overview' => $this->getOverviewMetrics($dateRange),
            'bookingTrends' => $this->getBookingTrends($dateRange),
            'spaceMetrics' => $this->getSpaceMetrics($dateRange),
            'statusMetrics' => $this->getStatusMetrics($dateRange),
            'staffMetrics' => $this->getStaffMetrics($dateRange),
            'timeMetrics' => $this->getTimeMetrics($dateRange),
            'clientMetrics' => $this->getClientMetrics($dateRange),
        ]);
    }

    /**
     * Get date range from request or default to current month
     */
    protected function getDateRange(Request $request): array
    {
        $start = $request->start
            ? Carbon::parse($request->start)
            : Carbon::now()->startOfMonth();

        $end = $request->end
            ? Carbon::parse($request->end)
            : Carbon::now()->endOfMonth();

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Get overview metrics
     */
    protected function getOverviewMetrics(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $totalBookings = Event::whereBetween('start_date', [$start, $end])->count();
        $confirmedBookings = Event::whereBetween('start_date', [$start, $end])
            ->where('status', 'confirmed')
            ->count();
        $totalDays = Event::whereBetween('start_date', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->get()
            ->sum(fn($event) => $event->start_date->diffInDays($event->end_date) + 1);

        $avgDuration = $totalBookings > 0
            ? round($totalDays / $totalBookings, 1)
            : 0;

        // Comparison with previous period
        $periodLength = $start->diffInDays($end);
        $prevStart = $start->copy()->subDays($periodLength + 1);
        $prevEnd = $start->copy()->subDay();

        $prevBookings = Event::whereBetween('start_date', [$prevStart, $prevEnd])->count();
        $bookingGrowth = $prevBookings > 0
            ? round((($totalBookings - $prevBookings) / $prevBookings) * 100, 1)
            : 0;

        return [
            'total_bookings' => $totalBookings,
            'confirmed_bookings' => $confirmedBookings,
            'pending_bookings' => Event::whereBetween('start_date', [$start, $end])
                ->where('status', 'pending')
                ->count(),
            'cancelled_bookings' => Event::whereBetween('start_date', [$start, $end])
                ->where('status', 'cancelled')
                ->count(),
            'avg_duration' => $avgDuration,
            'total_days' => $totalDays,
            'booking_growth' => $bookingGrowth,
            'confirmation_rate' => $totalBookings > 0
                ? round(($confirmedBookings / $totalBookings) * 100, 1)
                : 0,
        ];
    }

    /**
     * Get booking trends over time
     */
    protected function getBookingTrends(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $trends = Event::select(
            DB::raw('DATE(start_date) as date'),
            DB::raw('count(*) as count'),
            'status'
        )
            ->whereBetween('start_date', [$start, $end])
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get();

        // Group by date
        $trendsByDate = [];
        foreach ($trends as $trend) {
            $date = $trend->date;
            if (!isset($trendsByDate[$date])) {
                $trendsByDate[$date] = [
                    'date' => $date,
                    'total' => 0,
                    'pending' => 0,
                    'confirmed' => 0,
                    'completed' => 0,
                    'cancelled' => 0,
                ];
            }
            $trendsByDate[$date]['total'] += $trend->count;
            $trendsByDate[$date][$trend->status] = $trend->count;
        }

        return array_values($trendsByDate);
    }

    /**
     * Get space utilization metrics
     */
    protected function getSpaceMetrics(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $spaceStats = EventSpace::withCount([
            'events' => fn($query) => $query
                ->whereBetween('start_date', [$start, $end])
                ->where('status', '!=', 'cancelled')
        ])
            ->where('is_active', true)
            ->get()
            ->map(function ($space) use ($start, $end) {
                $events = Event::where('event_space_id', $space->id)
                    ->whereBetween('start_date', [$start, $end])
                    ->where('status', '!=', 'cancelled')
                    ->get();

                $totalDays = $events->sum(
                    fn($event) =>
                    $event->start_date->diffInDays($event->end_date) + 1
                );

                $periodDays = $start->diffInDays($end) + 1;
                $utilization = $periodDays > 0
                    ? round(($totalDays / $periodDays) * 100, 1)
                    : 0;

                return [
                    'id' => $space->id,
                    'name' => $space->name,
                    'booking_count' => $space->events_count,
                    'total_days' => $totalDays,
                    'utilization_rate' => $utilization,
                ];
            })
            ->sortByDesc('booking_count')
            ->values();

        return $spaceStats->toArray();
    }

    /**
     * Get status distribution metrics
     */
    protected function getStatusMetrics(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $statusCounts = Event::select('status', DB::raw('count(*) as count'))
            ->whereBetween('start_date', [$start, $end])
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status => $item->count]);

        return [
            'pending' => $statusCounts['pending'] ?? 0,
            'confirmed' => $statusCounts['confirmed'] ?? 0,
            'completed' => $statusCounts['completed'] ?? 0,
            'cancelled' => $statusCounts['cancelled'] ?? 0,
        ];
    }

    /**
     * Get staff assignment metrics
     */
    protected function getStaffMetrics(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $staffStats = Staff::with('user')
            ->get()
            ->map(function ($staff) use ($start, $end) {
                $assignments = $staff->events()
                    ->whereBetween('start_date', [$start, $end])
                    ->where('status', '!=', 'cancelled')
                    ->count();

                $totalDays = $staff->events()
                    ->whereBetween('start_date', [$start, $end])
                    ->where('status', '!=', 'cancelled')
                    ->get()
                    ->sum(fn($event) => $event->start_date->diffInDays($event->end_date) + 1);

                return [
                    'id' => $staff->id,
                    'name' => $staff->user->name,
                    'position' => $staff->position,
                    'assignment_count' => $assignments,
                    'total_days' => $totalDays,
                ];
            })
            ->sortByDesc('assignment_count')
            ->values();

        return $staffStats->toArray();
    }

    /**
     * Get time-based metrics (day of week, hour patterns)
     */
    protected function getTimeMetrics(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        // Day of week distribution
        $dayOfWeek = Event::select(
            DB::raw('DAYOFWEEK(start_date) as day'),
            DB::raw('count(*) as count')
        )
            ->whereBetween('start_date', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->mapWithKeys(function ($item) {
                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                return [$days[$item->day - 1] => $item->count];
            });

        // Lead time (days between booking creation and event start)
        $leadTimes = Event::whereBetween('start_date', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->get()
            ->map(fn($event) => Carbon::parse($event->created_at)->diffInDays($event->start_date))
            ->values();

        $avgLeadTime = $leadTimes->count() > 0 ? round($leadTimes->avg(), 1) : 0;

        return [
            'day_of_week' => $dayOfWeek->toArray(),
            'avg_lead_time' => $avgLeadTime,
            'min_lead_time' => $leadTimes->min() ?? 0,
            'max_lead_time' => $leadTimes->max() ?? 0,
        ];
    }

    /**
     * Get client metrics
     */
    protected function getClientMetrics(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        // Top clients by booking count
        $topClients = Event::select('client_name', DB::raw('count(*) as booking_count'))
            ->whereBetween('start_date', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->groupBy('client_name')
            ->orderBy('booking_count', 'desc')
            ->limit(10)
            ->get();

        // New vs returning clients
        $clientEmails = Event::whereBetween('start_date', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->pluck('client_email')
            ->unique();

        $returningClients = 0;
        foreach ($clientEmails as $email) {
            $previousBookings = Event::where('client_email', $email)
                ->where('start_date', '<', $start)
                ->exists();

            if ($previousBookings) {
                $returningClients++;
            }
        }

        $newClients = $clientEmails->count() - $returningClients;

        return [
            'top_clients' => $topClients->toArray(),
            'unique_clients' => $clientEmails->count(),
            'new_clients' => $newClients,
            'returning_clients' => $returningClients,
            'return_rate' => $clientEmails->count() > 0
                ? round(($returningClients / $clientEmails->count()) * 100, 1)
                : 0,
        ];
    }
}
