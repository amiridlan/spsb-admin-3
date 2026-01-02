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
    public function index(Request $request)
    {
        $user = $request->user();

        // Staff can view calendar but with read-only access
        $canEdit = in_array($user->role, ['superadmin', 'admin']);

        // Get filters
        $spaceId = $request->input('space');
        $status = $request->input('status');
        $view = $request->input('view', 'dayGridMonth');
        $showCancelled = $request->boolean('show_cancelled', false);

        // Base query - all events visible to staff and admins
        $query = Event::with('eventSpace');

        // Apply filters
        if ($spaceId) {
            $query->where('event_space_id', $spaceId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if (!$showCancelled) {
            $query->where('status', '!=', 'cancelled');
        }

        $events = $query->get();

        // Format events for FullCalendar
        $formattedEvents = $events->map(function ($event) {
            $colors = $this->getStatusColors($event->status);

            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date,
                'end' => Carbon::parse($event->end_date)->addDay()->toDateString(),
                'backgroundColor' => $colors['background'],
                'borderColor' => $colors['border'],
                'extendedProps' => [
                    'status' => $event->status,
                    'space' => $event->eventSpace->name,
                    'space_id' => $event->event_space_id,
                    'client' => $event->client_name,
                    'description' => $event->description,
                ],
            ];
        });

        $spaces = EventSpace::where('is_active', true)->get();

        return Inertia::render('Calendar', [
            'events' => $formattedEvents,
            'spaces' => $spaces,
            'filters' => [
                'space' => $spaceId,
                'status' => $status,
                'view' => $view,
                'show_cancelled' => $showCancelled,
            ],
            'canEdit' => $canEdit, // Pass this to the frontend
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
