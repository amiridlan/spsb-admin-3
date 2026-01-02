<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventSpace;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
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
     * Display the calendar view with advanced filtering
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Build query based on user role
        $query = Event::query()->with(['eventSpace', 'creator']);

        // Staff users only see their assigned events
        if ($user->isStaff() && !$user->canManageUsers()) {
            if (!$user->hasStaffProfile()) {
                abort(403, 'You do not have a staff profile.');
            }

            $query->whereHas('staff', function ($q) use ($user) {
                $q->where('staff.id', $user->staffProfile->id);
            });
        }

        // Apply space filter
        if ($request->space) {
            $query->where('event_space_id', $request->space);
        }

        // Apply status filter
        if ($request->status) {
            $query->where('status', $request->status);
        } else {
            // By default, exclude cancelled events unless explicitly requested
            if (!$request->show_cancelled) {
                $query->where('status', '!=', 'cancelled');
            }
        }

        // Apply show_cancelled filter
        if ($request->show_cancelled && !$request->status) {
            // Include all statuses including cancelled
            // No additional filtering needed
        }

        // Get events
        $events = $query->orderBy('start_date')->get();

        // Format events for FullCalendar
        $calendarEvents = $events->map(function ($event) {
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
        });

        // Get all active event spaces for filter
        $spaces = EventSpace::where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Calendar', [
            'events' => $calendarEvents,
            'spaces' => $spaces,
            'filters' => [
                'space' => $request->space ? (int) $request->space : null,
                'status' => $request->status,
                'view' => $request->view ?? 'dayGridMonth',
                'show_cancelled' => $request->show_cancelled ? true : false,
            ],
        ]);
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
