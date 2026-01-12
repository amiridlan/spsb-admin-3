<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function index(): Response
    {
        $departments = Department::query()
            ->with(['head:id,name,email'])
            ->withCount('staff')
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('admin/departments/Index', [
            'departments' => $departments->items(),
            'pagination' => [
                'current_page' => $departments->currentPage(),
                'last_page' => $departments->lastPage(),
                'per_page' => $departments->perPage(),
                'total' => $departments->total(),
            ],
        ]);
    }

    public function create(): Response
    {
        // Get users who can be department heads (superadmin, admin, head_of_department)
        $potentialHeads = User::query()
            ->whereIn('role', ['superadmin', 'admin', 'head_of_department', 'staff'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return Inertia::render('admin/departments/Create', [
            'potentialHeads' => $potentialHeads,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
            'code' => ['nullable', 'string', 'max:10', 'unique:departments,code'],
            'description' => ['nullable', 'string', 'max:1000'],
            'head_user_id' => ['nullable', 'exists:users,id'],
        ]);

        Department::create($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department): Response
    {
        $department->load(['head:id,name,email,role']);

        // Get users who can be department heads
        $potentialHeads = User::query()
            ->whereIn('role', ['superadmin', 'admin', 'head_of_department', 'staff'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return Inertia::render('admin/departments/Edit', [
            'department' => $department,
            'potentialHeads' => $potentialHeads,
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($department->id)],
            'code' => ['nullable', 'string', 'max:10', Rule::unique('departments')->ignore($department->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'head_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        // Check if department has staff assigned
        if ($department->staff()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete department with assigned staff. Please reassign staff first.']);
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
