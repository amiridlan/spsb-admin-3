<?php

use App\Models\Event;
use App\Models\EventSpace;
use Modules\Events\Services\EventSpaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(EventSpaceService::class);
});

test('can get all event spaces', function () {
    EventSpace::factory()->count(5)->create(['is_active' => true]);

    $spaces = $this->service->getAll();

    expect($spaces)->toHaveCount(5);
});

test('can get all spaces including inactive', function () {
    EventSpace::factory()->count(3)->create(['is_active' => true]);
    EventSpace::factory()->count(2)->create(['is_active' => false]);

    $spaces = $this->service->getAll(activeOnly: false);

    expect($spaces)->toHaveCount(5);
});

test('can get space by id', function () {
    $space = EventSpace::factory()->create(['name' => 'Conference Room']);

    $result = $this->service->getById($space->id);

    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('Conference Room');
});

test('returns null for non-existent space id', function () {
    $result = $this->service->getById(99999);

    expect($result)->toBeNull();
});

test('can get active spaces only', function () {
    EventSpace::factory()->count(3)->create(['is_active' => true]);
    EventSpace::factory()->count(2)->create(['is_active' => false]);

    $spaces = $this->service->getActive();

    expect($spaces)->toHaveCount(3)
        ->and($spaces->every(fn($space) => $space->is_active))->toBeTrue();
});

test('can create event space', function () {
    $data = [
        'name' => 'New Conference Room',
        'location' => 'Building A, Floor 2',
        'capacity' => 50,
        'is_active' => true,
    ];

    $space = $this->service->create($data);

    expect($space->name)->toBe('New Conference Room')
        ->and($space->capacity)->toBe(50)
        ->and($space->id)->toBeGreaterThan(0);

    $this->assertDatabaseHas('event_spaces', ['name' => 'New Conference Room']);
});

test('can update event space', function () {
    $space = EventSpace::factory()->create(['name' => 'Old Name']);

    $updated = $this->service->update($space->id, ['name' => 'New Name']);

    expect($updated->name)->toBe('New Name');
    $this->assertDatabaseHas('event_spaces', ['name' => 'New Name']);
});

test('cannot delete space with future events', function () {
    $space = EventSpace::factory()->create();
    Event::factory()->create([
        'event_space_id' => $space->id,
        'start_date' => now()->addDays(5),
        'status' => 'confirmed',
    ]);

    expect(fn() => $this->service->delete($space->id))
        ->toThrow(Exception::class, 'Cannot delete event space with future events');
});

test('can delete space without future events', function () {
    $space = EventSpace::factory()->create();
    Event::factory()->create([
        'event_space_id' => $space->id,
        'start_date' => now()->subDays(10),
        'status' => 'completed',
    ]);

    $result = $this->service->delete($space->id);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('event_spaces', ['id' => $space->id]);
});

test('can toggle active status', function () {
    $space = EventSpace::factory()->create(['is_active' => true]);

    $toggled = $this->service->toggleActive($space->id);

    expect($toggled->is_active)->toBeFalse();

    $toggled = $this->service->toggleActive($space->id);

    expect($toggled->is_active)->toBeTrue();
});

test('can get spaces with event counts', function () {
    $space1 = EventSpace::factory()->create();
    $space2 = EventSpace::factory()->create();

    Event::factory()->count(3)->create(['event_space_id' => $space1->id]);
    Event::factory()->count(1)->create(['event_space_id' => $space2->id]);

    $spaces = $this->service->getWithEventCounts();

    expect($spaces)->toHaveCount(2)
        ->and($spaces->first()->events_count)->toBeGreaterThan(0);
});

test('can get spaces with event counts for date range', function () {
    $space = EventSpace::factory()->create();

    Event::factory()->create([
        'event_space_id' => $space->id,
        'start_date' => now()->subMonths(2),
    ]);
    Event::factory()->count(2)->create([
        'event_space_id' => $space->id,
        'start_date' => now(),
    ]);

    $spaces = $this->service->getWithEventCounts(now()->subDay(), now()->addDay());

    expect($spaces->first()->events_count)->toBe(2);
});

test('can get space utilization', function () {
    $space1 = EventSpace::factory()->create();
    $space2 = EventSpace::factory()->create();
    $space3 = EventSpace::factory()->create();

    Event::factory()->count(5)->create(['event_space_id' => $space1->id, 'status' => 'confirmed']);
    Event::factory()->count(3)->create(['event_space_id' => $space2->id, 'status' => 'confirmed']);
    Event::factory()->count(1)->create(['event_space_id' => $space3->id, 'status' => 'confirmed']);

    $utilization = $this->service->getSpaceUtilization(limit: 2);

    expect($utilization)->toHaveCount(2)
        ->and($utilization->first()->events_count)->toBe(5);
});

test('can get space metrics for date range', function () {
    $space = EventSpace::factory()->create();
    $start = now()->startOfMonth();
    $end = now()->endOfMonth();

    Event::factory()->count(3)->create([
        'event_space_id' => $space->id,
        'start_date' => now(),
        'end_date' => now()->addDays(2),
        'status' => 'confirmed',
    ]);

    $metrics = $this->service->getSpaceMetrics(['start' => $start, 'end' => $end]);

    expect($metrics)->toBeArray()
        ->and($metrics[0])->toHaveKeys(['id', 'name', 'booking_count', 'total_days', 'utilization_rate'])
        ->and($metrics[0]['booking_count'])->toBe(3);
});

test('can generate spaces report', function () {
    $space = EventSpace::factory()->create();

    Event::factory()->count(2)->create([
        'event_space_id' => $space->id,
        'start_date' => now(),
        'status' => 'confirmed',
    ]);

    $filters = [
        'start_date' => now()->subDay()->format('Y-m-d'),
        'end_date' => now()->addDay()->format('Y-m-d'),
    ];

    $report = $this->service->getSpacesReport($filters);

    expect($report)->toBeArray()
        ->and($report)->toHaveKeys(['type', 'title', 'period', 'total_count', 'data', 'summary'])
        ->and($report['type'])->toBe('spaces')
        ->and($report['data'])->toBeArray()
        ->and($report['summary'])->toHaveKeys(['total_spaces', 'total_bookings']);
});
