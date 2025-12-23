<?php

namespace Tests\Feature\Api\V1;

use App\Models\Event;
use App\Models\EventSpace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected EventSpace $space;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);
        $this->space = EventSpace::factory()->create([
            'name' => 'Test Hall',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_get_calendar_events()
    {
        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'title' => 'Test Event',
            'start_date' => '2025-01-15',
            'end_date' => '2025-01-16',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/events/calendar');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'start',
                        'end',
                        'allDay',
                        'backgroundColor',
                        'borderColor',
                        'textColor',
                        'extendedProps' => [
                            'status',
                            'space',
                            'space_id',
                            'client',
                            'description',
                            'start_date',
                            'end_date',
                            'duration_days',
                        ],
                    ],
                ],
            ])
            ->assertJsonPath('data.0.title', 'Test Event')
            ->assertJsonPath('data.0.start', '2025-01-15')
            ->assertJsonPath('data.0.end', '2025-01-17') // Exclusive end date
            ->assertJsonPath('data.0.extendedProps.duration_days', 2);
    }

    /** @test */
    public function it_can_filter_calendar_events_by_date_range()
    {
        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'start_date' => '2025-01-15',
            'end_date' => '2025-01-16',
            'created_by' => $this->user->id,
        ]);

        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'start_date' => '2025-02-15',
            'end_date' => '2025-02-16',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/events/calendar?start=2025-01-01&end=2025-01-31');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_filter_calendar_events_by_space()
    {
        $space2 = EventSpace::factory()->create(['is_active' => true]);

        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'created_by' => $this->user->id,
        ]);

        Event::factory()->create([
            'event_space_id' => $space2->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/events/calendar?space_id={$this->space->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.extendedProps.space_id', $this->space->id);
    }

    /** @test */
    public function it_can_filter_calendar_events_by_status()
    {
        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'status' => 'confirmed',
            'created_by' => $this->user->id,
        ]);

        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/events/calendar?status=confirmed');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.extendedProps.status', 'confirmed');
    }

    /** @test */
    public function it_excludes_cancelled_events_by_default()
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

        $response = $this->getJson('/api/v1/events/calendar');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_include_cancelled_events()
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

        $response = $this->getJson('/api/v1/events/calendar?include_cancelled=1');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_can_get_calendar_events_by_month()
    {
        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'title' => 'January Event',
            'start_date' => '2025-01-15',
            'end_date' => '2025-01-16',
            'created_by' => $this->user->id,
        ]);

        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'title' => 'February Event',
            'start_date' => '2025-02-15',
            'end_date' => '2025-02-16',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/events/calendar/month?year=2025&month=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'year',
                    'month',
                    'month_name',
                    'start_date',
                    'end_date',
                    'total_events',
                    'events',
                ],
            ])
            ->assertJsonPath('data.year', 2025)
            ->assertJsonPath('data.month', 1)
            ->assertJsonPath('data.month_name', 'January')
            ->assertJsonPath('data.total_events', 1)
            ->assertJsonCount(1, 'data.events')
            ->assertJsonPath('data.events.0.title', 'January Event');
    }

    /** @test */
    public function it_requires_year_and_month_for_monthly_view()
    {
        $response = $this->getJson('/api/v1/events/calendar/month');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['year', 'month']);
    }

    /** @test */
    public function it_validates_year_range()
    {
        $response = $this->getJson('/api/v1/events/calendar/month?year=1999&month=1');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['year']);
    }

    /** @test */
    public function it_validates_month_range()
    {
        $response = $this->getJson('/api/v1/events/calendar/month?year=2025&month=13');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['month']);
    }

    /** @test */
    public function it_formats_colors_correctly_based_on_status()
    {
        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'status' => 'confirmed',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/events/calendar');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.backgroundColor', '#10b981')
            ->assertJsonPath('data.0.borderColor', '#059669');
    }

    /** @test */
    public function it_handles_multi_day_events_correctly()
    {
        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'start_date' => '2025-01-15',
            'end_date' => '2025-01-20', // 6 day event
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/events/calendar');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.start', '2025-01-15')
            ->assertJsonPath('data.0.end', '2025-01-21') // Exclusive end
            ->assertJsonPath('data.0.extendedProps.duration_days', 6);
    }
}
