# Module Namespace Conventions

## Base Namespace

All modules are under the `Modules\` namespace, configured in `composer.json`:

```json
"autoload": {
    "psr-4": {
        "Modules\\": "Modules/"
    }
}
```

## Namespace Structure

### Events Module

```
Modules\Events\
├── Console\Commands\          # Modules\Events\Console\Commands
├── Contracts\                 # Modules\Events\Contracts
├── Http\Controllers\          # Modules\Events\Http\Controllers
├── Http\Resources\            # Modules\Events\Http\Resources
├── Models\                    # Modules\Events\Models
├── Providers\                 # Modules\Events\Providers
└── Services\                  # Modules\Events\Services
```

### Staff Module

```
Modules\Staff\
├── Console\Commands\          # Modules\Staff\Console\Commands (if needed)
├── Contracts\                 # Modules\Staff\Contracts
├── Http\Controllers\Admin\    # Modules\Staff\Http\Controllers\Admin
├── Http\Controllers\Staff\    # Modules\Staff\Http\Controllers\Staff
├── Http\Resources\            # Modules\Staff\Http\Resources
├── Models\                    # Modules\Staff\Models
├── Providers\                 # Modules\Staff\Providers
└── Services\                  # Modules\Staff\Services
```

## Naming Conventions

### Contracts (Interfaces)

**Convention:** `{Entity}ServiceInterface`

**Examples:**
```php
Modules\Events\Contracts\EventServiceInterface
Modules\Events\Contracts\EventSpaceServiceInterface
Modules\Events\Contracts\EventStaffAssignmentServiceInterface
Modules\Events\Contracts\EventAnalyticsServiceInterface

Modules\Staff\Contracts\StaffServiceInterface
Modules\Staff\Contracts\StaffAvailabilityServiceInterface
Modules\Staff\Contracts\StaffAnalyticsServiceInterface
```

**File naming:** `{Name}Interface.php`
- `EventServiceInterface.php`
- `StaffAvailabilityServiceInterface.php`

### Services

**Convention:** `{Entity}Service`

**Examples:**
```php
Modules\Events\Services\EventService
Modules\Events\Services\EventSpaceService
Modules\Events\Services\EventStaffAssignmentService
Modules\Events\Services\EventAnalyticsService

Modules\Staff\Services\StaffService
Modules\Staff\Services\StaffAvailabilityService
Modules\Staff\Services\StaffAnalyticsService
```

**File naming:** `{Name}Service.php`
- `EventService.php`
- `StaffAvailabilityService.php`

### Models

**Convention:** `{Entity}` (singular, no suffix)

**Examples:**
```php
Modules\Events\Models\Event
Modules\Events\Models\EventSpace

Modules\Staff\Models\Staff
```

**File naming:** `{Name}.php`
- `Event.php`
- `EventSpace.php`
- `Staff.php`

### Controllers

**Convention:** `{Entity}Controller`

**Examples:**
```php
Modules\Events\Http\Controllers\EventController
Modules\Events\Http\Controllers\EventSpaceController
Modules\Events\Http\Controllers\EventStaffController

Modules\Staff\Http\Controllers\Admin\StaffController
Modules\Staff\Http\Controllers\Staff\AssignmentController
```

**File naming:** `{Name}Controller.php`
- `EventController.php`
- `StaffController.php`

### Resources (API Transformers)

**Convention:** `{Entity}Resource`

**Examples:**
```php
Modules\Events\Http\Resources\EventResource
Modules\Events\Http\Resources\EventSpaceResource

Modules\Staff\Http\Resources\StaffResource
```

**File naming:** `{Name}Resource.php`
- `EventResource.php`
- `StaffResource.php`

### Service Providers

**Convention:** `{Module}ServiceProvider`

**Examples:**
```php
Modules\Events\Providers\EventServiceProvider
Modules\Staff\Providers\StaffServiceProvider
```

**File naming:** `{Module}ServiceProvider.php`
- `EventServiceProvider.php`
- `StaffServiceProvider.php`

### Commands (Console)

**Convention:** `{Action}{Entity}` or descriptive name

**Examples:**
```php
Modules\Events\Console\Commands\CompletePassedEvents
```

**File naming:** `{Name}.php`
- `CompletePassedEvents.php`

## Import Examples

### Importing from Same Module

```php
<?php

