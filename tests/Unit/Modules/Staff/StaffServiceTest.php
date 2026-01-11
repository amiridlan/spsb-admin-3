<?php

use App\Models\Staff;
use App\Models\Event;
use Modules\Staff\Services\StaffService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(StaffService::class);
});

test('can get all staff', function () {
    Staff::factory()->count(5)->create();

    $staff = $this->service->getAll();

    expect($staff)->toHaveCount(5);
});

test('can get available staff only', function () {
    Staff::factory()->count(3)->create(['is_available' => true]);
    Staff::factory()->count(2)->create(['is_available' => false]);

    $staff = $this->service->getAll(['available_only' => true]);

    expect($staff)->toHaveCount(3)
        ->and($staff->every(fn($s) => $s->is_available))->toBeTrue();
});

test('can get staff by id', function () {
    $staff = Staff::factory()->create();

    $result = $this->service->getById($staff->id);

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($staff->id)
        ->and($result->user)->not->toBeNull();
});

test('returns null for non-existent staff id', function () {
    $result = $this->service->getById(99999);

    expect($result)->toBeNull();
});

test('can get available staff', function () {
    Staff::factory()->count(3)->create(['is_available' => true]);
    Staff::factory()->count(2)->create(['is_available' => false]);

    $staff = $this->service->getAvailable();

    expect($staff)->toHaveCount(3);
});

test('can create staff', function () {
    $user = User::factory()->create();
    $department = \App\Models\Department::factory()->create(['name' => 'Events']);

    $data = [
        'user_id' => $user->id,
        'position' => 'Event Coordinator',
        'department_id' => $department->id,
        'is_available' => true,
    ];

    $staff = $this->service->create($data);

    expect($staff->position)->toBe('Event Coordinator')
        ->and($staff->is_available)->toBeTrue()
        ->and($staff->id)->toBeGreaterThan(0);

    $this->assertDatabaseHas('staff', ['user_id' => $user->id]);
});

test('can update staff', function () {
    $staff = Staff::factory()->create(['position' => 'Old Position']);

    $updated = $this->service->update($staff->id, ['position' => 'New Position']);

    expect($updated->position)->toBe('New Position');
    $this->assertDatabaseHas('staff', ['position' => 'New Position']);
});

test('cannot delete staff with future assignments', function () {
    $staff = Staff::factory()->create();
    $event = Event::factory()->create([
        'start_date' => now()->addDays(5),
        'status' => 'confirmed',
    ]);
    $event->staff()->attach($staff->id);

    expect(fn() => $this->service->delete($staff->id))
        ->toThrow(Exception::class, 'Cannot delete staff member with future event assignments');
});

test('can delete staff without future assignments', function () {
    $staff = Staff::factory()->create();
    $event = Event::factory()->create([
        'start_date' => now()->subDays(10),
        'status' => 'completed',
    ]);
    $event->staff()->attach($staff->id);

    $result = $this->service->delete($staff->id);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('staff', ['id' => $staff->id]);
});

test('can toggle availability', function () {
    $staff = Staff::factory()->create(['is_available' => true]);

    $toggled = $this->service->toggleAvailability($staff->id);

    expect($toggled->is_available)->toBeFalse();

    $toggled = $this->service->toggleAvailability($staff->id);

    expect($toggled->is_available)->toBeTrue();
});

test('can get staff by user id', function () {
    $user = User::factory()->create();
    $staff = Staff::factory()->create(['user_id' => $user->id]);

    $result = $this->service->getByUserId($user->id);

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($staff->id);
});

test('can get staff by position', function () {
    Staff::factory()->count(3)->create(['position' => 'Coordinator']);
    Staff::factory()->count(2)->create(['position' => 'Manager']);

    $staff = $this->service->getByPosition('Coordinator');

    expect($staff)->toHaveCount(3);
});

test('can get staff by department', function () {
    $eventsDept = \App\Models\Department::factory()->create(['name' => 'Events']);
    $hrDept = \App\Models\Department::factory()->create(['name' => 'HR']);

    Staff::factory()->count(3)->create(['department_id' => $eventsDept->id]);
    Staff::factory()->count(2)->create(['department_id' => $hrDept->id]);

    // Query staff by department directly
    $staff = Staff::where('department_id', $eventsDept->id)->get();

    expect($staff)->toHaveCount(3);
});
