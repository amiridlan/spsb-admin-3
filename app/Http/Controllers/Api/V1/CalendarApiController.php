<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;

#[Group('Calendar API', 'FullCalendar-compatible event calendar endpoints')]
class CalendarApiController extends Controller
{
    use ApiResponse;

    /**
     * Status color mapping for calendar events
     */
    private const STATUS_COLORS = [
        'pending' => [
            'background' => '#f59e0b',
            'border' => '#d97706',
            'text' => '#ffffff',
        ],
        'confirmed' => [
            'background' => '#10b981',
            'border' => '#059669',
            'text' => '#ffffff',
        ],
        'completed' => [
            'background' => '#6b7280',
            'border' => '#4b5563',
            'text' => '#ffffff',
        ],
        'cancelled' => [
            'background' => '#ef4444',
            'border' => '#dc2626',
            'text' => '#ffffff',
        ],
    ];

    /**
     * Get Calendar Events
     *
     * Retrieve events formatted for FullCalendar. Returns events with proper color coding based on status.
     *
     * @unauthenticated
     */
    #[QueryParam('start', 'date', 'Start date for filtering (Y-m-d)', required: false, example: '2024-01-01')]
    #[QueryParam('end', 'date', 'End date for filtering (Y-m-d)', required: false, example: '2024-12-31')]
    #[QueryParam('space_id', 'integer', 'Filter by event space ID', required: false, example: 1)]
    #[QueryParam('status', 'string', 'Filter by status (pending, confirmed, completed, cancelled)', required: false, example: 'confirmed')]
    #[QueryParam('include_cancelled', 'boolean', 'Include cancelled events', required: false, example: false)]
    #[Response(['success' => true, 'message' => 'Calendar events retrieved successfully', 'data' => [['id' => 1, 'title' => 'Corporate Meeting', 'start' => '2024-06-15', 'end' => '2024-06-17', 'allDay' => true, 'backgroundColor' => '#10b981', 'borderColor' => '#059669', 'textColor' => '#ffffff', 'extendedProps' => ['status' => 'confirmed', 'space' => 'Grand Ballroom', 'client' => 'John Smith']]]], 200, 'Calendar events list')]
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date'],
            'space_id' => ['nullable', 'exists:event_spaces,id'],
            'status' => ['nullable', 'in:pending,confirmed,completed,cancelled'],
            'include_cancelled' => ['nullable', 'boolean'],
        ]);

        $query = Event::query()->with('eventSpace');

        // Filter by date range if provided
        if ($request->start && $request->end) {
            $startDate = Carbon::parse($request->start);
            $endDate = Carbon::parse($request->end);

            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });
        }

        // Filter by space
        if ($request->space_id) {
            $query->where('event_space_id', $request->space_id);
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        } else {
            // By default, exclude cancelled events
            if (!$request->include_cancelled) {
                $query->where('status', '!=', 'cancelled');
            }
        }

        $events = $query->orderBy('start_date')->get();

        $calendarEvents = $events->map(function ($event) {
            return $this->formatEventForCalendar($event);
        });

        return $this->success($calendarEvents, 'Calendar events retrieved successfully');
    }

    /**
     * Get Monthly Calendar
     *
     * Retrieve events for a specific month, formatted for FullCalendar.
     *
     * @unauthenticated
     */
    #[QueryParam('year', 'integer', 'The year (2000-2100)', required: true, example: 2024)]
    #[QueryParam('month', 'integer', 'The month (1-12)', required: true, example: 6)]
    #[QueryParam('space_id', 'integer', 'Filter by event space ID', required: false, example: 1)]
    #[QueryParam('status', 'string', 'Filter by status (pending, confirmed, completed, cancelled)', required: false, example: 'confirmed')]
    #[QueryParam('include_cancelled', 'boolean', 'Include cancelled events', required: false, example: false)]
    #[Response(['success' => true, 'message' => 'Calendar events for month retrieved successfully', 'data' => ['year' => 2024, 'month' => 6, 'month_name' => 'June', 'start_date' => '2024-06-01', 'end_date' => '2024-06-30', 'events' => [], 'total_events' => 0]], 200, 'Monthly calendar data')]
    public function month(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'space_id' => ['nullable', 'exists:event_spaces,id'],
            'status' => ['nullable', 'in:pending,confirmed,completed,cancelled'],
            'include_cancelled' => ['nullable', 'boolean'],
        ]);

        $year = $validated['year'];
        $month = $validated['month'];

        // Calculate month boundaries
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = Event::query()->with('eventSpace');

        // Filter by month date range
        $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q2) use ($startDate, $endDate) {
                    $q2->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });

        // Filter by space
        if ($request->space_id) {
            $query->where('event_space_id', $request->space_id);
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        } else {
            // By default, exclude cancelled events
            if (!$request->include_cancelled) {
                $query->where('status', '!=', 'cancelled');
            }
        }

        $events = $query->orderBy('start_date')->get();

        $calendarEvents = $events->map(function ($event) {
            return $this->formatEventForCalendar($event);
        });

        return $this->success([
            'year' => $year,
            'month' => $month,
            'month_name' => $startDate->format('F'),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'events' => $calendarEvents,
            'total_events' => $calendarEvents->count(),
        ], 'Calendar events for month retrieved successfully');
    }

    /**
     * Format event for FullCalendar
     *
     * @param Event $event
     * @return array
     */
    private function formatEventForCalendar(Event $event): array
    {
        $statusColors = $this->getStatusColors($event->status);

        $startDate = $event->start_date;
        $endDate = $event->end_date;

        // FullCalendar uses exclusive end dates for all-day events
        $exclusiveEndDate = $endDate->copy()->addDay();

        return [
            'id' => $event->id,
            'title' => $event->title,
            'start' => $startDate->format('Y-m-d'),
            'end' => $exclusiveEndDate->format('Y-m-d'),
            'allDay' => true,
            'backgroundColor' => $statusColors['background'],
            'borderColor' => $statusColors['border'],
            'textColor' => $statusColors['text'],
            'extendedProps' => [
                'status' => $event->status,
                'space' => $event->eventSpace->name,
                'space_id' => $event->eventSpace->id,
                'client' => $event->client_name,
                'description' => $event->description,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'duration_days' => $this->calculateDuration($startDate, $endDate),
            ],
        ];
    }

    /**
     * Calculate duration in days (inclusive)
     */
    private function calculateDuration(Carbon $startDate, Carbon $endDate): int
    {
        return $startDate->diffInDays($endDate) + 1;
    }

    /**
     * Get colors for a specific status
     */
    private function getStatusColors(string $status): array
    {
        return self::STATUS_COLORS[$status] ?? [
            'background' => '#3b82f6',
            'border' => '#2563eb',
            'text' => '#ffffff',
        ];
    }
}
