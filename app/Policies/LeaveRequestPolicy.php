<?php

namespace App\Policies;

use App\Models\User;
use Modules\Staff\Models\LeaveRequest;

class LeaveRequestPolicy
{
    /**
     * Determine if user can approve as HR/Admin (first step)
     */
    public function approveAsHR(User $user, LeaveRequest $leaveRequest): bool
    {
        // Only admin and superadmin can approve as HR
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine if user can approve as Department Head (second step)
     */
    public function approveAsHead(User $user, LeaveRequest $leaveRequest): bool
    {
        // Must be head of department
        if (!$user->isHeadOfDepartment()) {
            return false;
        }

        // Must have a staff profile
        if (!$user->hasStaffProfile()) {
            return false;
        }

        $userStaff = $user->staffProfile;

        // Must have a department assignment
        if (!$userStaff->department_id) {
            return false;
        }

        // Check if user is head of the leave request staff's department
        $requestStaff = $leaveRequest->staff;

        // Staff must have a department
        if (!$requestStaff->department_id) {
            return false;
        }

        // User's department must match staff's department
        if ($userStaff->department_id !== $requestStaff->department_id) {
            return false;
        }

        // Check if user is actually assigned as head of that department
        $department = $userStaff->department;
        return $department && $department->head_user_id === $user->id;
    }

    /**
     * Determine if user can reject as HR/Admin
     */
    public function rejectAsHR(User $user, LeaveRequest $leaveRequest): bool
    {
        // Same as approveAsHR
        return $this->approveAsHR($user, $leaveRequest);
    }

    /**
     * Determine if user can reject as Department Head
     */
    public function rejectAsHead(User $user, LeaveRequest $leaveRequest): bool
    {
        // Same as approveAsHead
        return $this->approveAsHead($user, $leaveRequest);
    }

    /**
     * Determine if user can view the leave request
     */
    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        // Admins and superadmins can view all
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        // Staff can view their own requests
        if ($user->hasStaffProfile() && $user->staffProfile->id === $leaveRequest->staff_id) {
            return true;
        }

        // Department heads can view requests from their department
        if ($user->isHeadOfDepartment() && $user->hasStaffProfile()) {
            $userStaff = $user->staffProfile;
            $requestStaff = $leaveRequest->staff;

            if ($userStaff->department_id && $requestStaff->department_id) {
                $department = $userStaff->department;
                return $department
                    && $department->head_user_id === $user->id
                    && $userStaff->department_id === $requestStaff->department_id;
            }
        }

        return false;
    }

    /**
     * Determine if user can cancel the leave request
     */
    public function cancel(User $user, LeaveRequest $leaveRequest): bool
    {
        // Only the staff who created the request can cancel it
        return $user->hasStaffProfile() && $user->staffProfile->id === $leaveRequest->staff_id;
    }

    /**
     * Determine if user can view any leave requests
     */
    public function viewAny(User $user): bool
    {
        // Admins, superadmins, staff, and heads can view leave requests
        return $user->isAdmin()
            || $user->isSuperAdmin()
            || $user->hasStaffProfile()
            || $user->isHeadOfDepartment();
    }

    /**
     * Determine if user can create leave requests
     */
    public function create(User $user): bool
    {
        // Only users with staff profiles can create leave requests
        return $user->hasStaffProfile();
    }
}
