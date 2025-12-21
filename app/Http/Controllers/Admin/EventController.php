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

    public function create(): Response
    {
        return Inertia::render('admin/events/Create', [
            'spaces' => EventSpace::where('is_active', true)->get(),
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

        Event::create($validated);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created successfully.');
    }

    public function show(Event $event): Response
    {
        $event->load(['eventSpace', 'creator']);

        return Inertia::render('admin/events/Show', [
            'event' => $event,
        ]);
    }

    public function edit(Event $event): Response
    {
        return Inertia::render('admin/events/Edit', [
            'event' => $event->load('eventSpace'),
            'spaces' => EventSpace::where('is_active', true)->get(),
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
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

        $event->update($validated);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted successfully.');
    }
}
