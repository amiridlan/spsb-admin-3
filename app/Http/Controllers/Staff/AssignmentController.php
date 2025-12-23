<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
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
            'filter' => $filter,
            'counts' => $counts,
        ]);
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

        // Get the event with assignment details
        $assignment = $staff->events()
            ->with(['eventSpace', 'creator', 'staff.user'])
            ->findOrFail($eventId);

        // Get the pivot data for this staff member
        $assignmentDetails = $assignment->staff()
            ->where('staff_id', $staff->id)
            ->first()
            ->pivot;

        return Inertia::render('staff/assignments/Show', [
            'assignment' => $assignment,
            'assignmentDetails' => $assignmentDetails,
            'staff' => $staff,
        ]);
    }

    /**
     * Display staff availability calendar
     */
    public function calendar(Request $request): Response
    {
        $user = $request->user();

        if (!$user->hasStaffProfile()) {
            abort(403, 'You do not have a staff profile.');
        }

        $staff = $user->staffProfile;

        // Get date range (default to current month)
        $startDate = $request->input('start', Carbon::now()->startOfMonth());
        $endDate = $request->input('end', Carbon::now()->endOfMonth());

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Get assignments in date range
        $assignments = $this->availabilityService->getAssignedEvents($staff, $startDate, $endDate);

        // Format for calendar
        $calendarEvents = $assignments->map(function ($event) use ($staff) {
            $pivot = $event->staff->where('id', $staff->id)->first()->pivot;

            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date->format('Y-m-d'),
                'end' => $event->end_date->addDay()->format('Y-m-d'), // FullCalendar uses exclusive end dates
                'backgroundColor' => $this->getStatusColor($event->status),
                'borderColor' => $this->getStatusColor($event->status),
                'extendedProps' => [
                    'status' => $event->status,
                    'space' => $event->eventSpace->name,
                    'role' => $pivot->role,
                    'client' => $event->client_name,
                ],
            ];
        });

        return Inertia::render('staff/assignments/Calendar', [
            'staff' => $staff->load('user'),
            'events' => $calendarEvents,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }

    /**
     * Get color for event status
     */
    private function getStatusColor(string $status): string
    {
        return match ($status) {
            'pending' => '#f59e0b',
            'confirmed' => '#10b981',
            'completed' => '#6b7280',
            'cancelled' => '#ef4444',
            default => '#3b82f6',
        };
    }
}
