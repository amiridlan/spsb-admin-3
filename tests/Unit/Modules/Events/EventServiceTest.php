<?php

use App\Models\Event;
use App\Models\EventSpace;
use Modules\Events\Services\EventService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(EventService::class);
});

test('can get all events', function () {
    Event::factory()->count(5)->create();

    $events = $this->service->getAll();

    expect($events)->toHaveCount(5);
});

test('can get all events with status filter', function () {
    Event::factory()->count(3)->create(['status' => 'pending']);
    Event::factory()->count(2)->create(['status' => 'confirmed']);

    $events = $this->service->getAll(['status' => 'pending']);

    expect($events)->toHaveCount(3)
        ->and($events->every(fn($event) => $event->status === 'pending'))->toBeTrue();
});

test('can get all events excluding cancelled', function () {
    Event::factory()->count(3)->create(['status' => 'confirmed']);
    Event::factory()->count(2)->create(['status' => 'cancelled']);

    $events = $this->service->getAll(['exclude_cancelled' => true]);

    expect($events)->toHaveCount(3)
        ->and($events->every(fn($event) => $event->status !== 'cancelled'))->toBeTrue();
});

test('can get event by id', function () {
    $event = Event::factory()->create(['title' => 'Test Event']);

    $result = $this->service->getById($event->id);

    expect($result)->not->toBeNull()
        ->and($result->title)->toBe('Test Event')
        ->and($result->eventSpace)->not->toBeNull()
        ->and($result->creator)->not->toBeNull();
});

test('returns null for non-existent event id', function () {
    $result = $this->service->getById(99999);

    expect($result)->toBeNull();
});

test('can create event', function () {
    $space = EventSpace::factory()->create();
    $user = \App\Models\User::factory()->create();

    $data = [
        'event_space_id' => $space->id,
        'title' => 'New Event',
        'description' => 'Event description',
        'client_name' => 'John Doe',
        'client_email' => 'john@example.com',
        'start_date' => now(),
        'end_date' => now()->addDays(1),
        'status' => 'pending',
        'created_by' => $user->id,
    ];

    $event = $this->service->create($data);

    expect($event->title)->toBe('New Event')
        ->and($event->status)->toBe('pending')
        ->and($event->id)->toBeGreaterThan(0);

    $this->assertDatabaseHas('events', ['title' => 'New Event']);
});

test('can update event', function () {
    $event = Event::factory()->create(['title' => 'Original Title']);

    $updated = $this->service->update($event->id, ['title' => 'Updated Title']);

    expect($updated->title)->toBe('Updated Title');
    $this->assertDatabaseHas('events', ['title' => 'Updated Title']);
});

test('can delete event', function () {
    $event = Event::factory()->create();

    $result = $this->service->delete($event->id);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('events', ['id' => $event->id]);
});

test('can get events by status', function () {
    Event::factory()->count(3)->create(['status' => 'confirmed']);
    Event::factory()->count(2)->create(['status' => 'pending']);

    $events = $this->service->getByStatus('confirmed');

    expect($events)->toHaveCount(3);
});

test('can get events by date range', function () {
    Event::factory()->create(['start_date' => now()->subDays(10)]);
    Event::factory()->count(3)->create(['start_date' => now()]);
    Event::factory()->create(['start_date' => now()->addDays(10)]);

    $events = $this->service->getByDateRange(now()->subDay(), now()->addDay());

    expect($events)->toHaveCount(3);
});

test('can get upcoming events', function () {
    Event::factory()->create(['start_date' => now()->subDays(5)]);
    Event::factory()->count(5)->create(['start_date' => now()->addDays(5)]);

    $events = $this->service->getUpcoming(3);

    expect($events)->toHaveCount(3)
        ->and($events->every(fn($event) => $event->start_date >= now()))->toBeTrue();
});

test('can get events by space', function () {
    $space1 = EventSpace::factory()->create();
    $space2 = EventSpace::factory()->create();

    Event::factory()->count(3)->create(['event_space_id' => $space1->id]);
    Event::factory()->count(2)->create(['event_space_id' => $space2->id]);

    $events = $this->service->getBySpace($space1->id);

    expect($events)->toHaveCount(3);
});

test('can update event status', function () {
    $event = Event::factory()->create(['status' => 'pending']);

    $updated = $this->service->updateStatus($event->id, 'confirmed');

    expect($updated->status)->toBe('confirmed');
});

test('can get calendar events', function () {
    Event::factory()->count(3)->create();

    $calendarEvents = $this->service->getCalendarEvents();

    expect($calendarEvents)->toBeArray()
        ->and($calendarEvents)->toHaveCount(3)
        ->and($calendarEvents[0])->toHaveKeys(['id', 'title', 'start', 'end', 'backgroundColor', 'extendedProps']);
});

test('calendar events exclude cancelled by default', function () {
    Event::factory()->count(2)->create(['status' => 'confirmed']);
    Event::factory()->count(1)->create(['status' => 'cancelled']);

    $calendarEvents = $this->service->getCalendarEvents();

    expect($calendarEvents)->toHaveCount(2);
});

test('calendar events can include cancelled when specified', function () {
    Event::factory()->count(2)->create(['status' => 'confirmed']);
    Event::factory()->count(1)->create(['status' => 'cancelled']);

    $calendarEvents = $this->service->getCalendarEvents(['show_cancelled' => true]);

    expect($calendarEvents)->toHaveCount(3);
});

test('calendar events can filter by space', function () {
    $space = EventSpace::factory()->create();
    Event::factory()->count(2)->create(['event_space_id' => $space->id]);
    Event::factory()->count(1)->create();

    $calendarEvents = $this->service->getCalendarEvents(['event_space_id' => $space->id]);

    expect($calendarEvents)->toHaveCount(2);
});

test('can get recent bookings', function () {
    Event::factory()->count(5)->create();

    $recentBookings = $this->service->getRecentBookings(3);

    expect($recentBookings)->toHaveCount(3);
});
