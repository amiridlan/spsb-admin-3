<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Staff\Contracts\LeaveServiceInterface;
use Modules\Staff\Models\LeaveRequest;

class LeaveRequestController extends Controller
{
    public function __construct(
        protected LeaveServiceInterface $leaveService
    ) {}

    /**
     * Show HR-approved leave requests for the head's department
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get head's department
        if (!$user->hasStaffProfile()) {
            abort(403, 'You must have a staff profile to access this page.');
        }

        $userStaff = $user->staffProfile;

        if (!$userStaff->department_id) {
            abort(403, 'You must be assigned to a department.');
        }

        $department = $userStaff->department;

        // Verify user is actually the head of this department
        if (!$department || $department->head_user_id !== $user->id) {
            abort(403, 'You are not the head of this department.');
        }

        $filters = $request->only(['staff_id', 'leave_type']);

        // Get hr_approved requests for this department's staff
        $query = LeaveRequest::hrApproved()
            ->whereHas('staff', function ($q) use ($department) {
                $q->where('department_id', $department->id);
            })
            ->with(['staff.user', 'staff.department', 'hrReviewer']);

        if (isset($filters['staff_id'])) {
            $query->where('staff_id', $filters['staff_id']);
        }

        if (isset($filters['leave_type'])) {
            $query->where('leave_type', $filters['leave_type']);
        }

        $leaveRequests = $query->orderBy('created_at', 'asc')->paginate(20);

        // Get pending count for this department
        $pendingCount = LeaveRequest::hrApproved()
            ->whereHas('staff', function ($q) use ($department) {
                $q->where('department_id', $department->id);
            })
            ->count();

        return Inertia::render('head/leave/Index', [
            'leaveRequests' => $leaveRequests,
            'pendingCount' => $pendingCount,
            'department' => $department,
            'filters' => $filters,
        ]);
    }

    /**
     * Show leave request details
     */
    public function show(Request $request, int $id): Response
    {
        $leaveRequest = $this->leaveService->getLeaveRequestById($id);

        if (!$leaveRequest) {
            abort(404);
        }

        // Authorization check
        $this->authorize('view', $leaveRequest);

        return Inertia::render('head/leave/Show', [
            'leaveRequest' => $leaveRequest,
        ]);
    }

    /**
     * Department Head approves a leave request (final approval)
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        // Authorization check
        $this->authorize('approveAsHead', $leaveRequest);

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $this->leaveService->approveAsHead($id, $request->user()->id, $validated['notes'] ?? null);

            return back()->with('success', 'Leave request approved successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Department Head rejects a leave request
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        // Authorization check
        $this->authorize('rejectAsHead', $leaveRequest);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10'],
        ]);

        try {
            $this->leaveService->rejectAsHead($id, $request->user()->id, $validated['reason']);

            return back()->with('success', 'Leave request rejected.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
