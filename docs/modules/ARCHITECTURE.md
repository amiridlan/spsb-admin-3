# Module Architecture

## Overview

This application follows a modular architecture pattern where business domains are separated into independent modules that communicate through service interfaces (contracts). This approach follows Laravel's industry-standard practices and promotes clean architecture principles.

## Architecture Pattern

**Pattern Used:** Service Layer with Interfaces (Method 2 - Industry Standard)

This architecture combines:
- **Service Pattern** - Business logic encapsulated in service classes
- **Dependency Inversion** - Modules depend on interfaces, not concrete implementations
- **Repository Pattern** (implicit) - Data access abstracted through services
- **Domain-Driven Design** - Each module represents a bounded context

## Module Structure

```
Modules/
├── Events/
│   ├── Console/Commands/      # Artisan commands
│   ├── Contracts/              # Service interfaces
│   ├── Http/
│   │   ├── Controllers/        # HTTP request handlers
│   │   └── Resources/          # API resources (transformers)
│   ├── Models/                 # Eloquent models
│   ├── Providers/              # Service providers
│   ├── routes/                 # Module routes
│   └── Services/               # Business logic
│
└── Staff/
    ├── Console/
    ├── Contracts/
    ├── Http/
    │   ├── Controllers/
    │   │   ├── Admin/          # Admin-specific controllers
    │   │   └── Staff/          # Staff-specific controllers
    │   └── Resources/
    ├── Models/
    ├── Providers/
    ├── routes/
    └── Services/
```

## Modules

### 1. Events Module

**Domain:** Event space booking and management

**Responsibilities:**
- Event CRUD operations
- Event Space management
- Staff assignment to events (coordination with Staff module)
- Event analytics and reporting

**Key Services:**
- `EventService` - Event CRUD and business logic
- `EventSpaceService` - Event space management
- `EventStaffAssignmentService` - Staff-event relationships
- `EventAnalyticsService` - Statistics, trends, and reporting

**Models:**
- `Event` - Event bookings
- `EventSpace` - Available spaces for events

**Database Tables:**
- `events`
- `event_spaces`
- `event_staff` (shared pivot table with Staff module)

### 2. Staff Module

**Domain:** Staff management and availability

**Responsibilities:**
- Staff CRUD operations
- Staff availability tracking
- Assignment management
- Staff analytics and workload reporting

**Key Services:**
- `StaffService` - Staff CRUD and business logic
- `StaffAvailabilityService` - Availability checking and conflict detection
- `StaffAnalyticsService` - Staff metrics and workload analysis

**Models:**
- `Staff` - Staff members

**Database Tables:**
- `staff`
- `event_staff` (shared pivot table with Events module)

## Communication Between Modules

### Service Interfaces (Contracts)

Modules communicate exclusively through service interfaces, never through direct model access.

**Example:**
```php
// Events module needs to check staff availability
namespace Modules\Events\Services;

use Modules\Staff\Contracts\StaffAvailabilityServiceInterface;

class EventStaffAssignmentService implements EventStaffAssignmentServiceInterface
{
    public function __construct(
        private StaffAvailabilityServiceInterface $staffAvailability
    ) {}

    public function assignStaff(int $eventId, int $staffId): void
    {
        // Use Staff module's service interface
        if (!$this->staffAvailability->isAvailable($staffId, $dateRange)) {
            throw new \Exception('Staff not available');
        }

        // Assign staff logic...
    }
}
```

### Dependency Injection

All service dependencies are injected through constructor injection and resolved by Laravel's service container.

**Service Provider Bindings:**
```php
// EventServiceProvider
$this->app->bind(
    \Modules\Events\Contracts\EventServiceInterface::class,
    \Modules\Events\Services\EventService::class
);

// StaffServiceProvider
$this->app->bind(
    \Modules\Staff\Contracts\StaffAvailabilityServiceInterface::class,
    \Modules\Staff\Services\StaffAvailabilityService::class
);
```

### Shared Database Resources

While modules are logically separate, they share database infrastructure:

- **`event_staff` pivot table** - Accessed by both modules through their respective models
- Both modules maintain Eloquent relationships but business logic stays within service boundaries

## Controller Architecture

### Thin Controllers

Controllers are kept thin (50-150 lines) and serve as request/response handlers only:

```php
class EventController extends Controller
{
    public function __construct(
        private EventServiceInterface $eventService
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([...]);

        $event = $this->eventService->create($validated);

        return redirect()->route('events.show', $event);
    }
}
```

### Shared Controllers

Controllers used across modules (Dashboard, Metrics, Reports, Calendar) inject services from multiple modules:

