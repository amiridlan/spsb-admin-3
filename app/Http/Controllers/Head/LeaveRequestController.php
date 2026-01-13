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

        // Get the department where this user is the head
        $department = $user->headOfDepartment;

        if (!$department) {
            abort(403, 'You must be assigned as head of a department. Please contact an administrator to assign you to a department.');
        }

        $filters = $request->only(['staff_id', 'leave_type']);

        // Get pending head approval requests for this department's staff
        $query = LeaveRequest::pendingHeadApproval()
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
        $pendingCount = LeaveRequest::pendingHeadApproval()
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
        $leaveRequest = LeaveRequest::with(['staff.department'])->findOrFail($id);

        // Authorization check
        $this->authorize('approveAsHead', $leaveRequest);

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        try {
            \Log::info('Head approving leave request', ['id' => $id, 'user_id' => $request->user()->id]);

            $leaveRequest = $this->leaveService->approveAsHead($id, $request->user()->id, $validated['notes'] ?? null);

            $leaveRequest->refresh();
            $message = $leaveRequest->status === 'approved'
                ? 'Leave request fully approved.'
                : 'Leave request approved by department head. Pending HR approval.';

            \Log::info('Head approval successful', ['status' => $leaveRequest->status, 'message' => $message]);

            return redirect()->route('head.leave.requests.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Head approval failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Department Head rejects a leave request
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $leaveRequest = LeaveRequest::with(['staff.department'])->findOrFail($id);

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
