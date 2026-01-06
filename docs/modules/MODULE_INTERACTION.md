# Module Interaction Patterns

## Overview

This document describes how the Events and Staff modules communicate with each other while maintaining clean separation of concerns and adherence to SOLID principles.

## Communication Architecture

### Principle: Dependency Inversion

Modules depend on **interfaces**, not concrete implementations. This allows modules to remain loosely coupled while still being able to collaborate.

```
Events Module                        Staff Module
     ↓                                    ↓
EventStaffAssignmentService ----→ StaffAvailabilityServiceInterface
     (concrete)                        (contract/interface)
                                           ↑
                                  StaffAvailabilityService
                                      (implementation)
```

## Cross-Module Dependencies

### Events Module → Staff Module

**Location:** `Modules/Events/Services/EventStaffAssignmentService.php`

```php
<?php

namespace Modules\Events\Services;

use Modules\Staff\Contracts\StaffAvailabilityServiceInterface; // ← Interface dependency

class EventStaffAssignmentService implements EventStaffAssignmentServiceInterface
{
    public function __construct(
        private StaffAvailabilityServiceInterface $staffAvailability
    ) {}

    public function assignStaff(int $eventId, int $staffId, ...): void
    {
        $event = Event::findOrFail($eventId);

        // Use Staff module's service to check availability
        if (!$this->staffAvailability->isAvailable($staffId, $event->start_date, $event->end_date)) {
            throw new \Exception('Staff member is not available');
        }

        // Proceed with assignment...
    }
}
```

**Key Points:**
- Events module depends on `StaffAvailabilityServiceInterface`, not the concrete service
- Laravel's service container automatically injects the correct implementation
- This allows swapping implementations without changing the Events module

### Staff Module → Events Module

The Staff module accesses Events through **Eloquent relationships**, not services.

**Location:** `Modules/Staff/Models/Staff.php`

```php
public function events(): BelongsToMany
{
    return $this->belongsToMany(Event::class, 'event_staff')
        ->withPivot('role', 'notes')
        ->withTimestamps();
}
```

**Location:** `Modules/Staff/Services/StaffAvailabilityService.php`

```php
use Modules\Events\Models\Event; // ← Direct model usage for queries

public function getStaffAvailabilityForEvent(int $eventId): Collection
{
    $event = Event::findOrFail($eventId);

    // Use event data to check staff availability...
}
```

**Why model usage is acceptable here:**
- Read-only queries for availability checking
- No business logic modification across modules
- Models are part of the shared data layer

## Shared Database Resources

### Event-Staff Pivot Table

**Table:** `event_staff`

**Columns:**
- `event_id` (foreign key to events)
- `staff_id` (foreign key to staff)
- `role` (nullable string)
- `notes` (nullable text)
- `created_at`, `updated_at`

**Accessed by:**
- Events module: Through `Event::staff()` relationship
- Staff module: Through `Staff::events()` relationship

**Business Logic Ownership:**
- Assignment/removal logic: **Events module** (`EventStaffAssignmentService`)
- Availability checking: **Staff module** (`StaffAvailabilityService`)

## Interaction Patterns

### Pattern 1: Service-to-Service (via Interface)

**Use when:** One module needs to execute business logic from another module

**Example:** Events module checking staff availability

```php
// In Events module
public function __construct(
    private StaffAvailabilityServiceInterface $staffAvailability
) {}

// Later...
$isAvailable = $this->staffAvailability->isAvailable($staffId, $start, $end);
```

**Benefits:**
- Loose coupling
- Easy to test (mock the interface)
- Clear contract between modules

### Pattern 2: Model-to-Model (via Eloquent)

**Use when:** Reading related data across modules

**Example:** Staff module querying events for a staff member

```php
// In Staff module
$staff = Staff::with('events')->find($staffId);
$upcomingEvents = $staff->events()
    ->where('start_date', '>=', Carbon::today())
    ->get();
```

**Benefits:**
- Leverages Laravel's ORM
- Efficient queries with eager loading
- Natural for read operations

### Pattern 3: Backwards-Compatible Aliases

**Use when:** Migrating existing code gradually

**Example:** Old code using `App\Models\Event`

```php
// Old code still works
use App\Models\Event; // ← Alias

$event = new Event();
// Actually creates Modules\Events\Models\Event instance
```

**Implementation:**
```php
// App/Models/Event.php
namespace App\Models;

use Modules\Events\Models\Event as ModuleEvent;

class Event extends ModuleEvent
{
    // All functionality inherited
}
```

## Service Dependency Map

```
┌─────────────────────────────────────────────────────────────┐
│                     Events Module                            │
├─────────────────────────────────────────────────────────────┤
│ EventService                                                 │
│ EventSpaceService                                            │
│ EventAnalyticsService                                        │
│ EventStaffAssignmentService ──────┐                         │
│   ↑                                │                         │
│   └─ Depends on ───────────────────┼─────────────────┐      │
└────────────────────────────────────┼─────────────────┼──────┘
                                     │                 │
                                     ↓                 ↓
┌─────────────────────────────────────────────────────────────┐
│                     Staff Module                             │
├─────────────────────────────────────────────────────────────┤
│ StaffService                                                 │
│ StaffAvailabilityServiceInterface ← (interface contract)    │
│   └── StaffAvailabilityService ← (implementation)           │
│ StaffAnalyticsService                                        │
└─────────────────────────────────────────────────────────────┘
```

