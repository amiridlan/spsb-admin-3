<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventSpace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EventSpaceController extends Controller
{
    public function index(): Response
    {
        $spaces = EventSpace::withCount('events')
            ->orderBy('name')
            ->paginate(12);

        return Inertia::render('admin/event-spaces/Index', [
            'spaces' => $spaces,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/event-spaces/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('event-spaces', 'public');
        }

        EventSpace::create($validated);

        return redirect()->route('admin.event-spaces.index')
            ->with('success', 'Event space created successfully.');
    }

    public function show(EventSpace $eventSpace): Response
    {
        $eventSpace->loadCount('events');

        // Load recent events (last 10)
        $eventSpace->load(['events' => function ($query) {
            $query->orderBy('start_date', 'desc')
                ->limit(10)
                ->select('id', 'event_space_id', 'title', 'client_name', 'start_date', 'end_date', 'status');
        }]);

        return Inertia::render('admin/event-spaces/Show', [
            'space' => $eventSpace,
        ]);
    }

    public function edit(EventSpace $eventSpace): Response
    {
        return Inertia::render('admin/event-spaces/Edit', [
            'space' => $eventSpace,
        ]);
    }

    public function update(Request $request, EventSpace $eventSpace): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            '_remove_image' => ['nullable', 'boolean'],
        ]);

        // Handle image removal
        if ($request->input('_remove_image') && $eventSpace->image) {
            Storage::disk('public')->delete($eventSpace->image);
            $validated['image'] = null;
        }

        // Handle new image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($eventSpace->image) {
                Storage::disk('public')->delete($eventSpace->image);
            }
            $validated['image'] = $request->file('image')->store('event-spaces', 'public');
        }

        // Remove the helper field from validated data
        unset($validated['_remove_image']);

        $eventSpace->update($validated);

        return redirect()->route('admin.event-spaces.show', $eventSpace)
            ->with('success', 'Event space updated successfully.');
    }

    public function destroy(EventSpace $eventSpace): RedirectResponse
    {
        // Delete image if exists
        if ($eventSpace->image) {
            Storage::disk('public')->delete($eventSpace->image);
        }

        $eventSpace->delete();

        return redirect()->route('admin.event-spaces.index')
            ->with('success', 'Event space deleted successfully.');
    }
}
