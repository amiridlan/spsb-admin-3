<?php

use App\Models\User;
use App\Models\Staff;
use App\Models\Event;
use App\Models\EventSpace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can access dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/');

    $response->assertOk();
});

test('admin dashboard shows stats from services', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Event::factory()->count(5)->create();
    EventSpace::factory()->count(3)->create(['is_active' => true]);
    Staff::factory()->count(4)->create();

    $response = $this->actingAs($admin)->get('/');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->component('Dashboard')
            ->has('stats')
            ->where('stats.total_events', 5)
            ->where('stats.total_spaces', 3)
            ->where('stats.total_staff', 4)
        );
});

test('admin dashboard shows upcoming events from service', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Event::factory()->count(5)->create(['start_date' => now()->addDays(5)]);

    $response = $this->actingAs($admin)->get('/');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->has('upcomingEvents', 5)
        );
});

test('admin dashboard shows space utilization from service', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $space = EventSpace::factory()->create(['is_active' => true]);
    Event::factory()->count(3)->create([
        'event_space_id' => $space->id,
        'status' => 'confirmed',
    ]);

    $response = $this->actingAs($admin)->get('/');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->has('spaceUtilization')
        );
});

test('staff can access dashboard', function () {
    $user = User::factory()->create(['role' => 'staff']);
    $staff = Staff::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get('/');

    $response->assertOk();
});

test('staff dashboard shows their stats from service', function () {
    $user = User::factory()->create(['role' => 'staff']);
    $staff = Staff::factory()->create(['user_id' => $user->id]);

    $events = Event::factory()->count(3)->create(['start_date' => now()->addDays(5)]);
    foreach ($events as $event) {
        $event->staff()->attach($staff->id);
    }

    $response = $this->actingAs($user)->get('/');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->component('Dashboard')
            ->where('role', 'staff')
            ->has('stats')
            ->where('stats.total_assignments', 3)
        );
});

test('staff without profile sees no profile message', function () {
    $user = User::factory()->create(['role' => 'staff']);

    $response = $this->actingAs($user)->get('/');

    $response->assertOk()
        ->assertInertia(fn($page) => $page
            ->where('role', 'staff')
            ->where('noProfile', true)
        );
});

test('unauthorized user cannot access dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