```php
class DashboardController extends Controller
{
    public function __construct(
        private EventAnalyticsServiceInterface $eventAnalytics,
        private StaffAnalyticsServiceInterface $staffAnalytics
    ) {}

    public function index()
    {
        return Inertia::render('Dashboard', [
            'eventStats' => $this->eventAnalytics->getStatistics(),
            'staffStats' => $this->staffAnalytics->getStatistics(),
        ]);
    }
}
```

## Service Layer

### Service Responsibilities

Services contain all business logic:
- Data validation (beyond basic HTTP validation)
- Business rule enforcement
- Complex queries
- Data transformation
- Cross-module coordination

### Service Types

**1. CRUD Services**
- Basic create, read, update, delete operations
- Example: `EventService`, `StaffService`

**2. Domain Services**
- Specialized business logic
- Example: `StaffAvailabilityService`, `EventStaffAssignmentService`

**3. Analytics Services**
- Reporting and metrics
- Example: `EventAnalyticsService`, `StaffAnalyticsService`

## Design Principles

### SOLID Principles

**Single Responsibility:** Each service has one clear responsibility

**Open/Closed:** Services are open for extension (via interfaces) but closed for modification

**Liskov Substitution:** Any implementation of an interface can be swapped without breaking functionality

**Interface Segregation:** Interfaces are focused and specific to use cases

**Dependency Inversion:** High-level modules depend on abstractions (interfaces), not concrete implementations

### Additional Principles

**DRY (Don't Repeat Yourself):** Shared logic lives in services, reused across controllers

**Separation of Concerns:** Controllers handle HTTP, services handle business logic, models handle data

**Convention over Configuration:** Follow Laravel conventions for directory structure and naming

## Testing Strategy

### Unit Tests
Test services in isolation with mocked dependencies:
```php
test('can assign available staff to event', function () {
    $availability = Mockery::mock(StaffAvailabilityServiceInterface::class);
    $availability->shouldReceive('isAvailable')->andReturn(true);

    $service = new EventStaffAssignmentService($availability);
    $service->assignStaff($eventId, $staffId);

    // Assertions...
});
```

### Integration Tests
Test cross-module communication:
```php
test('staff assignment checks availability across modules', function () {
    // Create real services with database
    $event = Event::factory()->create();
    $staff = Staff::factory()->create();

    $assignmentService = app(EventStaffAssignmentServiceInterface::class);
    $assignmentService->assignStaff($event->id, $staff->id);

    expect($event->staff)->toHaveCount(1);
});
```

### Feature Tests
Test full HTTP workflows:
```php
test('admin can assign staff to event via HTTP', function () {
    $event = Event::factory()->create();
    $staff = Staff::factory()->create();

    $this->actingAs($admin)
        ->post("/events/{$event->id}/staff", ['staff_id' => $staff->id])
        ->assertRedirect();

    expect($event->fresh()->staff)->toContain($staff);
});
```

## Migration Path

### From Monolithic to Modular

1. **Models moved** from `app/Models/` to `Modules/{Module}/Models/`
2. **Controllers moved** from `app/Http/Controllers/` to `Modules/{Module}/Http/Controllers/`
3. **Business logic extracted** from controllers into services
4. **Direct model access replaced** with service interface calls
5. **Routes organized** by module in `Modules/{Module}/routes/`

### Backwards Compatibility

All existing routes, APIs, and functionality remain unchanged. The refactor is purely architectural with zero breaking changes for end users.

## Benefits

### Maintainability
- Clear boundaries between domains
- Easy to locate code by feature
- Changes isolated to specific modules

### Testability
- Services can be tested independently
- Easy to mock dependencies
- Clear integration points

### Scalability
- Modules can be extracted into separate packages
- Can be deployed independently in future
- Team can work on modules in parallel

### Code Quality
- Enforces separation of concerns
- Prevents tight coupling
- Follows industry best practices

## Future Enhancements

### Potential Module Extractions
- **Users Module** - User management and authentication
- **Notifications Module** - Email, SMS, push notifications
- **Reporting Module** - Advanced analytics and exports
- **API Module** - External API endpoints

### Advanced Patterns
- **Event-Driven Architecture** - Decouple modules further with domain events
- **CQRS** - Separate read and write models for complex queries
- **Microservices** - Extract modules into separate applications

## References

- [Laravel Service Container](https://laravel.com/docs/container)
- [Laravel Service Providers](https://laravel.com/docs/providers)
- [Domain-Driven Design](https://martinfowler.com/tags/domain%20driven%20design.html)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)
