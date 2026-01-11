<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Staff\Contracts\LeaveServiceInterface;

class LeaveRequestController extends Controller
{
    public function __construct(
        protected LeaveServiceInterface $leaveService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        if (!$user->hasStaffProfile()) {
            abort(403, 'You do not have a staff profile.');
        }

        $staff = $user->staffProfile;

        $filters = $request->only(['status', 'year', 'leave_type']);

        $leaveRequests = $this->leaveService->getStaffLeaveRequests($staff->id, $filters);

        // Get leave balances
        $leaveBalances = [
            'annual' => [
                'total' => $staff->annual_leave_total,
                'used' => $staff->annual_leave_used,
                'remaining' => $staff->annual_leave_remaining,
            ],
            'sick' => [
                'total' => $staff->sick_leave_total,
                'used' => $staff->sick_leave_used,
                'remaining' => $staff->sick_leave_remaining,
            ],
            'emergency' => [
                'total' => $staff->emergency_leave_total,
                'used' => $staff->emergency_leave_used,
                'remaining' => $staff->emergency_leave_remaining,
            ],
        ];

        return Inertia::render('staff/leave/Index', [
            'leaveRequests' => $leaveRequests,
            'leaveBalances' => $leaveBalances,
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();

        if (!$user->hasStaffProfile()) {
            abort(403, 'You do not have a staff profile.');
        }

        $staff = $user->staffProfile;

        // Get leave balances
        $leaveBalances = [
            'annual' => [
                'total' => $staff->annual_leave_total,
                'used' => $staff->annual_leave_used,
                'remaining' => $staff->annual_leave_remaining,
            ],
            'sick' => [
                'total' => $staff->sick_leave_total,
                'used' => $staff->sick_leave_used,
                'remaining' => $staff->sick_leave_remaining,
            ],
            'emergency' => [
                'total' => $staff->emergency_leave_total,
                'used' => $staff->emergency_leave_used,
                'remaining' => $staff->emergency_leave_remaining,
            ],
        ];

        return Inertia::render('staff/leave/Create', [
            'leaveBalances' => $leaveBalances,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user->hasStaffProfile()) {
            abort(403, 'You do not have a staff profile.');
        }

        $staff = $user->staffProfile;

        $validated = $request->validate([
            'leave_type' => ['required', 'in:annual,sick,emergency'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:10'],
        ]);

        try {
            $this->leaveService->submitLeaveRequest($staff->id, $validated);

            return redirect()->route('staff.leave.requests.index')
                ->with('success', 'Leave request submitted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show(Request $request, int $id): Response
    {
        $user = $request->user();

        if (!$user->hasStaffProfile()) {
            abort(403, 'You do not have a staff profile.');
        }

        $staff = $user->staffProfile;
        $leaveRequest = $this->leaveService->getLeaveRequestById($id);

        if (!$leaveRequest || $leaveRequest->staff_id !== $staff->id) {
            abort(404);
        }

        return Inertia::render('staff/leave/Show', [
            'leaveRequest' => $leaveRequest,
        ]);
    }

    public function cancel(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();

        if (!$user->hasStaffProfile()) {
            abort(403, 'You do not have a staff profile.');
        }

        $staff = $user->staffProfile;
        $leaveRequest = $this->leaveService->getLeaveRequestById($id);

        if (!$leaveRequest || $leaveRequest->staff_id !== $staff->id) {
            abort(404);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string'],
        ]);

        try {
            $this->leaveService->cancelLeaveRequest($id, $user->id, $validated['reason'] ?? null);

            return redirect()->route('staff.leave.requests.index')
                ->with('success', 'Leave request cancelled successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function balance(Request $request): Response
    {
        $user = $request->user();

        if (!$user->hasStaffProfile()) {
            abort(403, 'You do not have a staff profile.');
        }

        $staff = $user->staffProfile;

        // Get leave balances
        $leaveBalances = [
            'annual' => [
                'total' => $staff->annual_leave_total,
                'used' => $staff->annual_leave_used,
                'remaining' => $staff->annual_leave_remaining,
            ],
            'sick' => [
                'total' => $staff->sick_leave_total,
                'used' => $staff->sick_leave_used,
                'remaining' => $staff->sick_leave_remaining,
            ],
            'emergency' => [
                'total' => $staff->emergency_leave_total,
                'used' => $staff->emergency_leave_used,
                'remaining' => $staff->emergency_leave_remaining,
            ],
        ];

        return Inertia::render('staff/leave/Balance', [
            'leaveBalances' => $leaveBalances,
            'staff' => $staff,
        ]);
    }
}