## Testing Module Interactions

### Unit Testing (Mocked Dependencies)

```php
public function test_assigns_staff_when_available()
{
    // Mock the Staff module service
    $staffAvailability = Mockery::mock(StaffAvailabilityServiceInterface::class);
    $staffAvailability->shouldReceive('isAvailable')
        ->once()
        ->andReturn(true);

    // Create service with mocked dependency
    $assignmentService = new EventStaffAssignmentService($staffAvailability);

    // Test assignment logic...
}
```

### Integration Testing (Real Dependencies)

```php
public function test_full_workflow_with_real_services()
{
    // Use real services from container
    $assignmentService = app(EventStaffAssignmentServiceInterface::class);
    $availabilityService = app(StaffAvailabilityServiceInterface::class);

    // Create test data
    $event = Event::factory()->create();
    $staff = Staff::factory()->create(['is_available' => true]);

    // Test real interaction
    $isAvailable = $availabilityService->isAvailable($staff, $event->start_date, $event->end_date);
    $this->assertTrue($isAvailable);

    $assignmentService->assignStaff($event->id, $staff->id);

    // Verify
    $this->assertTrue($event->fresh()->staff->contains($staff));
}
```

## Common Workflows

### Workflow 1: Assign Staff to Event

1. **User action:** Admin clicks "Assign Staff" on event page
2. **Controller:** `EventStaffController::store()`
3. **Service call:** `EventStaffAssignmentService::assignStaff()`
4. **Cross-module check:** `StaffAvailabilityService::isAvailable()` ← Staff module
5. **If available:** Attach staff to event via pivot table
6. **Response:** Redirect with success message

### Workflow 2: Check Staff Availability

1. **User action:** Admin views staff assignment page for event
2. **Controller:** `EventStaffController::index()`
3. **Service call:** `StaffAvailabilityService::getStaffAvailabilityForEvent()`
4. **Query:** Fetch all staff with availability status
5. **For each staff:** Check conflicts using event dates
6. **Response:** Return list with availability flags

### Workflow 3: View Staff Dashboard

1. **User action:** Staff member logs in
2. **Controller:** `DashboardController::staffDashboard()`
3. **Model query:** `Staff::with('events')->find($staffId)`
4. **Relationship:** Load events through `staff.events()` relationship
5. **Filter:** Upcoming, current, past assignments
6. **Response:** Render dashboard with assignments

## Best Practices

### DO ✅

1. **Depend on interfaces, not implementations**
   ```php
   private StaffAvailabilityServiceInterface $staffService; // Good
   ```

2. **Use service layer for business logic**
   ```php
   $this->staffAvailability->isAvailable($staff, $start, $end);
   ```

3. **Use Eloquent relationships for data queries**
   ```php
   $staff->events()->where('status', 'confirmed')->get();
   ```

4. **Keep backwards-compatible aliases during migration**
   ```php
   class Event extends Modules\Events\Models\Event {}
   ```

### DON'T ❌

1. **Don't depend on concrete service classes**
   ```php
   private StaffAvailabilityService $staffService; // Bad - concrete class
   ```

2. **Don't access other module's models for business logic**
   ```php
   // Bad - Events module modifying Staff directly
   Staff::find($id)->update(['is_available' => false]);

   // Good - Use Staff module's service
   $this->staffService->toggleAvailability($id);
   ```

3. **Don't create circular dependencies**
   ```php
   // Bad - both modules depending on each other's services creates a cycle
   EventService → StaffService → EventService (circular!)
   ```

4. **Don't bypass service layer in controllers**
   ```php
   // Bad - Controller directly querying
   $event = Event::find($id);
   $event->staff()->attach($staffId);

   // Good - Use service
   $this->eventStaffService->assignStaff($eventId, $staffId);
   ```

## Migration Strategy

When refactoring existing code to use modules:

1. **Identify dependencies** - Which modules need to talk to each other?
2. **Create interface** - Define the contract in the providing module
3. **Implement service** - Build the concrete implementation
4. **Inject interface** - Update consuming module to use interface
5. **Bind in provider** - Register binding in service provider
6. **Test integration** - Verify cross-module communication works
7. **Update controllers** - Refactor controllers to use services
8. **Remove old code** - Clean up direct model access

## Future Enhancements

### Potential Improvements

1. **Event-Driven Communication**
   - Use Laravel events for decoupled notifications
   - Example: `StaffAssignedToEvent` event

2. **API Versioning**
   - Version module interfaces for breaking changes
   - Maintain multiple versions during transitions

3. **Module Extraction**
   - Eventually extract modules into separate packages
   - Enable independent deployment

## References

- [Laravel Service Container](https://laravel.com/docs/container)
- [Dependency Inversion Principle](https://en.wikipedia.org/wiki/Dependency_inversion_principle)
- [Clean Architecture](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
