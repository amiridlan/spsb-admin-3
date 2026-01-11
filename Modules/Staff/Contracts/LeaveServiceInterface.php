<?php

namespace Modules\Staff\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Staff\Models\LeaveRequest;

interface LeaveServiceInterface
{
    /**
     * Submit a leave request
     */
    public function submitLeaveRequest(int $staffId, array $data): LeaveRequest;

    /**
     * Approve a leave request
     */
    public function approveLeaveRequest(int $requestId, int $reviewerUserId, ?string $notes = null): LeaveRequest;

    /**
     * Reject a leave request
     */
    public function rejectLeaveRequest(int $requestId, int $reviewerUserId, string $reason): LeaveRequest;

    /**
     * Cancel a leave request
     */
    public function cancelLeaveRequest(int $requestId, int $userId, ?string $reason = null): LeaveRequest;

    /**
     * Get leave requests for a staff member
     */
    public function getStaffLeaveRequests(int $staffId, array $filters = []): Collection;

    /**
     * Get all pending leave requests
     */
    public function getPendingRequests(): Collection;

    /**
     * Get a leave request by ID
     */
    public function getLeaveRequestById(int $id): ?LeaveRequest;

    /**
     * Check if leave dates are available
     */
    public function checkLeaveAvailability(int $staffId, string $leaveType, string $startDate, string $endDate): array;

    /**
     * Calculate business days between dates
     */
    public function calculateLeaveDays(string $startDate, string $endDate): int;

    /**
     * Detect conflicts with event assignments
     */
    public function detectEventConflicts(int $staffId, string $startDate, string $endDate): ?array;
}
