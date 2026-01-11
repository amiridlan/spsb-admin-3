<?php

namespace App\Http\Controllers\Admin;

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

    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'staff_id', 'leave_type']);

        $query = LeaveRequest::with(['staff.user', 'staff.department', 'hrReviewer', 'headReviewer']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['staff_id'])) {
            $query->where('staff_id', $filters['staff_id']);
        }

        if (isset($filters['leave_type'])) {
            $query->where('leave_type', $filters['leave_type']);
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get pending count and hr_approved count
        $pendingCount = LeaveRequest::pending()->count();
        $hrApprovedCount = LeaveRequest::hrApproved()->count();

        return Inertia::render('admin/leave/Index', [
            'leaveRequests' => $leaveRequests,
            'pendingCount' => $pendingCount,
            'hrApprovedCount' => $hrApprovedCount,
            'filters' => $filters,
        ]);
    }

    public function show(int $id): Response
    {
        $leaveRequest = $this->leaveService->getLeaveRequestById($id);

        if (!$leaveRequest) {
            abort(404);
        }

        return Inertia::render('admin/leave/Show', [
            'leaveRequest' => $leaveRequest,
        ]);
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $this->leaveService->approveLeaveRequest($id, $request->user()->id, $validated['notes'] ?? null);

            return back()->with('success', 'Leave request approved successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10'],
        ]);

        try {
            $this->leaveService->rejectLeaveRequest($id, $request->user()->id, $validated['reason']);

            return back()->with('success', 'Leave request rejected.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show pending leave requests for HR review
     */
    public function hrIndex(Request $request): Response
    {
        $filters = $request->only(['staff_id', 'leave_type']);

        $query = LeaveRequest::pending()
            ->with(['staff.user', 'staff.department']);

        if (isset($filters['staff_id'])) {
            $query->where('staff_id', $filters['staff_id']);
        }

        if (isset($filters['leave_type'])) {
            $query->where('leave_type', $filters['leave_type']);
        }

        $leaveRequests = $query->orderBy('created_at', 'asc')->paginate(20);

        return Inertia::render('admin/leave/hr/Index', [
            'leaveRequests' => $leaveRequests,
            'filters' => $filters,
        ]);
    }

    /**
     * HR approves a leave request (first step)
     */
    public function hrApprove(Request $request, int $id): RedirectResponse
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        // Authorization check
        $this->authorize('approveAsHR', $leaveRequest);

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $this->leaveService->approveAsHR($id, $request->user()->id, $validated['notes'] ?? null);

            return back()->with('success', 'Leave request approved. Pending department head approval.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * HR rejects a leave request
     */
    public function hrReject(Request $request, int $id): RedirectResponse
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        // Authorization check
        $this->authorize('rejectAsHR', $leaveRequest);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10'],
        ]);

        try {
            $this->leaveService->rejectAsHR($id, $request->user()->id, $validated['reason']);

            return back()->with('success', 'Leave request rejected.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
