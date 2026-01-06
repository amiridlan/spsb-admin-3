<?php

namespace Modules\Events\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Events\Contracts\EventAnalyticsServiceInterface;
use Modules\Events\Models\Event;
use Modules\Events\Models\EventSpace;

class EventAnalyticsService implements EventAnalyticsServiceInterface
{
    public function getStatistics(array $dateRange = []): array
    {
        $start = $dateRange['start'] ?? Carbon::now()->startOfMonth();
        $end = $dateRange['end'] ?? Carbon::now()->endOfMonth();

        // Total events
        $totalEvents = Event::whereBetween('start_date', [$start, $end])->count();

        // Status breakdown
        $pending = Event::whereBetween('start_date', [$start, $end])
            ->where('status', 'pending')
            ->count();

        $confirmed = Event::whereBetween('start_date', [$start, $end])
            ->where('status', 'confirmed')
            ->count();

        $completed = Event::whereBetween('start_date', [$start, $end])
            ->where('status', 'completed')
            ->count();

        $cancelled = Event::whereBetween('start_date', [$start, $end])
            ->where('status', 'cancelled')
            ->count();

        // Calculate total days
        $totalDays = Event::whereBetween('start_date', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->get()
            ->sum(fn($event) => $event->start_date->diffInDays($event->end_date) + 1);

        $avgDuration = $totalEvents > 0 ? round($totalDays / $totalEvents, 1) : 0;

        // Growth comparison with previous period
        $periodLength = $start->diffInDays($end);
        $prevStart = $start->copy()->subDays($periodLength + 1);
        $prevEnd = $start->copy()->subDay();
        $prevBookings = Event::whereBetween('start_date', [$prevStart, $prevEnd])->count();
        $bookingGrowth = $prevBookings > 0
            ? round((($totalEvents - $prevBookings) / $prevBookings) * 100, 1)
            : 0;

        return [
            'total_events' => $totalEvents,
            'total_spaces' => EventSpace::where('is_active', true)->count(),
            'month_bookings' => $totalEvents,
            'pending_bookings' => $pending,
            'confirmed_bookings' => $confirmed,
            'completed_bookings' => $completed,
            'cancelled_bookings' => $cancelled,
            'avg_duration' => $avgDuration,
            'total_days' => $totalDays,
            'booking_growth' => $bookingGrowth,
            'confirmation_rate' => $totalEvents > 0
                ? round(($confirmed / $totalEvents) * 100, 1)
                : 0,
        ];
    }

    public function getBookingTrends(array $dateRange): array
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

    public function getStatusMetrics(array $dateRange): array
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

    public function getTimeMetrics(array $dateRange): array
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

    public function getClientMetrics(array $dateRange): array
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

    public function generateReport(string $type, array $filters): array
    {
        $startDate = $filters['start_date'];
        $endDate = $filters['end_date'];

        $query = Event::with(['eventSpace', 'creator', 'staff.user'])
            ->whereBetween('start_date', [$startDate, $endDate]);

        // Apply filters
        if (isset($filters['space_id'])) {
            $query->where('event_space_id', $filters['space_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        } elseif (!($filters['include_cancelled'] ?? false)) {
            $query->where('status', '!=', 'cancelled');
        }

        $events = $query->orderBy('start_date')->get();

        $data = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'space' => $event->eventSpace->name,
                'client_name' => $event->client_name,
                'client_email' => $event->client_email,
                'client_phone' => $event->client_phone,
                'start_date' => $event->start_date->format('Y-m-d'),
                'end_date' => $event->end_date->format('Y-m-d'),
                'duration' => $event->start_date->diffInDays($event->end_date) + 1,
                'status' => $event->status,
                'staff_count' => $event->staff->count(),
                'created_by' => $event->creator->name,
                'created_at' => $event->created_at->format('Y-m-d H:i'),
            ];
        });

        return [
            'type' => 'bookings',
            'title' => 'Bookings Report',
            'period' => sprintf('%s to %s', $startDate, $endDate),
            'total_count' => $data->count(),
            'data' => $data->toArray(),
            'summary' => [
                'total_bookings' => $data->count(),
                'total_days' => $data->sum('duration'),
                'avg_duration' => $data->count() > 0 ? round($data->avg('duration'), 1) : 0,
                'by_status' => $events->groupBy('status')->map->count(),
            ],
        ];
    }

    public function getDashboardStats(string $role, ?int $userId = null): array
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Overview Statistics
        $stats = [
            'total_events' => Event::count(),
            'total_spaces' => EventSpace::where('is_active', true)->count(),
            'month_bookings' => Event::whereBetween('start_date', [$startOfMonth, $endOfMonth])->count(),
            'pending_bookings' => Event::where('status', 'pending')->count(),
            'confirmed_bookings' => Event::where('status', 'confirmed')->count(),
            'completed_bookings' => Event::where('status', 'completed')->count(),
            'cancelled_bookings' => Event::where('status', 'cancelled')->count(),
        ];

        return $stats;
    }

    public function getPendingActions(): array
    {
        $today = Carbon::today();

        return [
            'pending_approvals' => Event::where('status', 'pending')->count(),
            'unassigned_staff' => Event::where('status', 'confirmed')
                ->whereDoesntHave('staff')
                ->count(),
            'today_events' => Event::whereDate('start_date', $today)
                ->where('status', '!=', 'cancelled')
                ->count(),
        ];
    }

    public function getEventsByMonth(int $months = 6): array
    {
        $eventsByMonth = Event::select(
            DB::raw('DATE_FORMAT(start_date, "%Y-%m-01") as month'),
            DB::raw('count(*) as count')
        )
            ->where('start_date', '>=', Carbon::now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($item) => [
                'month' => Carbon::parse($item->month)->format('M Y'),
                'count' => $item->count,
            ]);

        return $eventsByMonth->toArray();
    }
}

