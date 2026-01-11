<?php

use App\Models\Department;
use App\Models\User;
use Modules\Staff\Models\Staff;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->staff = User::factory()->create(['role' => 'staff']);
});

// Department Index Tests
test('admin can view departments list', function () {
    $this->actingAs($this->admin);

    $departments = Department::factory()->count(3)->create();

    $response = $this->get(route('admin.departments.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('admin/departments/Index')
        ->has('departments', 3)
    );
});

test('staff cannot view departments list', function () {
    $this->actingAs($this->staff);

    $response = $this->get(route('admin.departments.index'));

    $response->assertStatus(403);
});

// Department Create Tests
test('admin can view create department form', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.departments.create'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('admin/departments/Create')
        ->has('potentialHeads')
    );
});

test('admin can create a department', function () {
    $this->actingAs($this->admin);

    $departmentData = [
        'name' => 'Information Technology',
        'code' => 'IT',
        'description' => 'Manages IT infrastructure',
        'is_active' => true,
    ];

    $response = $this->post(route('admin.departments.store'), $departmentData);

    $response->assertRedirect(route('admin.departments.index'));
    $response->assertSessionHas('success', 'Department created successfully.');

    $this->assertDatabaseHas('departments', [
        'name' => 'Information Technology',
        'code' => 'IT',
    ]);
});

test('department name must be unique', function () {
    $this->actingAs($this->admin);

    Department::factory()->create(['name' => 'Information Technology']);

    $response = $this->post(route('admin.departments.store'), [
        'name' => 'Information Technology',
        'code' => 'IT',
    ]);

    $response->assertSessionHasErrors('name');
});

test('department code must be unique', function () {
    $this->actingAs($this->admin);

    Department::factory()->create(['code' => 'IT']);

    $response = $this->post(route('admin.departments.store'), [
        'name' => 'Information Tech',
        'code' => 'IT',
    ]);

    $response->assertSessionHasErrors('code');
});

// Department Edit Tests
test('admin can view edit department form', function () {
    $this->actingAs($this->admin);

    $department = Department::factory()->create();

    $response = $this->get(route('admin.departments.edit', $department));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('admin/departments/Edit')
        ->has('department')
        ->has('potentialHeads')
    );
});

test('admin can update a department', function () {
    $this->actingAs($this->admin);

    $department = Department::factory()->create([
        'name' => 'IT Department',
        'code' => 'IT',
    ]);

    $response = $this->put(route('admin.departments.update', $department), [
        'name' => 'Information Technology',
        'code' => 'TECH',
        'description' => 'Updated description',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.departments.index'));
    $response->assertSessionHas('success', 'Department updated successfully.');

    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'name' => 'Information Technology',
        'code' => 'TECH',
    ]);
});

// Department Delete Tests
test('admin can delete a department without staff', function () {
    $this->actingAs($this->admin);

    $department = Department::factory()->create();

    $response = $this->delete(route('admin.departments.destroy', $department));

    $response->assertRedirect(route('admin.departments.index'));
    $response->assertSessionHas('success', 'Department deleted successfully.');

    $this->assertDatabaseMissing('departments', [
        'id' => $department->id,
    ]);
});

test('admin cannot delete a department with assigned staff', function () {
    $this->actingAs($this->admin);

    $department = Department::factory()->create();
    $user = User::factory()->create(['role' => 'staff']);
    Staff::factory()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
    ]);

    $response = $this->delete(route('admin.departments.destroy', $department));

    $response->assertSessionHasErrors('error');

    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
    ]);
});

// Department-Head Assignment Tests
test('admin can assign a department head', function () {
    $this->actingAs($this->admin);

    $department = Department::factory()->create();
    $head = User::factory()->create(['role' => 'admin']);

    $response = $this->put(route('admin.departments.update', $department), [
        'name' => $department->name,
        'code' => $department->code,
        'head_user_id' => $head->id,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.departments.index'));

    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'head_user_id' => $head->id,
    ]);
});

test('admin can remove a department head', function () {
    $this->actingAs($this->admin);

    $head = User::factory()->create(['role' => 'admin']);
    $department = Department::factory()->create(['head_user_id' => $head->id]);

    $response = $this->put(route('admin.departments.update', $department), [
        'name' => $department->name,
        'code' => $department->code,
        'head_user_id' => null,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.departments.index'));

    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'head_user_id' => null,
    ]);
});

// Department-Staff Assignment Tests
test('admin can assign staff to a department', function () {
    $this->actingAs($this->admin);

    $department = Department::factory()->create();
    $staffUser = User::factory()->create(['role' => 'staff']);

    $response = $this->post(route('admin.staff.store'), [
        'user_id' => $staffUser->id,
        'department_id' => $department->id,
        'position' => 'Developer',
        'is_available' => true,
    ]);

    $response->assertRedirect(route('admin.staff.index'));

    $this->assertDatabaseHas('staff', [
        'user_id' => $staffUser->id,
        'department_id' => $department->id,
    ]);
});

test('admin can change staff department', function () {
    $this->actingAs($this->admin);

    $department1 = Department::factory()->create(['name' => 'IT']);
    $department2 = Department::factory()->create(['name' => 'HR']);

    $staffUser = User::factory()->create(['role' => 'staff']);
    $staff = Staff::factory()->create([
        'user_id' => $staffUser->id,
        'department_id' => $department1->id,
    ]);

    $response = $this->put(route('admin.staff.update', $staff), [
        'department_id' => $department2->id,
        'position' => $staff->position,
        'is_available' => $staff->is_available,
    ]);

    $response->assertRedirect(route('admin.staff.index'));

    $this->assertDatabaseHas('staff', [
        'id' => $staff->id,
        'department_id' => $department2->id,
    ]);
});

test('staff can be assigned with no department', function () {
    $this->actingAs($this->admin);

    $staffUser = User::factory()->create(['role' => 'staff']);

    $response = $this->post(route('admin.staff.store'), [
        'user_id' => $staffUser->id,
        'department_id' => null,
        'position' => 'Contractor',
        'is_available' => true,
    ]);

    $response->assertRedirect(route('admin.staff.index'));

    $this->assertDatabaseHas('staff', [
        'user_id' => $staffUser->id,
        'department_id' => null,
    ]);
});
