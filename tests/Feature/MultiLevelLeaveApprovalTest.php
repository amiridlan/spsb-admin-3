<?php

use App\Models\Department;
use App\Models\User;
use Modules\Staff\Models\LeaveRequest;
use Modules\Staff\Models\Staff;
use Modules\Staff\Services\LeaveService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->leaveService = app(LeaveService::class);
});

test('complete two-step approval workflow updates balance on final approval', function () {
    // Create department
    $department = Department::factory()->create(['name' => 'Engineering']);

    // Create HR/Admin user
    $hrUser = User::factory()->create(['role' => 'admin']);

    // Create department head user with staff profile
    $headUser = User::factory()->create(['role' => 'head_of_department']);
    $headStaff = Staff::factory()->create([
        'user_id' => $headUser->id,
        'department_id' => $department->id,
    ]);
    $department->update(['head_user_id' => $headUser->id]);

    // Create staff member in the department
    $staffUser = User::factory()->create(['role' => 'staff']);
    $staff = Staff::factory()->create([
        'user_id' => $staffUser->id,
        'department_id' => $department->id,
        'annual_leave_total' => 20,
        'annual_leave_used' => 0,
    ]);

    // Step 1: Staff submits leave request
    $leaveRequest = $this->leaveService->submitLeaveRequest($staff->id, [
        'leave_type' => 'annual',
        'start_date' => now()->addDays(10)->toDateString(),
        'end_date' => now()->addDays(12)->toDateString(),
        'reason' => 'Family vacation',
    ]);

    expect($leaveRequest->status)->toBe('pending')
        ->and($leaveRequest->total_days)->toBe(3);

    // Verify balance NOT updated yet
    $staff->refresh();
    expect($staff->annual_leave_used)->toBe(0);

    // Step 2: HR approves
    $hrApproved = $this->leaveService->approveAsHR($leaveRequest->id, $hrUser->id, 'Approved by HR');

    expect($hrApproved->status)->toBe('hr_approved')
        ->and($hrApproved->hr_reviewed_by)->toBe($hrUser->id)
        ->and($hrApproved->hr_review_notes)->toBe('Approved by HR')
        ->and($hrApproved->hr_reviewed_at)->not->toBeNull();

    // Verify balance STILL NOT updated (only on final approval)
    $staff->refresh();
    expect($staff->annual_leave_used)->toBe(0);

    // Step 3: Department Head approves (final approval)
    $finalApproved = $this->leaveService->approveAsHead($leaveRequest->id, $headUser->id, 'Team coverage arranged');

    expect($finalApproved->status)->toBe('approved')
        ->and($finalApproved->head_reviewed_by)->toBe($headUser->id)
        ->and($finalApproved->head_review_notes)->toBe('Team coverage arranged')
        ->and($finalApproved->head_reviewed_at)->not->toBeNull();

    // Verify balance IS UPDATED on final approval
    $staff->refresh();
    expect($staff->annual_leave_used)->toBe(3);
});

test('HR can reject leave request immediately', function () {
    $hrUser = User::factory()->create(['role' => 'admin']);
    $staff = Staff::factory()->create();

    $leaveRequest = $this->leaveService->submitLeaveRequest($staff->id, [
        'leave_type' => 'annual',
        'start_date' => now()->addDays(5)->toDateString(),
        'end_date' => now()->addDays(7)->toDateString(),
        'reason' => 'Personal matters',
    ]);

    expect($leaveRequest->status)->toBe('pending');

    // HR rejects
    $rejected = $this->leaveService->rejectAsHR($leaveRequest->id, $hrUser->id, 'Insufficient leave balance');

    expect($rejected->status)->toBe('rejected')
        ->and($rejected->hr_reviewed_by)->toBe($hrUser->id)
        ->and($rejected->hr_review_notes)->toBe('Insufficient leave balance');

    // Verify balance not updated
    $staff->refresh();
    expect($staff->annual_leave_used)->toBe(0);
});

