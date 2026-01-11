<?php

use App\Models\Department;
use App\Models\User;
use Modules\Staff\Models\Staff;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->head = User::factory()->create(['role' => 'head_of_department']);
    $this->staff = User::factory()->create(['role' => 'staff']);
});

// User Role Tests
test('isHeadOfDepartment returns true for head_of_department role', function () {
    expect($this->head->isHeadOfDepartment())->toBeTrue();
});

test('isHeadOfDepartment returns false for other roles', function () {
    expect($this->admin->isHeadOfDepartment())->toBeFalse();
    expect($this->staff->isHeadOfDepartment())->toBeFalse();
});

// Department Head Relationship Tests
test('user can be assigned as head of a department', function () {
    $department = Department::factory()->create(['head_user_id' => $this->head->id]);

    expect($this->head->headOfDepartment)->toBeInstanceOf(Department::class);
    expect($this->head->headOfDepartment->id)->toBe($department->id);
});

test('user headOfDepartment relationship returns null when not a head', function () {
    expect($this->staff->headOfDepartment)->toBeNull();
});

// Navigation Access Tests
test('head_of_department can access staff assignments', function () {
    $this->actingAs($this->head);

    // Create staff profile for head
    Staff::factory()->create(['user_id' => $this->head->id]);

    $response = $this->get(route('staff.assignments.index'));

    $response->assertStatus(200);
});

test('head_of_department can access staff leave requests', function () {
    $this->actingAs($this->head);

    // Create staff profile for head
    Staff::factory()->create(['user_id' => $this->head->id]);

    $response = $this->get(route('staff.leave.requests.index'));

    $response->assertStatus(200);
});

// Admin Access Tests
test('head_of_department cannot access admin users page', function () {
    $this->actingAs($this->head);

    $response = $this->get(route('admin.users.index'));

    $response->assertStatus(403);
});

test('head_of_department cannot access admin departments page', function () {
    $this->actingAs($this->head);

    $response = $this->get(route('admin.departments.index'));

    $response->assertStatus(403);
});

test('head_of_department cannot access admin staff page', function () {
    $this->actingAs($this->head);

    $response = $this->get(route('admin.staff.index'));

    $response->assertStatus(403);
});

test('head_of_department cannot access admin events page', function () {
    $this->actingAs($this->head);

    $response = $this->get('/admin/events');

    $response->assertStatus(403);
});

// User Management Tests
test('admin can create head_of_department user', function () {
    $this->actingAs($this->admin);

    $response = $this->post(route('admin.users.store'), [
        'name' => 'Department Head',
        'email' => 'head@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'head_of_department',
    ]);

    $response->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'head@example.com',
        'role' => 'head_of_department',
    ]);
});

test('admin can update user to head_of_department role', function () {
    $this->actingAs($this->admin);

    $user = User::factory()->create(['role' => 'staff']);

    $response = $this->put(route('admin.users.update', $user), [
        'name' => $user->name,
        'email' => $user->email,
        'role' => 'head_of_department',
    ]);

    $response->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'role' => 'head_of_department',
    ]);
});

test('admin can assign head_of_department user to department', function () {
    $this->actingAs($this->admin);

    $department = Department::factory()->create(['head_user_id' => null]);

    $response = $this->put(route('admin.departments.update', $department), [
        'name' => $department->name,
        'code' => $department->code,
        'head_user_id' => $this->head->id,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.departments.index'));

    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'head_user_id' => $this->head->id,
    ]);

    // Verify relationship works
    $department->refresh();
    expect($department->head->id)->toBe($this->head->id);
    expect($this->head->headOfDepartment->id)->toBe($department->id);
});

test('head_of_department can be staff member with department assignment', function () {
    $this->actingAs($this->admin);

    $department = Department::factory()->create(['head_user_id' => $this->head->id]);

    // Create staff profile for the head
    $response = $this->post(route('admin.staff.store'), [
        'user_id' => $this->head->id,
        'department_id' => $department->id,
        'position' => 'Department Manager',
        'is_available' => true,
    ]);

    $response->assertRedirect(route('admin.staff.index'));

    $this->assertDatabaseHas('staff', [
        'user_id' => $this->head->id,
        'department_id' => $department->id,
    ]);
});

// Dashboard Access Tests
test('head_of_department can access dashboard', function () {
    $this->actingAs($this->head);

    $response = $this->get(route('dashboard'));

    $response->assertStatus(200);
});
