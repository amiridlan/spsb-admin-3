<?php

use App\Models\User;
use App\Models\Staff;
use App\Models\Event;
use App\Models\EventSpace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can access calendar', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/calendar');

    $response->assertOk();
});

test('calendar shows events from service', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Event::factory()->count(5)->create(['status' => 'confirmed']);

    $response = $this->actingAs($admin)->get('/calendar');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->component('Calendar')
            ->has('events', 5)
        );
});

test('calendar excludes cancelled events by default', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Event::factory()->count(3)->create(['status' => 'confirmed']);
    Event::factory()->count(2)->create(['status' => 'cancelled']);

    $response = $this->actingAs($admin)->get('/calendar');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->has('events', 3)
        );
});

test('calendar can include cancelled events when requested', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Event::factory()->count(3)->create(['status' => 'confirmed']);
    Event::factory()->count(2)->create(['status' => 'cancelled']);

    $response = $this->actingAs($admin)->get('/calendar?show_cancelled=1');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->has('events', 5)
        );
});

test('calendar can filter by space', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $space1 = EventSpace::factory()->create();
    $space2 = EventSpace::factory()->create();

    Event::factory()->count(3)->create(['event_space_id' => $space1->id]);
    Event::factory()->count(2)->create(['event_space_id' => $space2->id]);

    $response = $this->actingAs($admin)->get("/calendar?space={$space1->id}");

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->has('events', 3)
        );
});

test('calendar can filter by status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Event::factory()->count(3)->create(['status' => 'confirmed']);
    Event::factory()->count(2)->create(['status' => 'pending']);

    $response = $this->actingAs($admin)->get('/calendar?status=confirmed');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->has('events', 3)
        );
});

test('staff calendar shows only their assigned events', function () {
    $user = User::factory()->create(['role' => 'staff']);
    $staff = Staff::factory()->create(['user_id' => $user->id]);

    $assignedEvents = Event::factory()->count(3)->create();
    foreach ($assignedEvents as $event) {
        $event->staff()->attach($staff->id);
    }

    Event::factory()->count(2)->create(); // Other events not assigned to this staff

    $response = $this->actingAs($user)->get('/calendar');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->has('events', 3)
        );
});

test('calendar shows active spaces from service', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    EventSpace::factory()->count(3)->create(['is_active' => true]);
    EventSpace::factory()->count(2)->create(['is_active' => false]);

    $response = $this->actingAs($admin)->get('/calendar');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->has('spaces', 3)
        );
});

test('calendar events have correct format', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Event::factory()->create([
        'title' => 'Test Event',
        'status' => 'confirmed',
        'start_date' => now(),
        'end_date' => now()->addDays(2),
    ]);

    $response = $this->actingAs($admin)->get('/calendar');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->has('events.0', fn($event) => $event
                ->has('id')
                ->has('title')
                ->has('start')
                ->has('end')
                ->has('backgroundColor')
                ->has('extendedProps')
            )
        );
});

test('unauthorized user cannot access calendar', function () {
    $response = $this->get('/calendar');

    $response->assertRedirect('/login');
});
