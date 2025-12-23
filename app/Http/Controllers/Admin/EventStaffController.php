<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Staff;
use App\Services\StaffAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventStaffController extends Controller
{
    public function __construct(
        protected StaffAvailabilityService $availabilityService
    ) {}

    /**
     * Show staff assignment page for an event
     */
    public function index(Event $event): Response
    {
        $event->load(['eventSpace', 'staff.user']);

        $staffAvailability = $this->availabilityService->getStaffAvailabilityForEvent($event);

        return Inertia::render('admin/events/StaffAssignment', [
            'event' => $event,
            'assignedStaff' => $event->staff,
            'availableStaff' => $staffAvailability,
        ]);
    }

    /**
     * Assign staff to event
     */
    public function store(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'staff_id' => ['required', 'exists:staff,id'],
            'role' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $staff = Staff::findOrFail($validated['staff_id']);

        // Check if already assigned
        if ($event->hasStaff($staff->id)) {
            return back()->withErrors(['staff_id' => 'This staff member is already assigned to this event.']);
        }

        // Check availability
        if (!$this->availabilityService->isAvailable($staff, $event->start_date, $event->end_date)) {
            return back()->withErrors(['staff_id' => 'This staff member is not available for the selected dates.']);
        }

        $event->assignStaff($staff->id, $validated['role'] ?? null, $validated['notes'] ?? null);

        return back()->with('success', 'Staff member assigned successfully.');
    }

    /**
     * Update staff assignment
     */
    public function update(Request $request, Event $event, Staff $staff): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $event->staff()->updateExistingPivot($staff->id, $validated);

        return back()->with('success', 'Assignment updated successfully.');
    }

    /**
     * Remove staff from event
     */
    public function destroy(Event $event, Staff $staff): RedirectResponse
    {
        $event->removeStaff($staff->id);

        return back()->with('success', 'Staff member removed from event.');
    }
}
