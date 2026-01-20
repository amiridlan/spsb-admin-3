<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventSpace;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;
use Knuckles\Scribe\Attributes\Authenticated;

#[Group('Events & Bookings', 'Manage events and bookings')]
class EventController extends Controller
{
    use ApiResponse;

    /**
     * List Events
     *
     * Get a list of events with optional filters. By default, cancelled events are excluded.
     *
     * @unauthenticated
     */
    #[QueryParam('space_id', 'integer', 'Filter by event space ID', required: false, example: 1)]
    #[QueryParam('start_date', 'date', 'Filter events starting from this date (Y-m-d)', required: false, example: '2024-01-01')]
    #[QueryParam('end_date', 'date', 'Filter events ending before this date (Y-m-d)', required: false, example: '2024-12-31')]
    #[Response(['success' => true, 'data' => [['id' => 1, 'title' => 'Corporate Meeting', 'client_name' => 'Acme Corp', 'start_date' => '2024-06-15', 'end_date' => '2024-06-16', 'status' => 'confirmed', 'event_space' => ['id' => 1, 'name' => 'Grand Ballroom']]]], 200, 'List of events')]
    public function index(Request $request): JsonResponse
    {
        $query = Event::query()
            ->with('eventSpace')
            ->where('status', '!=', 'cancelled');

        if ($request->space_id) {
            $query->where('event_space_id', $request->space_id);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        }

        $events = $query->orderBy('start_date')->get();

        return $this->success(EventResource::collection($events));
    }

    /**
     * Get Event Details
     *
     * Get detailed information about a specific event.
     *
     * @unauthenticated
     */
    #[UrlParam('event', 'integer', 'The ID of the event', example: 1)]
    #[Response(['success' => true, 'data' => ['id' => 1, 'title' => 'Corporate Meeting', 'description' => 'Annual company meeting', 'client_name' => 'Acme Corp', 'client_email' => 'contact@acme.com', 'start_date' => '2024-06-15', 'end_date' => '2024-06-16', 'status' => 'confirmed', 'event_space' => ['id' => 1, 'name' => 'Grand Ballroom']]], 200, 'Event details')]
    #[Response(['success' => false, 'message' => 'Event not available'], 404, 'Event cancelled or not found')]
    public function show(Event $event): JsonResponse
    {
        if ($event->isCancelled()) {
            return $this->error('Event not available', 404);
        }

        return $this->success(new EventResource($event->load('eventSpace')));
    }

    /**
     * Check Availability
     *
     * Check if a specific event space is available for the given date range.
     *
     * @unauthenticated
     */
    #[BodyParam('event_space_id', 'integer', 'The ID of the event space to check', required: true, example: 1)]
    #[BodyParam('start_date', 'date', 'Start date (Y-m-d)', required: true, example: '2024-06-15')]
    #[BodyParam('end_date', 'date', 'End date (Y-m-d)', required: true, example: '2024-06-16')]
    #[Response(['success' => true, 'data' => ['available' => true, 'message' => 'Space is available']], 200, 'Space is available')]
    #[Response(['success' => true, 'data' => ['available' => false, 'message' => 'Space is not available for the selected dates']], 200, 'Space has conflicts')]
    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'event_space_id' => ['required', 'exists:event_spaces,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $conflicts = Event::where('event_space_id', $request->event_space_id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            })
            ->exists();

        return $this->success([
            'available' => !$conflicts,
            'message' => $conflicts
                ? 'Space is not available for the selected dates'
                : 'Space is available',
        ]);
    }

    /**
     * Create Booking
     *
     * Create a new event booking request. The booking will start with 'pending' status and requires admin approval.
     */
    #[Authenticated]
    #[BodyParam('event_space_id', 'integer', 'The ID of the event space to book', required: true, example: 1)]
    #[BodyParam('title', 'string', 'Event title', required: true, example: 'Corporate Annual Meeting')]
    #[BodyParam('description', 'string', 'Event description', required: false, example: 'Annual company-wide meeting and celebration')]
    #[BodyParam('client_name', 'string', 'Client/organizer name', required: true, example: 'John Smith')]
    #[BodyParam('client_email', 'email', 'Client email address', required: true, example: 'john@example.com')]
    #[BodyParam('client_phone', 'string', 'Client phone number', required: false, example: '+1234567890')]
    #[BodyParam('start_date', 'date', 'Event start date (Y-m-d)', required: true, example: '2024-06-15')]
    #[BodyParam('end_date', 'date', 'Event end date (Y-m-d)', required: true, example: '2024-06-16')]
    #[BodyParam('start_time', 'string', 'Event start time (HH:mm)', required: false, example: '09:00')]
    #[BodyParam('end_time', 'string', 'Event end time (HH:mm)', required: false, example: '17:00')]
    #[Response(['success' => true, 'message' => 'Booking request created successfully', 'data' => ['id' => 1, 'title' => 'Corporate Meeting', 'status' => 'pending', 'start_date' => '2024-06-15', 'end_date' => '2024-06-16']], 201, 'Booking created')]
    #[Response(['success' => false, 'message' => 'Space is not available for the selected dates'], 422, 'Date conflict')]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_space_id' => ['required', 'exists:event_spaces,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['required', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:50'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
        ]);

        // Check availability
        $conflicts = Event::where('event_space_id', $validated['event_space_id'])
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_date', '<=', $validated['start_date'])
                            ->where('end_date', '>=', $validated['end_date']);
                    });
            })
            ->exists();

        if ($conflicts) {
            return $this->error('Space is not available for the selected dates', 422);
        }

        $validated['status'] = 'pending';
        $validated['created_by'] = auth()->id() ?? 1; // Fallback for API users

        $event = Event::create($validated);

        return $this->success(
            new EventResource($event->load('eventSpace')),
            'Booking request created successfully',
            201
        );
    }
}
