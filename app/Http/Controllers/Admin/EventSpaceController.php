<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventSpace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventSpaceController extends Controller
{
    public function index(): Response
    {
        $spaces = EventSpace::query()
            ->withCount('events')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

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
            'description' => ['nullable', 'string'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        EventSpace::create($validated);

        return redirect()->route('admin.event-spaces.index')
            ->with('success', 'Event space created successfully.');
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
            'description' => ['nullable', 'string'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        $eventSpace->update($validated);

        return redirect()->route('admin.event-spaces.index')
            ->with('success', 'Event space updated successfully.');
    }

    public function destroy(EventSpace $eventSpace): RedirectResponse
    {
        if ($eventSpace->events()->count() > 0) {
            return back()->withErrors([
                'error' => 'Cannot delete event space with existing events.'
            ]);
        }

        $eventSpace->delete();

        return redirect()->route('admin.event-spaces.index')
            ->with('success', 'Event space deleted successfully.');
    }
}
