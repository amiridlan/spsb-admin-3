<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSpace;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Event::query()
            ->with(['eventSpace', 'creator', 'staff.user']) // Eager load staff
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
            'staff' => Staff::where('is_available', true)->with('user')->get(),
            'prefill' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // First, clean up the input data
        $input = $request->all();

        // Handle time fields - convert empty strings to null
        if (isset($input['start_time']) && trim($input['start_time']) === '') {
            $input['start_time'] = null;
        }
        if (isset($input['end_time']) && trim($input['end_time']) === '') {
            $input['end_time'] = null;
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
            'staff_ids' => ['nullable', 'array'],
            'staff_ids.*' => ['exists:staff,id'],
        ])->validate();

        $validated['created_by'] = auth()->id();

        // Remove staff_ids from validated data before creating event
        $staffIds = $validated['staff_ids'] ?? [];
        unset($validated['staff_ids']);

        $event = Event::create($validated);

        // Attach staff if any
        if (!empty($staffIds)) {
            $event->staff()->attach($staffIds);
        }

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Event created successfully.');
    }

    public function show(Event $event): Response
    {
        $event->load([
            'eventSpace',
            'creator',
            'staff.user'
        ]);

        return Inertia::render('admin/events/Show', [
            'event' => $event,
        ]);
    }

    public function edit(Event $event): Response
    {
        // Load relationships
        $event->load(['eventSpace', 'staff.user']);

        // Format dates to ensure they're strings in YYYY-MM-DD format
        // Format times to ensure they're in HH:mm format (strip seconds if present)
        $eventData = [
            'id' => $event->id,
            'event_space_id' => $event->event_space_id,
            'title' => $event->title,
            'description' => $event->description,
            'client_name' => $event->client_name,
            'client_email' => $event->client_email,
            'client_phone' => $event->client_phone,
            'start_date' => $event->start_date->format('Y-m-d'),
            'end_date' => $event->end_date->format('Y-m-d'),
            'start_time' => $event->start_time ? substr($event->start_time, 0, 5) : null, // Strip seconds
            'end_time' => $event->end_time ? substr($event->end_time, 0, 5) : null, // Strip seconds
            'status' => $event->status,
            'notes' => $event->notes,
            'event_space' => $event->eventSpace,
            'staff' => $event->staff,
        ];

        return Inertia::render('admin/events/Edit', [
            'event' => $eventData,
            'spaces' => EventSpace::where('is_active', true)->get(),
            'staff' => Staff::where('is_available', true)->with('user')->get(),
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        // DEBUG: Log incoming request
        \Log::info('Event Update Request', [
            'event_id' => $event->id,
            'staff_ids_raw' => $request->input('staff_ids'),
            'has_staff_ids' => $request->has('staff_ids'),
            'all_input' => $request->all(),
        ]);

        // First, clean up the input data
        $input = $request->all();

        // Handle time fields - convert empty strings to null, and ensure null if not set
        if (isset($input['start_time'])) {
            $input['start_time'] = trim($input['start_time']);
            if ($input['start_time'] === '' || $input['start_time'] === 'null') {
                $input['start_time'] = null;
            }
            // Strip seconds if present (HH:mm:ss -> HH:mm)
            elseif (strlen($input['start_time']) === 8) {
                $input['start_time'] = substr($input['start_time'], 0, 5);
            }
        } else {
            $input['start_time'] = null;
        }

        if (isset($input['end_time'])) {
            $input['end_time'] = trim($input['end_time']);
            if ($input['end_time'] === '' || $input['end_time'] === 'null') {
                $input['end_time'] = null;
            }
            // Strip seconds if present (HH:mm:ss -> HH:mm)
            elseif (strlen($input['end_time']) === 8) {
                $input['end_time'] = substr($input['end_time'], 0, 5);
            }
        } else {
            $input['end_time'] = null;
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
            'staff_ids' => ['nullable', 'array'],
            'staff_ids.*' => ['exists:staff,id'],
        ])->validate();

        // Remove staff_ids from validated data before updating
        $staffIds = $validated['staff_ids'] ?? [];
        unset($validated['staff_ids']);

        $event->update($validated);

        // Sync staff assignments
        \Log::info('Syncing staff', [
            'event_id' => $event->id,
            'staff_ids' => $staffIds,
            'staff_ids_count' => count($staffIds),
        ]);

        $event->staff()->sync($staffIds);

        \Log::info('Staff synced', [
            'event_id' => $event->id,
            'attached_count' => $event->staff()->count(),
        ]);

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
