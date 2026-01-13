<?php

namespace App\Policies;

use App\Models\User;
use Modules\Staff\Models\LeaveRequest;

class LeaveRequestPolicy
{
    /**
     * Determine if user can approve as HR/Admin
     */
    public function approveAsHR(User $user, LeaveRequest $leaveRequest): bool
    {
        // Only admin and superadmin can approve as HR
        if (!($user->isAdmin() || $user->isSuperAdmin())) {
            return false;
        }

        // Cannot review if HR already reviewed
        if ($leaveRequest->hasHrReviewed()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if user can approve as Department Head
     */
    public function approveAsHead(User $user, LeaveRequest $leaveRequest): bool
    {
        // Must be head of department
        if (!$user->isHeadOfDepartment()) {
            \Log::info('approveAsHead: User is not head of department', ['user_id' => $user->id]);
            return false;
        }

        // Cannot review if Head already reviewed
        if ($leaveRequest->hasHeadReviewed()) {
            \Log::info('approveAsHead: Head has already reviewed', ['leave_request_id' => $leaveRequest->id]);
            return false;
        }

        // Ensure relationships are loaded
        $user->loadMissing('headOfDepartment');
        $leaveRequest->loadMissing('staff.department');

        // Get the department where this user is the head
        $department = $user->headOfDepartment;

        if (!$department) {
            \Log::info('approveAsHead: User has no department assigned', ['user_id' => $user->id]);
            return false;
        }

        // Check if the leave request staff belongs to this department
        $requestStaff = $leaveRequest->staff;

        // Staff must have a department
        if (!$requestStaff->department_id) {
            \Log::info('approveAsHead: Staff has no department', ['staff_id' => $requestStaff->id]);
            return false;
        }

        // User's department must match staff's department
        $authorized = $department->id === $requestStaff->department_id;

        \Log::info('approveAsHead: Final authorization result', [
            'authorized' => $authorized,
            'user_id' => $user->id,
            'user_dept_id' => $department->id,
            'staff_dept_id' => $requestStaff->department_id,
            'leave_request_id' => $leaveRequest->id,
        ]);

        return $authorized;
    }

    /**
     * Determine if user can reject as HR/Admin
     */
    public function rejectAsHR(User $user, LeaveRequest $leaveRequest): bool
    {
        // Only admin and superadmin can reject as HR
        if (!($user->isAdmin() || $user->isSuperAdmin())) {
            return false;
        }

        // Cannot review if HR already reviewed
        if ($leaveRequest->hasHrReviewed()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if user can reject as Department Head
     */
    public function rejectAsHead(User $user, LeaveRequest $leaveRequest): bool
    {
        // Must be head of department
        if (!$user->isHeadOfDepartment()) {
            return false;
        }

        // Cannot review if Head already reviewed
        if ($leaveRequest->hasHeadReviewed()) {
            return false;
        }

        // Ensure relationships are loaded
        $user->loadMissing('headOfDepartment');
        $leaveRequest->loadMissing('staff.department');

        // Get the department where this user is the head
        $department = $user->headOfDepartment;

        if (!$department) {
            return false;
        }

        // Check if the leave request staff belongs to this department
        $requestStaff = $leaveRequest->staff;

        // Staff must have a department
        if (!$requestStaff->department_id) {
            return false;
        }

        // User's department must match staff's department
        return $department->id === $requestStaff->department_id;
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
        if ($user->isHeadOfDepartment()) {
            // Ensure relationships are loaded
            $user->loadMissing('headOfDepartment');
            $leaveRequest->loadMissing('staff.department');

            $department = $user->headOfDepartment;
            $requestStaff = $leaveRequest->staff;

            if ($department && $requestStaff->department_id) {
                return $department->id === $requestStaff->department_id;
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