test('department head can reject HR-approved request', function () {
    $department = Department::factory()->create();
    $hrUser = User::factory()->create(['role' => 'admin']);
    $headUser = User::factory()->create(['role' => 'head_of_department']);

    $headStaff = Staff::factory()->create([
        'user_id' => $headUser->id,
        'department_id' => $department->id,
    ]);
    $department->update(['head_user_id' => $headUser->id]);

    $staff = Staff::factory()->create(['department_id' => $department->id]);

    $leaveRequest = $this->leaveService->submitLeaveRequest($staff->id, [
        'leave_type' => 'annual',
        'start_date' => now()->addDays(5)->toDateString(),
        'end_date' => now()->addDays(7)->toDateString(),
        'reason' => 'Personal matters',
    ]);

    // HR approves
    $this->leaveService->approveAsHR($leaveRequest->id, $hrUser->id);

    $leaveRequest->refresh();
    expect($leaveRequest->status)->toBe('hr_approved');

    // Head rejects
    $rejected = $this->leaveService->rejectAsHead($leaveRequest->id, $headUser->id, 'Peak workload period');

    expect($rejected->status)->toBe('rejected')
        ->and($rejected->head_reviewed_by)->toBe($headUser->id)
        ->and($rejected->head_review_notes)->toBe('Peak workload period');

    // Verify balance not updated
    $staff->refresh();
    expect($staff->annual_leave_used)->toBe(0);
});

test('cannot HR approve a non-pending request', function () {
    $hrUser = User::factory()->create(['role' => 'admin']);
    $staff = Staff::factory()->create();

    $leaveRequest = LeaveRequest::factory()->create([
        'staff_id' => $staff->id,
        'status' => 'approved',
    ]);

    expect(fn() => $this->leaveService->approveAsHR($leaveRequest->id, $hrUser->id))
        ->toThrow(Exception::class, 'Only pending leave requests can be approved by HR');
});

test('cannot head approve a non-HR-approved request', function () {
    $department = Department::factory()->create();
    $headUser = User::factory()->create(['role' => 'head_of_department']);

    $headStaff = Staff::factory()->create([
        'user_id' => $headUser->id,
        'department_id' => $department->id,
    ]);
    $department->update(['head_user_id' => $headUser->id]);

    $staff = Staff::factory()->create(['department_id' => $department->id]);

    $leaveRequest = LeaveRequest::factory()->create([
        'staff_id' => $staff->id,
        'status' => 'pending', // Not hr_approved
    ]);

    expect(fn() => $this->leaveService->approveAsHead($leaveRequest->id, $headUser->id))
        ->toThrow(Exception::class, 'Leave request must be HR approved before head can approve');
});

test('scopes work correctly for filtering requests', function () {
    $staff = Staff::factory()->create();

    LeaveRequest::factory()->create(['staff_id' => $staff->id, 'status' => 'pending']);
    LeaveRequest::factory()->create(['staff_id' => $staff->id, 'status' => 'pending']);
    LeaveRequest::factory()->create(['staff_id' => $staff->id, 'status' => 'hr_approved']);
    LeaveRequest::factory()->create(['staff_id' => $staff->id, 'status' => 'hr_approved']);
    LeaveRequest::factory()->create(['staff_id' => $staff->id, 'status' => 'hr_approved']);
    LeaveRequest::factory()->create(['staff_id' => $staff->id, 'status' => 'approved']);

    $pending = LeaveRequest::pending()->get();
    $hrApproved = LeaveRequest::hrApproved()->get();
    $pendingHeadApproval = LeaveRequest::pendingHeadApproval()->get();
    $approved = LeaveRequest::approved()->get();

    expect($pending)->toHaveCount(2)
        ->and($hrApproved)->toHaveCount(3)
        ->and($pendingHeadApproval)->toHaveCount(3) // Same as hr_approved
        ->and($approved)->toHaveCount(1);
});

test('helper methods work correctly', function () {
    $staff = Staff::factory()->create();

    $pending = LeaveRequest::factory()->create(['staff_id' => $staff->id, 'status' => 'pending']);
    $hrApproved = LeaveRequest::factory()->create(['staff_id' => $staff->id, 'status' => 'hr_approved']);
    $approved = LeaveRequest::factory()->create(['staff_id' => $staff->id, 'status' => 'approved']);
    $rejected = LeaveRequest::factory()->create(['staff_id' => $staff->id, 'status' => 'rejected']);

    expect($pending->isPending())->toBeTrue()
        ->and($pending->isHrApproved())->toBeFalse()
        ->and($pending->isApproved())->toBeFalse();

    expect($hrApproved->isPending())->toBeFalse()
        ->and($hrApproved->isHrApproved())->toBeTrue()
        ->and($hrApproved->isPendingHeadApproval())->toBeTrue()
        ->and($hrApproved->isApproved())->toBeFalse();

    expect($approved->isPending())->toBeFalse()
        ->and($approved->isHrApproved())->toBeFalse()
        ->and($approved->isApproved())->toBeTrue();

    expect($rejected->isRejected())->toBeTrue();
});

