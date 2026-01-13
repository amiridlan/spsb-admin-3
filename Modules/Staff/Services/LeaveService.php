<?php

namespace Modules\Staff\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Modules\Staff\Contracts\LeaveServiceInterface;
use Modules\Staff\Models\LeaveRequest;
use Modules\Staff\Models\Staff;

class LeaveService implements LeaveServiceInterface
{
    public function submitLeaveRequest(int $staffId, array $data): LeaveRequest
    {
        $staff = Staff::findOrFail($staffId);

        // Calculate leave days
        $totalDays = $this->calculateLeaveDays($data['start_date'], $data['end_date']);

        // Check availability
        $availability = $this->checkLeaveAvailability(
            $staffId,
            $data['leave_type'],
            $data['start_date'],
            $data['end_date']
        );

        if (!$availability['can_request']) {
            throw new \Exception($availability['message']);
        }

        // Detect conflicts with events
        $conflicts = $this->detectEventConflicts($staffId, $data['start_date'], $data['end_date']);

        // Create request
        $request = LeaveRequest::create([
            'staff_id' => $staffId,
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_days' => $totalDays,
            'reason' => $data['reason'],
            'status' => 'pending',
            'conflict_events' => $conflicts,
        ]);

        return $request->load(['staff.user']);
    }

    public function approveLeaveRequest(int $requestId, int $reviewerUserId, ?string $notes = null): LeaveRequest
    {
        $request = LeaveRequest::findOrFail($requestId);

        if (!$request->isPending()) {
            throw new \Exception('Only pending leave requests can be approved.');
        }

        // Update request status
        $request->update([
            'status' => 'approved',
            'head_reviewed_by' => $reviewerUserId,
            'head_review_notes' => $notes,
            'head_reviewed_at' => now(),
        ]);

        // Update staff leave balance
        $staff = $request->staff;
        $field = $request->leave_type . '_leave_used';
        $staff->$field += $request->total_days;
        $staff->save();

        return $request->load(['staff.user', 'headReviewer']);
    }

    public function rejectLeaveRequest(int $requestId, int $reviewerUserId, string $reason): LeaveRequest
    {
        $request = LeaveRequest::findOrFail($requestId);

        if (!$request->isPending()) {
            throw new \Exception('Only pending leave requests can be rejected.');
        }

        // Update request status
        $request->update([
            'status' => 'rejected',
            'head_reviewed_by' => $reviewerUserId,
            'head_review_notes' => $reason,
            'head_reviewed_at' => now(),
        ]);

        return $request->load(['staff.user', 'headReviewer']);
    }

    /**
     * HR/Admin approves a leave request
     */
    public function approveAsHR(int $requestId, int $hrUserId, ?string $notes = null): LeaveRequest
    {
        $request = LeaveRequest::findOrFail($requestId);

        if (!$request->isPending()) {
            throw new \Exception('Only pending leave requests can be approved.');
        }

        if ($request->hasHrReviewed()) {
            throw new \Exception('This request has already been reviewed by HR.');
        }

        // Check if Head has already approved
        $headAlreadyApproved = $request->head_reviewed_by !== null;

        // Set status to approved if Head already approved, otherwise remain pending
        $newStatus = $headAlreadyApproved ? LeaveRequest::STATUS_APPROVED : LeaveRequest::STATUS_PENDING;

        // Update request
        $request->update([
            'status' => $newStatus,
            'hr_reviewed_by' => $hrUserId,
            'hr_review_notes' => $notes,
            'hr_reviewed_at' => now(),
        ]);

        // Update staff leave balance ONLY if both approvals are complete
        if ($newStatus === LeaveRequest::STATUS_APPROVED) {
            $staff = $request->staff;
            $field = $request->leave_type . '_leave_used';
            $staff->$field += $request->total_days;
            $staff->save();
        }

        return $request->load(['staff.user', 'hrReviewer', 'headReviewer']);
    }

    /**
     * Department Head approves a leave request
     */
    public function approveAsHead(int $requestId, int $headUserId, ?string $notes = null): LeaveRequest
    {
        $request = LeaveRequest::findOrFail($requestId);

        if (!$request->isPending()) {
            throw new \Exception('Only pending leave requests can be approved.');
        }

        if ($request->hasHeadReviewed()) {
            throw new \Exception('This request has already been reviewed by the department head.');
        }

        // Check if HR has already approved
        $hrAlreadyApproved = $request->hr_reviewed_by !== null;

        // Set status to approved if HR already approved, otherwise remain pending
        $newStatus = $hrAlreadyApproved ? LeaveRequest::STATUS_APPROVED : LeaveRequest::STATUS_PENDING;

        // Update request
        $request->update([
            'status' => $newStatus,
            'head_reviewed_by' => $headUserId,
            'head_review_notes' => $notes,
            'head_reviewed_at' => now(),
        ]);

        // Update staff leave balance ONLY if both approvals are complete
        if ($newStatus === LeaveRequest::STATUS_APPROVED) {
            $staff = $request->staff;
            $field = $request->leave_type . '_leave_used';
            $staff->$field += $request->total_days;
            $staff->save();
        }

        return $request->load(['staff.user', 'hrReviewer', 'headReviewer']);
    }