namespace Modules\Events\Http\Controllers;

use Modules\Events\Contracts\EventServiceInterface;
use Modules\Events\Http\Resources\EventResource;
use Modules\Events\Models\Event;
```

### Importing from Another Module

```php
<?php

namespace Modules\Events\Services;

use Modules\Events\Contracts\EventStaffAssignmentServiceInterface;
use Modules\Staff\Contracts\StaffAvailabilityServiceInterface; // Cross-module

class EventStaffAssignmentService implements EventStaffAssignmentServiceInterface
{
    public function __construct(
        private StaffAvailabilityServiceInterface $staffAvailability
    ) {}
}
```

### Importing in Controllers (Outside Modules)

Controllers that remain in `app/Http/Controllers/` can import module services:

```php
<?php

namespace App\Http\Controllers;

use Modules\Events\Contracts\EventAnalyticsServiceInterface;
use Modules\Staff\Contracts\StaffAnalyticsServiceInterface;

class DashboardController extends Controller
{
    public function __construct(
        private EventAnalyticsServiceInterface $eventAnalytics,
        private StaffAnalyticsServiceInterface $staffAnalytics
    ) {}
}
```

## PSR-4 Autoloading Rules

Following PSR-4 standards:

1. **Namespace must match directory structure**
   - `Modules\Events\Services\EventService` → `Modules/Events/Services/EventService.php`

2. **One class per file**
   - Each file contains exactly one class/interface/trait

3. **File name must match class name**
   - Class `EventService` → File `EventService.php`

4. **Namespace declaration must be first**
   ```php
   <?php

   namespace Modules\Events\Services;

   class EventService
   {
       // ...
   }
   ```

## Cross-Module Dependencies

### Allowed Dependencies

Modules can depend on:
- **Service interfaces** from other modules (via dependency injection)
- **Shared models** like `App\Models\User` (not moved to modules)

### Prohibited Dependencies

Modules must NOT:
- Import models directly from other modules
- Import concrete service implementations from other modules
- Access controllers from other modules

**Wrong:**
```php
use Modules\Staff\Models\Staff; // ❌ Direct model import
use Modules\Staff\Services\StaffService; // ❌ Concrete implementation
```

**Correct:**
```php
use Modules\Staff\Contracts\StaffServiceInterface; // ✅ Interface only
```

## Shared/Common Code

Code used across all modules should remain in `App\`:

```php
App\Models\User                    # Shared user model
App\Http\Controllers\Controller    # Base controller
App\Http\Middleware\*              # Middleware
App\Providers\*                    # App-level providers
```

## Quick Reference

| Type | Namespace Pattern | File Pattern | Example |
|------|------------------|--------------|---------|
| Contract | `Modules\{Module}\Contracts\{Name}Interface` | `{Name}Interface.php` | `EventServiceInterface` |
| Service | `Modules\{Module}\Services\{Name}Service` | `{Name}Service.php` | `EventService` |
| Model | `Modules\{Module}\Models\{Name}` | `{Name}.php` | `Event` |
| Controller | `Modules\{Module}\Http\Controllers\{Name}Controller` | `{Name}Controller.php` | `EventController` |
| Resource | `Modules\{Module}\Http\Resources\{Name}Resource` | `{Name}Resource.php` | `EventResource` |
| Provider | `Modules\{Module}\Providers\{Module}ServiceProvider` | `{Module}ServiceProvider.php` | `EventServiceProvider` |

## Verification

After creating files, verify autoloading works:

```bash
composer dump-autoload
php artisan tinker
```

```php
>>> app(Modules\Events\Contracts\EventServiceInterface::class)
=> Modules\Events\Services\EventService {#...}
```

## IDE Configuration

For proper IDE autocomplete, ensure your IDE is configured to recognize the `Modules/` directory as a source root.

**PhpStorm:**
1. Right-click `Modules` folder
2. Mark Directory as → Sources Root

**VS Code:**
Update `.vscode/settings.json`:
```json
{
    "intelephense.environment.includePaths": [
        "Modules/"
    ]
}
```
