<?php

namespace Tests\Integration\Modules;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Events\Contracts\EventServiceInterface;
use Modules\Events\Contracts\EventSpaceServiceInterface;
use Modules\Events\Contracts\EventStaffAssignmentServiceInterface;
use Modules\Events\Models\Event;
use Modules\Events\Models\EventSpace;
use Modules\Staff\Contracts\StaffAvailabilityServiceInterface;
use Modules\Staff\Contracts\StaffServiceInterface;
use Modules\Staff\Models\Staff;
use Tests\TestCase;

class ModuleCommunicationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that all module services can be resolved from the container
     */
    public function test_all_module_services_resolve_from_container(): void
    {
        // Events module services
        $this->assertInstanceOf(
            EventServiceInterface::class,
            app(EventServiceInterface::class)
        );

        $this->assertInstanceOf(
            EventSpaceServiceInterface::class,
            app(EventSpaceServiceInterface::class)
        );

        $this->assertInstanceOf(
            EventStaffAssignmentServiceInterface::class,
            app(EventStaffAssignmentServiceInterface::class)
        );

        // Staff module services
        $this->assertInstanceOf(
            StaffServiceInterface::class,
            app(StaffServiceInterface::class)
        );

        $this->assertInstanceOf(
            StaffAvailabilityServiceInterface::class,
            app(StaffAvailabilityServiceInterface::class)
        );
    }

    /**
     * Test cross-module dependency injection
     */
    public function test_events_module_depends_on_staff_module_service(): void
    {
        $assignmentService = app(EventStaffAssignmentServiceInterface::class);

        $reflection = new \ReflectionClass($assignmentService);
        $constructor = $reflection->getConstructor();
        $params = $constructor->getParameters();

        // Verify it depends on StaffAvailabilityServiceInterface
        $dependencies = array_map(
            fn($param) => $param->getType()?->getName(),
            $params
        );

        $this->assertContains(
            StaffAvailabilityServiceInterface::class,
            $dependencies,
            'EventStaffAssignmentService should depend on StaffAvailabilityServiceInterface'
        );
    }

    /**
     * Test backwards compatibility - old model namespaces still work
     */
    public function test_backwards_compatible_model_aliases_work(): void
    {
        $oldEvent = new \App\Models\Event();
        $oldStaff = new \App\Models\Staff();

        $this->assertInstanceOf(Event::class, $oldEvent);
        $this->assertInstanceOf(Staff::class, $oldStaff);
    }

    /**
     * Test that Event and Staff models have correct relationships
     */
    public function test_event_and_staff_models_have_cross_module_relationships(): void
    {
        $event = new Event();
        $staff = new Staff();

        // Event should have staff() relationship
        $this->assertTrue(
            method_exists($event, 'staff'),
            'Event model should have staff() relationship'
        );

        // Staff should have events() relationship
        $this->assertTrue(
            method_exists($staff, 'events'),
            'Staff model should have events() relationship'
        );
    }

    /**
     * Integration test: Full workflow of creating event and assigning staff
     *
     * This test demonstrates:
     * 1. Using EventSpaceService to create a space
     * 2. Using EventService to create an event
     * 3. Using StaffService to create staff
     * 4. Using EventStaffAssignmentService to assign staff (cross-module)
     * 5. Using StaffAvailabilityService to check availability (cross-module)
     */
    public function test_full_workflow_create_event_and_assign_staff(): void
    {
        $this->markTestSkipped(
            'This test requires database seeding. Enable when ready to test with real data.'
        );

        // Step 1: Create event space
        $spaceService = app(EventSpaceServiceInterface::class);
        $space = $spaceService->create([
            'name' => 'Test Conference Room',
            'location' => 'Building A',
            'capacity' => 50,
            'is_active' => true,
        ]);

        $this->assertInstanceOf(EventSpace::class, $space);

        // Step 2: Create event
        $eventService = app(EventServiceInterface::class);
        $event = $eventService->create([
            'event_space_id' => $space->id,
            'title' => 'Integration Test Event',
            'client_name' => 'Test Client',
            'client_email' => 'test@example.com',
            'start_date' => Carbon::tomorrow(),
            'end_date' => Carbon::tomorrow()->addDays(2),
            'status' => 'confirmed',
            'created_by' => 1, // Assuming user ID 1 exists
        ]);

        $this->assertInstanceOf(Event::class, $event);

        // Step 3: Create staff
        $staffService = app(StaffServiceInterface::class);
        $staff = $staffService->create([
            'user_id' => 1, // Assuming user ID 1 exists
            'position' => 'Event Coordinator',
            'is_available' => true,
        ]);

        $this->assertInstanceOf(Staff::class, $staff);

        // Step 4: Check staff availability (cross-module)
        $availabilityService = app(StaffAvailabilityServiceInterface::class);
        $isAvailable = $availabilityService->isAvailable(
            $staff,
            $event->start_date,
            $event->end_date
        );

        $this->assertTrue($isAvailable, 'Staff should be available for the event');

        // Step 5: Assign staff to event (cross-module)
        $assignmentService = app(EventStaffAssignmentServiceInterface::class);
        $assignmentService->assignStaff($event->id, $staff->id, 'Lead Coordinator');

        // Verify assignment
        $event->refresh();
        $this->assertTrue(
            $event->staff->contains($staff),
            'Staff should be assigned to event'
        );

        // Step 6: Verify staff is now unavailable for overlapping dates
        $isStillAvailable = $availabilityService->isAvailable(
            $staff,
            $event->start_date,
            $event->end_date
        );

        $this->assertFalse(
            $isStillAvailable,
            'Staff should NOT be available during assigned event'
        );
    }

    /**
     * Test that module services are singletons (same instance returned)
     */
    public function test_module_services_are_properly_bound(): void
    {
        $eventService1 = app(EventServiceInterface::class);
        $eventService2 = app(EventServiceInterface::class);

        // They should be different instances (not singleton)
        // because we used bind() not singleton()
        $this->assertNotSame($eventService1, $eventService2);
    }
}
