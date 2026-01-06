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

        // Get all active event spaces for filter using service
        $spaces = $this->eventSpaceService->getActive();

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

}
