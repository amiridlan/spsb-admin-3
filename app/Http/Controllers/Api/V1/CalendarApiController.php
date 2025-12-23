<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * Get calendar events in FullCalendar format
     *
     * @param Request $request
     * @return JsonResponse
     */
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
     * Get calendar events for a specific month
     *
     * @param Request $request
     * @return JsonResponse
     */
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