test('relationships load correctly', function () {
    $hrUser = User::factory()->create(['role' => 'admin', 'name' => 'HR Manager']);
    $headUser = User::factory()->create(['role' => 'head_of_department', 'name' => 'Department Head']);
    $staff = Staff::factory()->create();

    $leaveRequest = LeaveRequest::factory()->create([
        'staff_id' => $staff->id,
        'status' => 'approved',
        'hr_reviewed_by' => $hrUser->id,
        'head_reviewed_by' => $headUser->id,
    ]);

    $loaded = LeaveRequest::with(['hrReviewer', 'headReviewer'])->find($leaveRequest->id);

    expect($loaded->hrReviewer)->not->toBeNull()
        ->and($loaded->hrReviewer->name)->toBe('HR Manager')
        ->and($loaded->headReviewer)->not->toBeNull()
        ->and($loaded->headReviewer->name)->toBe('Department Head');
});

test('cancelling approved request restores leave balance', function () {
    $department = Department::factory()->create();
    $hrUser = User::factory()->create(['role' => 'admin']);
    $headUser = User::factory()->create(['role' => 'head_of_department']);

    $headStaff = Staff::factory()->create([
        'user_id' => $headUser->id,
        'department_id' => $department->id,
    ]);
    $department->update(['head_user_id' => $headUser->id]);

    $staffUser = User::factory()->create(['role' => 'staff']);
    $staff = Staff::factory()->create([
        'user_id' => $staffUser->id,
        'department_id' => $department->id,
        'annual_leave_total' => 20,
        'annual_leave_used' => 0,
    ]);

    // Submit, HR approve, Head approve
    $leaveRequest = $this->leaveService->submitLeaveRequest($staff->id, [
        'leave_type' => 'annual',
        'start_date' => now()->addDays(10)->toDateString(),
        'end_date' => now()->addDays(12)->toDateString(),
        'reason' => 'Vacation',
    ]);

    $this->leaveService->approveAsHR($leaveRequest->id, $hrUser->id);
    $this->leaveService->approveAsHead($leaveRequest->id, $headUser->id);

    $staff->refresh();
    expect($staff->annual_leave_used)->toBe(3);

    // Cancel the request
    $cancelled = $this->leaveService->cancelLeaveRequest($leaveRequest->id, $staffUser->id, 'Plans changed');

    expect($cancelled->status)->toBe('cancelled');

    // Balance restored
    $staff->refresh();
    expect($staff->annual_leave_used)->toBe(0);
});

test('status constants are defined correctly', function () {
    expect(LeaveRequest::STATUS_PENDING)->toBe('pending')
        ->and(LeaveRequest::STATUS_HR_APPROVED)->toBe('hr_approved')
        ->and(LeaveRequest::STATUS_APPROVED)->toBe('approved')
        ->and(LeaveRequest::STATUS_REJECTED)->toBe('rejected')
        ->and(LeaveRequest::STATUS_CANCELLED)->toBe('cancelled');
});

test('hr approved requests are visible to department head', function () {
    $department = Department::factory()->create(['name' => 'IT']);
    $otherDepartment = Department::factory()->create(['name' => 'HR']);

    $headUser = User::factory()->create(['role' => 'head_of_department']);
    $headStaff = Staff::factory()->create([
        'user_id' => $headUser->id,
        'department_id' => $department->id,
    ]);
    $department->update(['head_user_id' => $headUser->id]);

    // Create staff in IT department with HR-approved request
    $staff1 = Staff::factory()->create(['department_id' => $department->id]);
    $request1 = LeaveRequest::factory()->create([
        'staff_id' => $staff1->id,
        'status' => 'hr_approved',
    ]);

    // Create staff in other department with HR-approved request
    $staff2 = Staff::factory()->create(['department_id' => $otherDepartment->id]);
    $request2 = LeaveRequest::factory()->create([
        'staff_id' => $staff2->id,
        'status' => 'hr_approved',
    ]);

    // Query HR-approved requests for IT department
    $itRequests = LeaveRequest::hrApproved()
        ->whereHas('staff', function ($q) use ($department) {
            $q->where('department_id', $department->id);
        })
        ->get();

    expect($itRequests)->toHaveCount(1)
        ->and($itRequests->first()->id)->toBe($request1->id);
});
