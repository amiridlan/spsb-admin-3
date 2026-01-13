<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Staff;
use App\Models\User;
use App\Services\StaffAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function __construct(
        protected StaffAvailabilityService $availabilityService
    ) {}

    public function index(): Response
    {
        $staff = Staff::with(['user', 'department'])
            ->withCount('events')
            ->latest()
            ->paginate(20);

        return Inertia::render('admin/staff/Index', [
            'staff' => $staff,
        ]);
    }

    public function create(): Response
    {
        // Get users who don't have staff profiles yet
        $availableUsers = User::whereDoesntHave('staffProfile')
            ->where('role', 'staff')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        // Get departments
        $departments = Department::orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('admin/staff/Create', [
            'availableUsers' => $availableUsers,
            'departments' => $departments,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id', 'unique:staff,user_id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position' => ['nullable', 'string', 'max:255'],
            'specializations' => ['nullable', 'array'],
            'specializations.*' => ['string', 'max:100'],
            'is_available' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['is_available'] = $validated['is_available'] ?? true;

        Staff::create($validated);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member added successfully.');
    }

    public function show(Staff $staff): Response
    {
        $staff->load(['user', 'department', 'events.eventSpace']);

        return Inertia::render('admin/staff/Show', [
            'staff' => $staff,
            'upcomingAssignments' => $staff->upcomingAssignments()->get(),
            'pastAssignments' => $staff->pastAssignments()->paginate(10),
        ]);
    }

    public function edit(Staff $staff): Response
    {
        $staff->load(['user', 'department']);

        // Get departments
        $departments = Department::orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('admin/staff/Edit', [
            'staff' => $staff,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => ['nullable', 'exists:departments,id'],
            'position' => ['nullable', 'string', 'max:255'],
            'specializations' => ['nullable', 'array'],
            'specializations.*' => ['string', 'max:100'],
            'is_available' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $staff->update($validated);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member updated successfully.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $staff->delete();

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member removed successfully.');
    }

    public function adjustLeave(Request $request, Staff $staff): RedirectResponse
    {
        $validated = $request->validate([
            'annual_leave_total' => ['required', 'integer', 'min:0'],
            'annual_leave_used' => ['required', 'integer', 'min:0'],
            'sick_leave_total' => ['required', 'integer', 'min:0'],
            'sick_leave_used' => ['required', 'integer', 'min:0'],
            'emergency_leave_total' => ['required', 'integer', 'min:0'],
            'emergency_leave_used' => ['required', 'integer', 'min:0'],
            'leave_notes' => ['nullable', 'string'],
        ]);

        // Validate that used doesn't exceed total for each leave type
        if ($validated['annual_leave_used'] > $validated['annual_leave_total']) {
            return back()->withErrors(['annual_leave_used' => 'Annual leave used cannot exceed total.']);
        }

        if ($validated['sick_leave_used'] > $validated['sick_leave_total']) {
            return back()->withErrors(['sick_leave_used' => 'Sick leave used cannot exceed total.']);
        }

        if ($validated['emergency_leave_used'] > $validated['emergency_leave_total']) {
            return back()->withErrors(['emergency_leave_used' => 'Emergency leave used cannot exceed total.']);
        }

        $staff->update($validated);

        return back()->with('success', 'Leave balance updated successfully.');
    }

    public function updateLeaveNotes(Request $request, Staff $staff): RedirectResponse
    {
        $validated = $request->validate([
            'leave_notes' => ['nullable', 'string'],
        ]);

        $staff->update($validated);

        return back()->with('success', 'Leave notes updated successfully.');
    }
}
