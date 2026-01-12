<?php

use App\Models\User;
use Modules\Staff\Models\LeaveRequest;
use Modules\Staff\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->staffUser = User::factory()->create(['role' => 'staff']);
    $this->staff = Staff::factory()->create([
        'user_id' => $this->staffUser->id,
        'annual_leave_total' => 15,
        'annual_leave_used' => 5,
        'sick_leave_total' => 10,
        'sick_leave_used' => 2,
        'emergency_leave_total' => 5,
        'emergency_leave_used' => 0,
    ]);
});

// Staff Tests
test('staff can view their leave requests', function () {
    LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-20',
        'end_date' => '2026-01-22',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->staffUser)->get('/staff/leave/requests');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->component('staff/leave/Index')
            ->has('leaveRequests', 1)
        );
});

test('staff can view leave request create form', function () {
    $response = $this->actingAs($this->staffUser)->get('/staff/leave/requests/create');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->component('staff/leave/Create')
            ->has('leaveBalances')
        );
});

test('staff can submit leave request', function () {
    $data = [
        'leave_type' => 'annual',
        'start_date' => '2026-01-20',
        'end_date' => '2026-01-22',
        'reason' => 'Need time off for personal matters which requires at least ten characters',
    ];

    $response = $this->actingAs($this->staffUser)->post('/staff/leave/requests', $data);

    $response->assertRedirect('/staff/leave/requests')
        ->assertSessionHas('success');

    $this->assertDatabaseHas('leave_requests', [
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'status' => 'pending',
        'total_days' => 3,
    ]);
});

test('staff cannot submit leave request with insufficient balance', function () {
    $data = [
        'leave_type' => 'annual',
        'start_date' => '2026-01-20',
        'end_date' => '2026-02-05', // 17 days, but only 10 remaining
        'reason' => 'Need extended time off for personal reasons',
    ];

    $response = $this->actingAs($this->staffUser)->post('/staff/leave/requests', $data);

    $response->assertSessionHasErrors(['error']);

    $this->assertDatabaseMissing('leave_requests', [
        'staff_id' => $this->staff->id,
        'start_date' => '2026-01-20',
    ]);
});

test('staff cannot submit leave request with invalid data', function () {
    $data = [
        'leave_type' => 'annual',
        'start_date' => '2026-01-20',
        'end_date' => '2026-01-22',
        'reason' => 'Short', // Too short
    ];

    $response = $this->actingAs($this->staffUser)->post('/staff/leave/requests', $data);

    $response->assertSessionHasErrors(['reason']);
});

test('staff can view their leave request details', function () {
    $request = LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-20',
        'end_date' => '2026-01-22',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->staffUser)->get("/staff/leave/requests/{$request->id}");

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->component('staff/leave/Show')
            ->where('leaveRequest.id', $request->id)
        );
});

test('staff cannot view other staff leave request', function () {
    $otherStaff = Staff::factory()->create();
    $request = LeaveRequest::create([
        'staff_id' => $otherStaff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-20',
        'end_date' => '2026-01-22',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->staffUser)->get("/staff/leave/requests/{$request->id}");

    $response->assertNotFound();
});

test('staff can cancel their pending leave request', function () {
    $request = LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-20',
        'end_date' => '2026-01-22',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->staffUser)->post("/staff/leave/requests/{$request->id}/cancel", [
        'reason' => 'Changed plans',
    ]);

    $response->assertRedirect('/staff/leave/requests')
        ->assertSessionHas('success');

    $this->assertDatabaseHas('leave_requests', [
        'id' => $request->id,
        'status' => 'cancelled',
    ]);
});

test('staff can view leave balance', function () {
    $response = $this->actingAs($this->staffUser)->get('/staff/leave/balance');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->component('staff/leave/Balance')
            ->has('leaveBalances')
            ->has('staff')
        );
});

test('user without staff profile cannot access staff leave routes', function () {
    $regularUser = User::factory()->create(['role' => 'staff']);

    $response = $this->actingAs($regularUser)->get('/staff/leave/requests');

    $response->assertForbidden();
});

