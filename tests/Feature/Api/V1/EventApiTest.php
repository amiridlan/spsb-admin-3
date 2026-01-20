<?php

namespace Tests\Feature\Api\V1;

use App\Models\Event;
use App\Models\EventSpace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected EventSpace $space;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);
        $this->space = EventSpace::factory()->create(['is_active' => true]);
    }

    /** @test */
    public function it_can_list_events()
    {
        Event::factory()->count(3)->create([
            'event_space_id' => $this->space->id,
            'status' => 'confirmed',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/events');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'start_date',
                        'end_date',
                        'status',
                    ],
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_excludes_cancelled_events_from_list()
    {
        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'status' => 'confirmed',
            'created_by' => $this->user->id,
        ]);
        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'status' => 'cancelled',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/events');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_filter_events_by_space_id()
    {
        $space2 = EventSpace::factory()->create(['is_active' => true]);

        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'status' => 'confirmed',
            'created_by' => $this->user->id,
        ]);
        Event::factory()->create([
            'event_space_id' => $space2->id,
            'status' => 'confirmed',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/events?space_id={$this->space->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_filter_events_by_date_range()
    {
        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'start_date' => '2025-01-15',
            'end_date' => '2025-01-16',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
        ]);
        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'start_date' => '2025-03-15',
            'end_date' => '2025-03-16',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/events?start_date=2025-01-01&end_date=2025-01-31');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_get_single_event()
    {
        $event = Event::factory()->create([
            'event_space_id' => $this->space->id,
            'title' => 'Test Event',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'start_date',
                    'end_date',
                    'status',
                ],
            ])
            ->assertJsonPath('data.title', 'Test Event');
    }

    /** @test */
    public function it_returns_404_for_cancelled_event()
    {
        $event = Event::factory()->create([
            'event_space_id' => $this->space->id,
            'status' => 'cancelled',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/events/{$event->id}");

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Event not available');
    }

    /** @test */
    public function it_can_check_availability_for_open_dates()
    {
        $response = $this->postJson('/api/v1/events/check-availability', [
            'event_space_id' => $this->space->id,
            'start_date' => '2025-06-15',
            'end_date' => '2025-06-16',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.available', true)
            ->assertJsonPath('data.message', 'Space is available');
    }

    /** @test */
    public function it_detects_conflicts_when_checking_availability()
    {
        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'start_date' => '2025-06-15',
            'end_date' => '2025-06-20',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson('/api/v1/events/check-availability', [
            'event_space_id' => $this->space->id,
            'start_date' => '2025-06-18',
            'end_date' => '2025-06-22',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.available', false)
            ->assertJsonPath('data.message', 'Space is not available for the selected dates');
    }

    /** @test */
    public function check_availability_requires_valid_params()
    {
        $response = $this->postJson('/api/v1/events/check-availability', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['event_space_id', 'start_date', 'end_date']);
    }

    /** @test */
    public function check_availability_validates_end_date_after_start()
    {
        $response = $this->postJson('/api/v1/events/check-availability', [
            'event_space_id' => $this->space->id,
            'start_date' => '2025-06-20',
            'end_date' => '2025-06-15',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }
}
