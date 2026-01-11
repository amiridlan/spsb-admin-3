<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventSpace;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Events\Contracts\EventServiceInterface;
use Modules\Events\Contracts\EventSpaceServiceInterface;
use Modules\Staff\Models\LeaveRequest;

class CalendarController extends Controller
{
    public function __construct(
        protected EventServiceInterface $eventService,
        protected EventSpaceServiceInterface $eventSpaceService
    ) {}

    /**
     * Display the calendar view with advanced filtering
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Build filters array
        $filters = [];

        // Staff users only see their assigned events
        if ($user->isStaff() && !$user->canManageUsers()) {
            if (!$user->hasStaffProfile()) {
                abort(403, 'You do not have a staff profile.');
            }

            $filters['staff_id'] = $user->staffProfile->id;
        }

        // Apply space filter
        if ($request->space) {
            $filters['event_space_id'] = $request->space;
        }

        // Apply status filter
        if ($request->status) {
            $filters['status'] = $request->status;
        } elseif (!$request->show_cancelled) {
            // By default, exclude cancelled events unless explicitly requested
            $filters['exclude_cancelled'] = true;
        }

        // Get calendar events using service
        $calendarEvents = $this->eventService->getCalendarEvents($filters);

        // Get approved leave requests for calendar
        $leaveEvents = $this->getLeaveCalendarEvents($user);

        // Merge event and leave data
        $allCalendarEvents = array_merge($calendarEvents, $leaveEvents);

        // Get all active event spaces for filter using service
        $spaces = $this->eventSpaceService->getActive();

        return Inertia::render('Calendar', [
            'events' => $allCalendarEvents,
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
     * Get leave requests formatted as calendar events
     */
    protected function getLeaveCalendarEvents($user): array
    {
        $query = LeaveRequest::with(['staff.user'])
            ->where('status', 'approved');

        // If staff user, only show their own leave
        if ($user->isStaff() && !$user->canManageUsers()) {
            if ($user->hasStaffProfile()) {
                $query->where('staff_id', $user->staffProfile->id);
            } else {
                return [];
            }
        }

        $leaveRequests = $query->get();

        return $leaveRequests->map(function ($leave) {
            // Color coding by leave type
            $colors = [
                'annual' => ['bg' => '#3b82f6', 'border' => '#2563eb', 'text' => '#ffffff'], // Blue
                'sick' => ['bg' => '#ef4444', 'border' => '#dc2626', 'text' => '#ffffff'], // Red
                'emergency' => ['bg' => '#f97316', 'border' => '#ea580c', 'text' => '#ffffff'], // Orange
            ];

            $color = $colors[$leave->leave_type] ?? $colors['annual'];

            return [
                'id' => 'leave-' . $leave->id,
                'title' => $leave->staff->user->name . ' - ' . ucfirst($leave->leave_type) . ' Leave',
                'start' => $leave->start_date->toDateString(),
                'end' => $leave->end_date->addDay()->toDateString(), // FullCalendar end is exclusive
                'backgroundColor' => $color['bg'],
                'borderColor' => $color['border'],
                'textColor' => $color['text'],
                'allDay' => true,
                'extendedProps' => [
                    'type' => 'leave',
                    'leave_type' => $leave->leave_type,
                    'staff_name' => $leave->staff->user->name,
                    'total_days' => $leave->total_days,
                    'isLeave' => true,
                ],
            ];
        })->toArray();
    }

}