// Admin Tests
test('admin can view all leave requests', function () {
    $staff2 = Staff::factory()->create();

    LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-20',
        'end_date' => '2026-01-22',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'pending',
    ]);

    LeaveRequest::create([
        'staff_id' => $staff2->id,
        'leave_type' => 'sick',
        'start_date' => '2026-02-20',
        'end_date' => '2026-02-21',
        'total_days' => 2,
        'reason' => 'Flu',
        'status' => 'approved',
    ]);

    $response = $this->actingAs($this->admin)->get('/admin/leave/requests');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->component('admin/leave/Index')
            ->has('leaveRequests.data', 2)
            ->where('pendingCount', 1)
        );
});

test('admin can filter leave requests by status', function () {
    LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-20',
        'end_date' => '2026-01-22',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'pending',
    ]);

    LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'sick',
        'start_date' => '2026-02-20',
        'end_date' => '2026-02-21',
        'total_days' => 2,
        'reason' => 'Flu',
        'status' => 'approved',
    ]);

    $response = $this->actingAs($this->admin)->get('/admin/leave/requests?status=pending');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->component('admin/leave/Index')
            ->has('leaveRequests.data', 1)
            ->where('leaveRequests.data.0.status', 'pending')
        );
});

test('admin can view leave request details', function () {
    $request = LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-20',
        'end_date' => '2026-01-22',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->admin)->get("/admin/leave/requests/{$request->id}");

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->component('admin/leave/Show')
            ->where('leaveRequest.id', $request->id)
        );
});

test('admin can approve pending leave request', function () {
    $request = LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-20',
        'end_date' => '2026-01-22',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->admin)->post("/admin/leave/requests/{$request->id}/approve", [
        'notes' => 'Approved - coverage arranged',
    ]);

    $response->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('leave_requests', [
        'id' => $request->id,
        'status' => 'approved',
        'head_reviewed_by' => $this->admin->id,
        'head_review_notes' => 'Approved - coverage arranged',
    ]);

    // Check balance updated
    $this->staff->refresh();
    expect($this->staff->annual_leave_used)->toBe(8); // Was 5, now 5 + 3 = 8
});

test('admin can reject pending leave request', function () {
    $request = LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-20',
        'end_date' => '2026-01-22',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->admin)->post("/admin/leave/requests/{$request->id}/reject", [
        'reason' => 'Insufficient coverage during this period',
    ]);

    $response->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('leave_requests', [
        'id' => $request->id,
        'status' => 'rejected',
        'head_reviewed_by' => $this->admin->id,
        'head_review_notes' => 'Insufficient coverage during this period',
    ]);

    // Check balance NOT updated
    $this->staff->refresh();
    expect($this->staff->annual_leave_used)->toBe(5); // Unchanged
});

test('admin cannot reject without reason', function () {
    $request = LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-20',
        'end_date' => '2026-01-22',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->admin)->post("/admin/leave/requests/{$request->id}/reject", [
        'reason' => 'Short',
    ]);

    $response->assertSessionHasErrors(['reason']);
});

test('admin cannot approve already approved request', function () {
    $request = LeaveRequest::create([
        'staff_id' => $this->staff->id,
        'leave_type' => 'annual',
        'start_date' => '2026-01-20',
        'end_date' => '2026-01-22',
        'total_days' => 3,
        'reason' => 'Vacation',
        'status' => 'approved',
    ]);

    $response = $this->actingAs($this->admin)->post("/admin/leave/requests/{$request->id}/approve");

    $response->assertSessionHasErrors(['error']);
});

test('regular staff cannot access admin leave routes', function () {
    $response = $this->actingAs($this->staffUser)->get('/admin/leave/requests');

    $response->assertForbidden();
});

test('superadmin can access admin leave routes', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $response = $this->actingAs($superadmin)->get('/admin/leave/requests');

    $response->assertOk();
});
