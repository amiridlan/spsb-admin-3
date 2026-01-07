<?php

use App\Models\Staff;
use App\Models\Event;
use Modules\Staff\Services\StaffAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(StaffAvailabilityService::class);
});

test('staff is available when no conflicting assignments', function () {
    $staff = Staff::factory()->create(['is_available' => true]);
    $startDate = now()->addDays(5);
    $endDate = now()->addDays(7);

    $isAvailable = $this->service->isAvailable($staff, $startDate, $endDate);

    expect($isAvailable)->toBeTrue();
});

test('staff is not available when is_available is false', function () {
    $staff = Staff::factory()->create(['is_available' => false]);
    $startDate = now()->addDays(5);
    $endDate = now()->addDays(7);

    $isAvailable = $this->service->isAvailable($staff, $startDate, $endDate);

    expect($isAvailable)->toBeFalse();
});

test('staff is not available when has conflicting assignment', function () {
    $staff = Staff::factory()->create(['is_available' => true]);
    $event = Event::factory()->create([
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(7),
        'status' => 'confirmed',
    ]);
    $event->staff()->attach($staff->id);

    $isAvailable = $this->service->isAvailable($staff, now()->addDays(6), now()->addDays(8));

    expect($isAvailable)->toBeFalse();
});

test('can check availability by staff id', function () {
    $staff = Staff::factory()->create(['is_available' => true]);

    $isAvailable = $this->service->isAvailable($staff->id, now()->addDays(5), now()->addDays(7));

    expect($isAvailable)->toBeTrue();
});

test('detects conflicting assignments', function () {
    $staff = Staff::factory()->create();
    $event = Event::factory()->create([
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(7),
        'status' => 'confirmed',
    ]);
    $event->staff()->attach($staff->id);

    $hasConflict = $this->service->hasConflictingAssignments($staff, now()->addDays(6), now()->addDays(8));

    expect($hasConflict)->toBeTrue();
});

test('no conflict when events do not overlap', function () {
    $staff = Staff::factory()->create();
    $event = Event::factory()->create([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(3),
        'status' => 'confirmed',
    ]);
    $event->staff()->attach($staff->id);

    $hasConflict = $this->service->hasConflictingAssignments($staff, now()->addDays(5), now()->addDays(7));

    expect($hasConflict)->toBeFalse();
});

test('can exclude specific event when checking conflicts', function () {
    $staff = Staff::factory()->create();
    $event = Event::factory()->create([
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(7),
        'status' => 'confirmed',
    ]);
    $event->staff()->attach($staff->id);

    $hasConflict = $this->service->hasConflictingAssignments(
        $staff,
        now()->addDays(6),
        now()->addDays(8),
        $event->id
    );

    expect($hasConflict)->toBeFalse();
});

test('can get available staff for date range', function () {
    $availableStaff = Staff::factory()->count(3)->create(['is_available' => true]);
    $unavailableStaff = Staff::factory()->create(['is_available' => false]);
    $busyStaff = Staff::factory()->create(['is_available' => true]);

    $event = Event::factory()->create([
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(7),
        'status' => 'confirmed',
    ]);
    $event->staff()->attach($busyStaff->id);

    $available = $this->service->getAvailableStaff(now()->addDays(5), now()->addDays(7));

    expect($available)->toHaveCount(3);
});

test('can get available staff by specialization', function () {
    Staff::factory()->count(2)->create([
        'is_available' => true,
        'position' => 'Event Coordinator',
    ]);
    Staff::factory()->create([
        'is_available' => true,
        'position' => 'Manager',
    ]);

    $available = $this->service->getAvailableStaffBySpecialization(
        now()->addDays(5),
        now()->addDays(7),
        'Event Coordinator'
    );

    expect($available)->toHaveCount(2);
});

test('can get assigned events for staff', function () {
    $staff = Staff::factory()->create();
    $events = Event::factory()->count(3)->create([
        'start_date' => now()->addDays(5),
        'status' => 'confirmed',
    ]);

    foreach ($events as $event) {
        $event->staff()->attach($staff->id);
    }

    $assignedEvents = $this->service->getAssignedEvents($staff, now()->addDays(4), now()->addDays(10));

    expect($assignedEvents)->toHaveCount(3);
});

test('can get availability summary for staff', function () {
    $staff = Staff::factory()->create(['is_available' => true]);
    $event = Event::factory()->create([
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(7),
        'status' => 'confirmed',
    ]);
    $event->staff()->attach($staff->id);

    $summary = $this->service->getAvailabilitySummary($staff, now(), now()->addDays(10));

    expect($summary)->toBeArray()
        ->and($summary)->toHaveKeys(['is_available', 'total_assignments', 'has_conflicts'])
        ->and($summary['total_assignments'])->toBe(1);
});

test('can get staff availability for event', function () {
    Staff::factory()->count(5)->create(['is_available' => true]);
    $event = Event::factory()->create([
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(7),
    ]);

    $availability = $this->service->getStaffAvailabilityForEvent($event->id);

    expect($availability)->toHaveCount(5)
        ->and($availability->first())->toHaveKeys(['staff_id', 'staff_name', 'is_available', 'is_assigned']);
});

test('can suggest staff for event', function () {
    Staff::factory()->count(3)->create(['is_available' => true]);
    $event = Event::factory()->create([
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(7),
    ]);

    $suggestions = $this->service->suggestStaffForEvent($event->id);

    expect($suggestions)->toHaveCount(3);
});

test('can suggest staff by specialization for event', function () {
    Staff::factory()->count(2)->create([
        'is_available' => true,
        'position' => 'Coordinator',
    ]);
    Staff::factory()->create([
        'is_available' => true,
        'position' => 'Manager',
    ]);

    $event = Event::factory()->create([
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(7),
    ]);

    $suggestions = $this->service->suggestStaffForEvent($event->id, 'Coordinator');

    expect($suggestions)->toHaveCount(2);
});
