<?php

namespace Tests\Feature\Api\V1;

use App\Models\EventSpace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventSpaceApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_active_event_spaces()
    {
        EventSpace::factory()->count(3)->create(['is_active' => true]);
        EventSpace::factory()->count(2)->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/event-spaces');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'location',
                        'capacity',
                        'description',
                    ],
                ],
            ])
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function it_returns_empty_array_when_no_active_spaces()
    {
        EventSpace::factory()->count(2)->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/event-spaces');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    /** @test */
    public function it_can_get_single_active_event_space()
    {
        $space = EventSpace::factory()->create([
            'name' => 'Grand Ballroom',
            'capacity' => 500,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/v1/event-spaces/{$space->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'location',
                    'capacity',
                    'description',
                ],
            ])
            ->assertJsonPath('data.name', 'Grand Ballroom')
            ->assertJsonPath('data.capacity', 500);
    }

    /** @test */
    public function it_returns_404_for_inactive_event_space()
    {
        $space = EventSpace::factory()->create(['is_active' => false]);

        $response = $this->getJson("/api/v1/event-spaces/{$space->id}");

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Event space not available');
    }

    /** @test */
    public function it_returns_404_for_non_existent_event_space()
    {
        $response = $this->getJson('/api/v1/event-spaces/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function event_spaces_are_ordered_by_name()
    {
        EventSpace::factory()->create(['name' => 'Zebra Room', 'is_active' => true]);
        EventSpace::factory()->create(['name' => 'Alpha Room', 'is_active' => true]);
        EventSpace::factory()->create(['name' => 'Beta Room', 'is_active' => true]);

        $response = $this->getJson('/api/v1/event-spaces');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.name', 'Alpha Room')
            ->assertJsonPath('data.1.name', 'Beta Room')
            ->assertJsonPath('data.2.name', 'Zebra Room');
    }
}
