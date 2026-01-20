<?php

namespace Tests\Feature\Api\V1;

use App\Models\Event;
use App\Models\EventSpace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected EventSpace $space;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);
        $this->space = EventSpace::factory()->create(['is_active' => true]);
        $this->token = $this->user->createToken('api-token')->plainTextToken;
    }

    /** @test */
    public function authenticated_user_can_create_booking()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/bookings', [
            'event_space_id' => $this->space->id,
            'title' => 'Corporate Meeting',
            'description' => 'Annual company meeting',
            'client_name' => 'John Doe',
            'client_email' => 'john@example.com',
            'client_phone' => '+1234567890',
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'end_date' => now()->addDays(11)->format('Y-m-d'),
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'status',
                    'start_date',
                    'end_date',
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Booking request created successfully')
            ->assertJsonPath('data.title', 'Corporate Meeting')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('events', [
            'title' => 'Corporate Meeting',
            'client_name' => 'John Doe',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_booking()
    {
        $response = $this->postJson('/api/v1/bookings', [
            'event_space_id' => $this->space->id,
            'title' => 'Corporate Meeting',
            'client_name' => 'John Doe',
            'client_email' => 'john@example.com',
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'end_date' => now()->addDays(11)->format('Y-m-d'),
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function booking_requires_valid_fields()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/bookings', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'event_space_id',
                'title',
                'client_name',
                'client_email',
                'start_date',
                'end_date',
            ]);
    }

    /** @test */
    public function booking_validates_email_format()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/bookings', [
            'event_space_id' => $this->space->id,
            'title' => 'Meeting',
            'client_name' => 'John',
            'client_email' => 'not-an-email',
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'end_date' => now()->addDays(11)->format('Y-m-d'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client_email']);
    }

    /** @test */
    public function booking_validates_start_date_not_in_past()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/bookings', [
            'event_space_id' => $this->space->id,
            'title' => 'Meeting',
            'client_name' => 'John',
            'client_email' => 'john@example.com',
            'start_date' => now()->subDays(5)->format('Y-m-d'),
            'end_date' => now()->subDays(4)->format('Y-m-d'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    /** @test */
    public function booking_validates_end_date_after_start_date()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/bookings', [
            'event_space_id' => $this->space->id,
            'title' => 'Meeting',
            'client_name' => 'John',
            'client_email' => 'john@example.com',
            'start_date' => now()->addDays(15)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    /** @test */
    public function booking_fails_when_space_has_conflict()
    {
        // Create existing booking
        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'end_date' => now()->addDays(15)->format('Y-m-d'),
            'status' => 'confirmed',
            'created_by' => $this->user->id,
        ]);

        // Try to create overlapping booking
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/bookings', [
            'event_space_id' => $this->space->id,
            'title' => 'Conflicting Meeting',
            'client_name' => 'Jane Doe',
            'client_email' => 'jane@example.com',
            'start_date' => now()->addDays(12)->format('Y-m-d'),
            'end_date' => now()->addDays(14)->format('Y-m-d'),
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Space is not available for the selected dates');
    }

    /** @test */
    public function booking_succeeds_when_existing_event_is_cancelled()
    {
        // Create cancelled booking
        Event::factory()->create([
            'event_space_id' => $this->space->id,
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'end_date' => now()->addDays(15)->format('Y-m-d'),
            'status' => 'cancelled',
            'created_by' => $this->user->id,
        ]);

        // Try to create booking on same dates
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/bookings', [
            'event_space_id' => $this->space->id,
            'title' => 'New Booking',
            'client_name' => 'Jane Doe',
            'client_email' => 'jane@example.com',
            'start_date' => now()->addDays(12)->format('Y-m-d'),
            'end_date' => now()->addDays(14)->format('Y-m-d'),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function booking_validates_event_space_exists()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/bookings', [
            'event_space_id' => 99999,
            'title' => 'Meeting',
            'client_name' => 'John',
            'client_email' => 'john@example.com',
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'end_date' => now()->addDays(11)->format('Y-m-d'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['event_space_id']);
    }

    /** @test */
    public function full_booking_workflow_integration()
    {
        // Step 1: User logs in
        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('data.token');

        // Step 2: List available spaces
        $spacesResponse = $this->getJson('/api/v1/event-spaces');
        $spacesResponse->assertStatus(200);
        $spaceId = $spacesResponse->json('data.0.id');

        // Step 3: Check availability
        $availabilityResponse = $this->postJson('/api/v1/events/check-availability', [
            'event_space_id' => $spaceId,
            'start_date' => now()->addDays(20)->format('Y-m-d'),
            'end_date' => now()->addDays(21)->format('Y-m-d'),
        ]);

        $availabilityResponse->assertStatus(200)
            ->assertJsonPath('data.available', true);

        // Step 4: Create booking
        $bookingResponse = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/v1/bookings', [
            'event_space_id' => $spaceId,
            'title' => 'Integration Test Event',
            'description' => 'Full workflow test',
            'client_name' => 'Test Client',
            'client_email' => 'test@example.com',
            'start_date' => now()->addDays(20)->format('Y-m-d'),
            'end_date' => now()->addDays(21)->format('Y-m-d'),
        ]);

        $bookingResponse->assertStatus(201);
        $eventId = $bookingResponse->json('data.id');

        // Step 5: Verify booking appears in events list
        $eventsResponse = $this->getJson('/api/v1/events');
        $eventsResponse->assertStatus(200);

        $eventIds = collect($eventsResponse->json('data'))->pluck('id')->toArray();
        $this->assertContains($eventId, $eventIds);

        // Step 6: Verify calendar shows the event
        $calendarResponse = $this->getJson('/api/v1/events/calendar');
        $calendarResponse->assertStatus(200);

        $calendarEventIds = collect($calendarResponse->json('data'))->pluck('id')->toArray();
        $this->assertContains($eventId, $calendarEventIds);

        // Step 7: Logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/v1/logout');

        $logoutResponse->assertStatus(200);
    }
}
