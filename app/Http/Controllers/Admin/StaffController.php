<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $staff = Staff::with('user')
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

        return Inertia::render('admin/staff/Create', [
            'availableUsers' => $availableUsers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id', 'unique:staff,user_id'],
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
        $staff->load(['user', 'events.eventSpace']);

        return Inertia::render('admin/staff/Show', [
            'staff' => $staff,
            'upcomingAssignments' => $staff->upcomingAssignments()->get(),
            'pastAssignments' => $staff->pastAssignments()->paginate(10),
        ]);
    }

    public function edit(Staff $staff): Response
    {
        $staff->load('user');

        return Inertia::render('admin/staff/Edit', [
            'staff' => $staff,
        ]);
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $validated = $request->validate([
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
}