    /**
     * HR/Admin rejects a leave request
     */
    public function rejectAsHR(int $requestId, int $hrUserId, string $reason): LeaveRequest
    {
        $request = LeaveRequest::findOrFail($requestId);

        if (!$request->isPending()) {
            throw new \Exception('Only pending leave requests can be rejected.');
        }

        if ($request->hasHrReviewed()) {
            throw new \Exception('This request has already been reviewed by HR.');
        }

        // Update request status to rejected
        $request->update([
            'status' => LeaveRequest::STATUS_REJECTED,
            'hr_reviewed_by' => $hrUserId,
            'hr_review_notes' => $reason,
            'hr_reviewed_at' => now(),
        ]);

        return $request->load(['staff.user', 'hrReviewer', 'headReviewer']);
    }

    /**
     * Department Head rejects a leave request
     */
    public function rejectAsHead(int $requestId, int $headUserId, string $reason): LeaveRequest
    {
        $request = LeaveRequest::findOrFail($requestId);

        if (!$request->isPending()) {
            throw new \Exception('Only pending leave requests can be rejected.');
        }

        if ($request->hasHeadReviewed()) {
            throw new \Exception('This request has already been reviewed by the department head.');
        }

        // Update request status to rejected
        $request->update([
            'status' => LeaveRequest::STATUS_REJECTED,
            'head_reviewed_by' => $headUserId,
            'head_review_notes' => $reason,
            'head_reviewed_at' => now(),
        ]);

        return $request->load(['staff.user', 'hrReviewer', 'headReviewer']);
    }

    public function cancelLeaveRequest(int $requestId, int $userId, ?string $reason = null): LeaveRequest
    {
        $request = LeaveRequest::findOrFail($requestId);

        if ($request->isCancelled()) {
            throw new \Exception('Leave request is already cancelled.');
        }

        $previousStatus = $request->status;

        // Update request status
        $request->update([
            'status' => 'cancelled',
            'head_review_notes' => $reason,
        ]);

        // If was approved, restore the leave balance
        if ($previousStatus === 'approved') {
            $staff = $request->staff;
            $field = $request->leave_type . '_leave_used';
            $staff->$field -= $request->total_days;
            // Ensure it doesn't go below 0
            $staff->$field = max(0, $staff->$field);
            $staff->save();
        }

        return $request->load(['staff.user']);
    }

    public function getStaffLeaveRequests(int $staffId, array $filters = []): Collection
    {
        $query = LeaveRequest::where('staff_id', $staffId)
            ->with(['hrReviewer', 'headReviewer']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['year'])) {
            $query->whereYear('start_date', $filters['year']);
        }

        if (isset($filters['leave_type'])) {
            $query->where('leave_type', $filters['leave_type']);
        }

        return $query->orderBy('start_date', 'desc')->get();
    }

    public function getPendingRequests(): Collection
    {
        return LeaveRequest::pending()
            ->with(['staff.user', 'hrReviewer', 'headReviewer'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getLeaveRequestById(int $id): ?LeaveRequest
    {
        return LeaveRequest::with(['staff.user', 'staff.department', 'hrReviewer', 'headReviewer'])->find($id);
    }

    public function checkLeaveAvailability(int $staffId, string $leaveType, string $startDate, string $endDate): array
    {
        $staff = Staff::findOrFail($staffId);
        $totalDays = $this->calculateLeaveDays($startDate, $endDate);

        // Check balance
        $totalField = $leaveType . '_leave_total';
        $usedField = $leaveType . '_leave_used';

        $available = $staff->$totalField - $staff->$usedField;

        if ($available < $totalDays) {
            return [
                'can_request' => false,
                'message' => "Insufficient {$leaveType} leave balance. Available: {$available} days, Requested: {$totalDays} days.",
            ];
        }

        // Check for overlapping approved leaves
        $overlapping = LeaveRequest::where('staff_id', $staffId)
            ->approved()
            ->forDateRange($startDate, $endDate)
            ->exists();

        if ($overlapping) {
            return [
                'can_request' => false,
                'message' => 'You already have approved leave during this period.',
            ];
        }

        return [
            'can_request' => true,
            'message' => 'Leave request can be submitted.',
            'total_days' => $totalDays,
        ];
    }

    public function calculateLeaveDays(string $startDate, string $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Simple calculation: inclusive days
        // Can be enhanced to exclude weekends/holidays
        $days = $start->diffInDays($end) + 1;

        return (int) $days;
    }

    public function detectEventConflicts(int $staffId, string $startDate, string $endDate): ?array
    {
        $staff = Staff::findOrFail($staffId);

        $conflictingEvents = $staff->events()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                    });
            })
            ->with('eventSpace')
            ->get();

        if ($conflictingEvents->isEmpty()) {
            return null;
        }

        return $conflictingEvents->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start_date' => $event->start_date->toDateString(),
                'end_date' => $event->end_date->toDateString(),
                'event_space' => $event->eventSpace->name ?? null,
            ];
        })->toArray();
    }
}
