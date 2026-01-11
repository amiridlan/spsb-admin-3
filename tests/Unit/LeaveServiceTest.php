<?php

use Modules\Staff\Models\LeaveRequest;
use Modules\Staff\Models\Staff;
use Modules\Staff\Services\LeaveService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(LeaveService::class);

    $this->user = User::factory()->create();
    $this->staff = Staff::factory()->create([
        'user_id' => $this->user->id,
        'annual_leave_total' => 15,
        'annual_leave_used' => 5,
        'sick_leave_total' => 10,
        'sick_leave_used' => 2,
    ]);
});

test('can calculate leave days correctly', function () {
    $days = $this->service->calculateLeaveDays('2026-01-10', '2026-01-15');

    expect($days)->toBe(6); // Inclusive of both dates
});

test('can check leave availability when sufficient balance exists', function () {
    $result = $this->service->checkLeaveAvailability(
        $this->staff->id,
        'annual',
        '2026-01-10',
        '2026-01-15'
    );

    expect($result['can_request'])->toBeTrue()
        ->and($result['total_days'])->toBe(6);
});

test('cannot request leave with insufficient balance', function () {
    $result = $this->service->checkLeaveAvailability(
        $this->staff->id,
        'annual',
        '2026-01-10',
        '2026-01-25' // 16 days, but only 10 remaining
    );

    expect($result['can_request'])->toBeFalse()
        ->and($result['message'])->toContain('Insufficient');
});

test('cannot request leave with overlapping approved request', function () {
    // Create an approved leave request
    LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-10',
        'end_date' => '2026-01-15',
        'total_days' => 6,
        'reason' => 'Vacation',
        'status' => 'approved',
    ]);

    $result = $this->service->checkLeaveAvailability(
        $this->staff->id,
        'annual',
        '2026-01-12',
        '2026-01-17'
    );

    expect($result['can_request'])->toBeFalse()
        ->and($result['message'])->toContain('already have approved leave');
});

test('can submit leave request', function () {
    $data = [
        'leave_type' => 'annual',
        'start_date' => '2026-01-10',
        'end_date' => '2026-01-12',
        'reason' => 'Need time off for personal matters',
    ];

    $request = $this->service->submitLeaveRequest($this->staff->id, $data);

    expect($request)->toBeInstanceOf(LeaveRequest::class)
        ->and($request->status)->toBe('pending')
        ->and($request->total_days)->toBe(3)
        ->and($request->staff_id)->toBe($this->staff->id);
});

test('cannot submit leave request with insufficient balance', function () {
    $data = [
        'leave_type' => 'annual',
        'start_date' => '2026-01-10',
        'end_date' => '2026-01-25', // 16 days
        'reason' => 'Need extended time off',
    ];

    expect(fn() => $this->service->submitLeaveRequest($this->staff->id, $data))
        ->toThrow(Exception::class, 'Insufficient');
});

test('can approve leave request and update balance', function () {
    $request = LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-10',
        'end_date' => '2026-01-12',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'pending',
    ]);

    $reviewer = User::factory()->create();
    $approved = $this->service->approveLeaveRequest($request->id, $reviewer->id, 'Approved');

    expect($approved->status)->toBe('approved')
        ->and($approved->head_reviewed_by)->toBe($reviewer->id)
        ->and($approved->head_review_notes)->toBe('Approved');

    // Check balance updated
    $this->staff->refresh();
    expect($this->staff->annual_leave_used)->toBe(8); // Was 5, now 5 + 3 = 8
});

test('cannot approve non-pending request', function () {
    $request = LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-10',
        'end_date' => '2026-01-12',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'approved',
    ]);

    $reviewer = User::factory()->create();

    expect(fn() => $this->service->approveLeaveRequest($request->id, $reviewer->id))
        ->toThrow(Exception::class, 'Only pending');
});

test('can reject leave request', function () {
    $request = LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-10',
        'end_date' => '2026-01-12',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'pending',
    ]);

    $reviewer = User::factory()->create();
    $rejected = $this->service->rejectLeaveRequest($request->id, $reviewer->id, 'Insufficient coverage');

    expect($rejected->status)->toBe('rejected')
        ->and($rejected->head_reviewed_by)->toBe($reviewer->id)
        ->and($rejected->head_review_notes)->toBe('Insufficient coverage');

    // Check balance NOT updated
    $this->staff->refresh();
    expect($this->staff->annual_leave_used)->toBe(5); // Unchanged
});

test('can cancel pending leave request', function () {
    $request = LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-10',
        'end_date' => '2026-01-12',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'pending',
    ]);

    $cancelled = $this->service->cancelLeaveRequest($request->id, $this->user->id, 'Changed plans');

    expect($cancelled->status)->toBe('cancelled')
        ->and($cancelled->head_review_notes)->toBe('Changed plans');

    // Balance should not change for pending request
    $this->staff->refresh();
    expect($this->staff->annual_leave_used)->toBe(5);
});

test('can cancel approved leave request and restore balance', function () {
    $request = LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-10',
        'end_date' => '2026-01-12',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'approved',
    ]);

    // Manually update balance as if it was approved
    $this->staff->update(['annual_leave_used' => 8]);

    $cancelled = $this->service->cancelLeaveRequest($request->id, $this->user->id, 'Emergency came up');

    expect($cancelled->status)->toBe('cancelled');

    // Balance should be restored
    $this->staff->refresh();
    expect($this->staff->annual_leave_used)->toBe(5); // 8 - 3 = 5
});

test('can get staff leave requests with filters', function () {
    LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-10',
        'end_date' => '2026-01-12',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'pending',
    ]);

    LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'sick',
        'start_date' => '2026-02-10',
        'end_date' => '2026-02-11',
        'total_days' => 2,
        'reason' => 'Flu',
        'status' => 'approved',
    ]);

    $requests = $this->service->getStaffLeaveRequests($this->staff->id);
    expect($requests)->toHaveCount(2);

    $pendingRequests = $this->service->getStaffLeaveRequests($this->staff->id, ['status' => 'pending']);
    expect($pendingRequests)->toHaveCount(1)
        ->and($pendingRequests->first()->leave_type)->toBe('annual');

    $sickRequests = $this->service->getStaffLeaveRequests($this->staff->id, ['leave_type' => 'sick']);
    expect($sickRequests)->toHaveCount(1)
        ->and($sickRequests->first()->status)->toBe('approved');
});

test('can get pending requests', function () {
    $staff2 = Staff::factory()->create();

    LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-10',
        'end_date' => '2026-01-12',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'pending',
    ]);

    LeaveRequest::create([
        'staff_id' => $staff2->id,
        'leave_type' => 'sick',
        'start_date' => '2026-02-10',
        'end_date' => '2026-02-11',
        'total_days' => 2,
        'reason' => 'Flu',
        'status' => 'pending',
    ]);

    LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-03-10',
        'end_date' => '2026-03-12',
        'total_days' => 3,
        'reason' => 'Trip',
        'status' => 'approved',
    ]);

    $pending = $this->service->getPendingRequests();
    expect($pending)->toHaveCount(2);
});
