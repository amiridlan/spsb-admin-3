<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSpace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Event::query()
            ->with(['eventSpace', 'creator'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->space, fn($q) => $q->where('event_space_id', $request->space))
            ->orderBy('start_date', 'desc');

        $events = $query->paginate(20);

        return Inertia::render('admin/events/Index', [
            'events' => $events,
            'filters' => $request->only(['status', 'space']),
            'spaces' => EventSpace::where('is_active', true)->get(),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('admin/events/Create', [
            'spaces' => EventSpace::where('is_active', true)->get(),
            'prefill' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_space_id' => ['required', 'exists:event_spaces,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['required', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'in:pending,confirmed,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = auth()->id();

        $event = Event::create($validated);

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Event created successfully.');
    }

    public function show(Event $event): Response
    {
        $event->load([
            'eventSpace',
            'creator',
<<<<<<< HEAD
            'staff.user'
=======
            'staff.user' // Load staff with user relationship
>>>>>>> parent of bcd2403 (push for reference cc)
        ]);

        return Inertia::render('admin/events/Show', [
            'event' => $event,
        ]);
    }

    public function edit(Event $event): Response
    {
<<<<<<< HEAD
        // Load relationships including staff
        $event->load(['eventSpace', 'staff.user']);

        $eventData = $event->toArray();
=======
        // Format the event data to ensure proper date/time format
        $eventData = $event->load('eventSpace')->toArray();
>>>>>>> parent of bcd2403 (push for reference cc)

        // Ensure dates are in YYYY-MM-DD format
        $eventData['start_date'] = $event->start_date->format('Y-m-d');
        $eventData['end_date'] = $event->end_date->format('Y-m-d');

        // Ensure times are in HH:mm format or null
        $eventData['start_time'] = $event->start_time ?
            (strlen($event->start_time) === 8 ? substr($event->start_time, 0, 5) : $event->start_time) :
            null;
        $eventData['end_time'] = $event->end_time ?
            (strlen($event->end_time) === 8 ? substr($event->end_time, 0, 5) : $event->end_time) :
            null;

<<<<<<< HEAD
        // Get staff availability
        $staffAvailability = app(\App\Services\StaffAvailabilityService::class)
            ->getStaffAvailabilityForEvent($event);

        return Inertia::render('admin/events/Edit', [
            'event' => $eventData,
            'spaces' => EventSpace::where('is_active', true)->get(),
            'assignedStaff' => $event->staff,
            'availableStaff' => $staffAvailability,
=======
        return Inertia::render('admin/events/Edit', [
            'event' => $eventData,
            'spaces' => EventSpace::where('is_active', true)->get(),
>>>>>>> parent of bcd2403 (push for reference cc)
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
<<<<<<< HEAD
        $input = $request->all();

=======
        // First, clean up the input data
        $input = $request->all();

        // Handle time fields - convert empty strings to null, trim seconds if present
>>>>>>> parent of bcd2403 (push for reference cc)
        if (isset($input['start_time'])) {
            $input['start_time'] = trim($input['start_time']);
            if ($input['start_time'] === '' || $input['start_time'] === null) {
                $input['start_time'] = null;
            } elseif (strlen($input['start_time']) === 8) {
<<<<<<< HEAD
=======
                // If format is HH:MM:SS, trim to HH:MM
>>>>>>> parent of bcd2403 (push for reference cc)
                $input['start_time'] = substr($input['start_time'], 0, 5);
            }
        }

        if (isset($input['end_time'])) {
            $input['end_time'] = trim($input['end_time']);
            if ($input['end_time'] === '' || $input['end_time'] === null) {
                $input['end_time'] = null;
            } elseif (strlen($input['end_time']) === 8) {
<<<<<<< HEAD
=======
                // If format is HH:MM:SS, trim to HH:MM
>>>>>>> parent of bcd2403 (push for reference cc)
                $input['end_time'] = substr($input['end_time'], 0, 5);
            }
        }

        $validated = validator($input, [
            'event_space_id' => ['required', 'exists:event_spaces,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['required', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'in:pending,confirmed,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ])->validate();

        $event->update($validated);

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted successfully.');
    }
}
