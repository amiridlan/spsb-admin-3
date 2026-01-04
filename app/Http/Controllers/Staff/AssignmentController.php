<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\StaffAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssignmentController extends Controller
{
    public function __construct(
        protected StaffAvailabilityService $availabilityService
    ) {}

    /**
     * Display staff member's assignments
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Check if user has staff profile
        if (!$user->hasStaffProfile()) {
            abort(403, 'You do not have a staff profile.');
        }

        $staff = $user->staffProfile;
        $staff->load('user'); // Ensure user relationship is loaded

        // Get filter parameters
        $filter = $request->input('filter', 'upcoming'); // upcoming, current, past, all

        $query = $staff->events()
            ->with(['eventSpace', 'creator'])
            ->where('status', '!=', 'cancelled');

        $today = Carbon::today();

        // Apply filters
        switch ($filter) {
            case 'current':
                $query->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
                break;
            case 'upcoming':
                $query->where('start_date', '>', $today);
                break;
            case 'past':
                $query->where('end_date', '<', $today);
                break;
            case 'all':
                // No additional filter
                break;
        }

        $assignments = $query->orderBy('start_date', $filter === 'past' ? 'desc' : 'asc')
            ->paginate(15)
            ->withQueryString();

        // Get counts for filters
        $counts = [
            'current' => $staff->currentAssignments()->count(),
            'upcoming' => $staff->upcomingAssignments()->count(),
            'past' => $staff->pastAssignments()->count(),
        ];

        // Get ALL events for calendar (not just assigned ones)
        $calendarEvents = $this->getCalendarEvents($staff->id);

        return Inertia::render('staff/assignments/Index', [
            'staff' => [
                'id' => $staff->id,
                'position' => $staff->position,
                'specializations' => $staff->specializations,
                'is_available' => $staff->is_available,
                'notes' => $staff->notes,
                'user' => [
                    'id' => $staff->user->id,
                    'name' => $staff->user->name,
                    'email' => $staff->user->email,
                ],
            ],
            'assignments' => $assignments,
            'calendarEvents' => $calendarEvents,
            'filter' => $filter,
            'counts' => $counts,
        ]);
    }

    /**
     * Get all calendar events and mark which ones the staff is assigned to
     */
    protected function getCalendarEvents(int $staffId): array
    {
        $events = Event::with(['eventSpace'])
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_date')
            ->get();

        // Get staff's assigned event IDs
        $assignedEventIds = Event::whereHas('staff', function ($query) use ($staffId) {
            $query->where('staff_id', $staffId);
        })->pluck('id')->toArray();

        return $events->map(function ($event) use ($assignedEventIds) {
            $isAssigned = in_array($event->id, $assignedEventIds);

            // Use green color for assigned events, otherwise use status colors
            if ($isAssigned) {
                $backgroundColor = '#10b981'; // green-500
                $borderColor = '#059669'; // green-600
            } else {
                // Use status colors
                $backgroundColor = match ($event->status) {
                    'pending' => '#f59e0b', // amber-500
                    'confirmed' => '#3b82f6', // blue-500
                    'completed' => '#6b7280', // gray-500
                    default => '#3b82f6',
                };
                $borderColor = match ($event->status) {
                    'pending' => '#d97706', // amber-600
                    'confirmed' => '#2563eb', // blue-600
                    'completed' => '#4b5563', // gray-600
                    default => '#2563eb',
                };
            }

            return [
                'id' => (string) $event->id,
                'title' => $event->title,
                'start' => $event->start_date->format('Y-m-d'),
                'end' => $event->end_date->addDay()->format('Y-m-d'), // FullCalendar end is exclusive
                'allDay' => true,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $borderColor,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'status' => $event->status,
                    'space' => $event->eventSpace->name,
                    'space_id' => $event->eventSpace->id,
                    'client' => $event->client_name,
                    'isAssigned' => $isAssigned,
                ],
            ];
        })->toArray();
    }

    /**
     * Display specific assignment details
     */
    public function show(Request $request, int $eventId): Response
    {
        $user = $request->user();

        if (!$user->hasStaffProfile()) {
            abort(403, 'You do not have a staff profile.');
        }

        $staff = $user->staffProfile;

        // Get the event and verify staff is assigned
        $event = Event::with(['eventSpace', 'creator', 'staff.user'])
            ->findOrFail($eventId);

        // Check if staff is assigned to this event
        $assignment = $event->staff()->where('staff_id', $staff->id)->first();

        if (!$assignment) {
            abort(403, 'You are not assigned to this event.');
        }

        // Get assignment details
        $assignmentDetails = [
            'role' => $assignment->pivot->role,
            'notes' => $assignment->pivot->notes,
        ];

        return Inertia::render('staff/assignments/Show', [
            'staff' => [
                'id' => $staff->id,
                'position' => $staff->position,
                'user' => [
                    'id' => $staff->user->id,
                    'name' => $staff->user->name,
                    'email' => $staff->user->email,
                ],
            ],
            'assignment' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'client_name' => $event->client_name,
                'client_email' => $event->client_email,
                'client_phone' => $event->client_phone,
                'start_date' => $event->start_date->format('Y-m-d'),
                'end_date' => $event->end_date->format('Y-m-d'),
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'status' => $event->status,
                'notes' => $event->notes,
                'event_space' => [
                    'id' => $event->eventSpace->id,
                    'name' => $event->eventSpace->name,
                    'location' => $event->eventSpace->location,
                ],
                'staff' => $event->staff->map(fn($s) => [
                    'id' => $s->id,
                    'user' => [
                        'id' => $s->user->id,
                        'name' => $s->user->name,
                        'email' => $s->user->email,
                    ],
                    'position' => $s->position,
                    'pivot' => [
                        'role' => $s->pivot->role,
                        'notes' => $s->pivot->notes,
                    ],
                ]),
            ],
            'assignmentDetails' => $assignmentDetails,
        ]);
    }
}
