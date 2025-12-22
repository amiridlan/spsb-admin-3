<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventSpace;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventSpaceController extends Controller
{
    public function index()
    {
        $spaces = EventSpace::withCount('events')
            ->latest()
            ->paginate(10);

        return Inertia::render('admin/event-spaces/Index', [
            'spaces' => $spaces,
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/event-spaces/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;

        EventSpace::create($validated);

        return redirect()->route('admin.event-spaces.index')
            ->with('success', 'Event space created successfully.');
    }

    public function edit(EventSpace $eventSpace)
    {
        return Inertia::render('admin/event-spaces/Edit', [
            'space' => $eventSpace,
        ]);
    }

    public function update(Request $request, EventSpace $eventSpace)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;

        $eventSpace->update($validated);

        return redirect()->route('admin.event-spaces.index')
            ->with('success', 'Event space updated successfully.');
    }

    public function destroy(EventSpace $eventSpace)
    {
        $eventSpace->delete();

        return redirect()->route('admin.event-spaces.index')
            ->with('success', 'Event space deleted successfully.');
    }
}
