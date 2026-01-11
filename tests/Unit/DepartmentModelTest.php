<?php

use App\Models\Department;
use App\Models\User;
use Modules\Staff\Models\Staff;

test('department can have a head', function () {
    $head = User::factory()->create(['role' => 'admin']);
    $department = Department::factory()->create(['head_user_id' => $head->id]);

    expect($department->head)->toBeInstanceOf(User::class);
    expect($department->head->id)->toBe($head->id);
});

test('department head relationship returns null when no head assigned', function () {
    $department = Department::factory()->create(['head_user_id' => null]);

    expect($department->head)->toBeNull();
});

test('department can have many staff members', function () {
    $department = Department::factory()->create();

    $users = User::factory()->count(3)->create(['role' => 'staff']);
    foreach ($users as $user) {
        Staff::factory()->create([
            'user_id' => $user->id,
            'department_id' => $department->id,
        ]);
    }

    expect($department->staff)->toHaveCount(3);
    expect($department->staff->first())->toBeInstanceOf(Staff::class);
});

test('department staff count is correct', function () {
    $department = Department::factory()->create();

    $users = User::factory()->count(5)->create(['role' => 'staff']);
    foreach ($users as $user) {
        Staff::factory()->create([
            'user_id' => $user->id,
            'department_id' => $department->id,
        ]);
    }

    expect($department->getStaffCount())->toBe(5);
});

test('department staff count returns zero when no staff assigned', function () {
    $department = Department::factory()->create();

    expect($department->getStaffCount())->toBe(0);
});

test('hasHead returns true when department has a head', function () {
    $head = User::factory()->create(['role' => 'admin']);
    $department = Department::factory()->create(['head_user_id' => $head->id]);

    expect($department->hasHead())->toBeTrue();
});

test('hasHead returns false when department has no head', function () {
    $department = Department::factory()->create(['head_user_id' => null]);

    expect($department->hasHead())->toBeFalse();
});

test('active scope filters only active departments', function () {
    Department::factory()->create(['is_active' => true, 'name' => 'Active Dept']);
    Department::factory()->create(['is_active' => false, 'name' => 'Inactive Dept']);
    Department::factory()->create(['is_active' => true, 'name' => 'Another Active']);

    $activeDepartments = Department::active()->get();

    expect($activeDepartments)->toHaveCount(2);
    expect($activeDepartments->every(fn ($dept) => $dept->is_active))->toBeTrue();
});

test('department can be created with all attributes', function () {
    $head = User::factory()->create(['role' => 'admin']);

    $department = Department::factory()->create([
        'name' => 'Information Technology',
        'code' => 'IT',
        'description' => 'Manages IT infrastructure',
        'head_user_id' => $head->id,
        'is_active' => true,
    ]);

    expect($department->name)->toBe('Information Technology');
    expect($department->code)->toBe('IT');
    expect($department->description)->toBe('Manages IT infrastructure');
    expect($department->head_user_id)->toBe($head->id);
    expect($department->is_active)->toBeTrue();
});

test('department is_active is cast to boolean', function () {
    $department = Department::factory()->create(['is_active' => 1]);

    expect($department->is_active)->toBeTrue();
    expect($department->is_active)->toBeBool();

    $inactiveDepartment = Department::factory()->create(['is_active' => 0]);

    expect($inactiveDepartment->is_active)->toBeFalse();
    expect($inactiveDepartment->is_active)->toBeBool();
});

test('department can be created without optional fields', function () {
    $department = Department::factory()->create([
        'name' => 'Marketing',
        'code' => null,
        'description' => null,
        'head_user_id' => null,
    ]);

    expect($department->name)->toBe('Marketing');
    expect($department->code)->toBeNull();
    expect($department->description)->toBeNull();
    expect($department->head_user_id)->toBeNull();
});
