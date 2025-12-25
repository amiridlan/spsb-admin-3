# System Optimization Guide

Comprehensive guide for optimizing the Event Space Booking System.

---

## Database Optimization

### Index Creation

```sql
-- Events table indexes
CREATE INDEX idx_events_start_date ON events(start_date);
CREATE INDEX idx_events_end_date ON events(end_date);
CREATE INDEX idx_events_status ON events(status);
CREATE INDEX idx_events_space_id ON events(event_space_id);
CREATE INDEX idx_events_created_by ON events(created_by);

-- Event staff pivot table indexes
CREATE INDEX idx_event_staff_event_id ON event_staff(event_id);
CREATE INDEX idx_event_staff_staff_id ON event_staff(staff_id);

-- Staff table indexes
CREATE INDEX idx_staff_user_id ON staff(user_id);
CREATE INDEX idx_staff_is_available ON staff(is_available);

-- Personal access tokens indexes
CREATE INDEX idx_pat_tokenable ON personal_access_tokens(tokenable_type, tokenable_id);
```

### Query Optimization

```php
// BAD: N+1 queries
$events = Event::all();
foreach ($events as $event) {
    echo $event->eventSpace->name; // Additional query per event
}

// GOOD: Eager loading
$events = Event::with('eventSpace')->get();
foreach ($events as $event) {
    echo $event->eventSpace->name; // No additional queries
}

// GOOD: Load multiple relationships
$events = Event::with(['eventSpace', 'creator', 'staff.user'])->get();

// GOOD: Conditional eager loading
$events = Event::with(['eventSpace', 'staff' => function ($query) {
    $query->where('is_available', true);
}])->get();
```

### Query Caching

```php
// Cache expensive queries
$spaces = Cache::remember('active_event_spaces', 3600, function () {
    return EventSpace::where('is_active', true)->get();
});

// Cache with tags for easier invalidation
$stats = Cache::tags(['dashboard', 'stats'])->remember('dashboard_stats', 300, function () {
    return [
        'total_events' => Event::count(),
        'total_spaces' => EventSpace::count(),
        // ... more stats
    ];
});

// Invalidate tagged cache
Cache::tags(['dashboard', 'stats'])->flush();
```

---

## Application Performance

### Configuration Caching

```bash
# Cache configuration files
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Clear all caches
php artisan optimize:clear
```

### Composer Optimization

```bash
# Production optimization
composer install --no-dev --optimize-autoloader

# Generate optimized autoload files
composer dump-autoload --optimize
```

### Asset Optimization

```bash
# Build for production
npm run build

# The Vite build process will:
# - Minify JavaScript and CSS
# - Remove unused code (tree-shaking)
# - Optimize images
# - Generate file hashes for cache busting
```

---

## Caching Strategy

### Redis Configuration

```env
# .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Cache Implementation

```php
// App/Http/Controllers/DashboardController.php
public function adminDashboard(User $user): Response
{
    $cacheKey = "dashboard_stats_{$user->id}";

    $stats = Cache::remember($cacheKey, 300, function () {
        return [
            'total_events' => Event::count(),
            'total_spaces' => EventSpace::where('is_active', true)->count(),
            // ... more statistics
        ];
    });

    return Inertia::render('Dashboard', ['stats' => $stats]);
}
```

### Cache Invalidation

```php
// When an event is created/updated
Event::created(function ($event) {
    Cache::tags(['dashboard', 'calendar'])->flush();
});

Event::updated(function ($event) {
    Cache::tags(['dashboard', 'calendar'])->flush();
    Cache::forget("event_{$event->id}");
});
```

---

## Queue Optimization

### Queue Configuration

```env
# .env
QUEUE_CONNECTION=redis
```

### Queue Workers

```bash
# Run queue worker
php artisan queue:work --tries=3 --timeout=90

# Run with supervisor for production
# /etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/worker.log
```

### Job Examples

```php
// Dispatch email notifications to queue
dispatch(new SendBookingConfirmation($event));

// Delay job execution
dispatch(new SendEventReminder($event))->delay(now()->addDay());

// Chain jobs
Bus::chain([
    new ProcessBooking($booking),
    new SendConfirmation($booking),
    new NotifyStaff($booking),
])->dispatch();
```

---

## Frontend Optimization

### Vue Component Optimization

```vue
<!-- Use v-show for frequently toggled elements -->
<div v-show="isVisible">Content</div>

<!-- Use v-if for rarely changed conditions -->
<div v-if="hasPermission">Admin Panel</div>

<!-- Use computed properties for derived data -->
<script setup>
const filteredEvents = computed(() => {
    return events.filter((e) => e.status === selectedStatus.value);
});
</script>

<!-- Lazy load components -->
<script setup>
import { defineAsyncComponent } from 'vue';

const HeavyComponent = defineAsyncComponent(
    () => import('./components/HeavyComponent.vue'),
);
</script>
```

### Image Optimization

```html

```

---

## Server Configuration

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/public;

    index index.php;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript
               application/x-javascript application/xml+rss
               application/json application/javascript;

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

        # Increase timeouts for long-running requests
        fastcgi_read_timeout 300;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
}
```

### PHP-FPM Configuration

```ini
; /etc/php/8.2/fpm/pool.d/www.conf

; Process management
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500

; Memory limits
php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 20M
php_admin_value[post_max_size] = 25M

; Opcache
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0
opcache.revalidate_freq = 0
```

---

## Monitoring & Debugging

### Enable Query Logging

```php
// config/database.php
'connections' => [
    'mysql' => [
        // ...
        'options' => [
            PDO::ATTR_EMULATE_PREPARES => true,
        ],
        'dump' => [
            'enabled' => env('DB_QUERY_LOG', false),
        ],
    ],
],
```

### Debug Bar (Development Only)

```bash
composer require barryvdh/laravel-debugbar --dev
```

### Performance Monitoring

```php
// Add to routes/web.php (development only)
if (app()->environment('local')) {
    Route::get('/debug/queries', function () {
        DB::enableQueryLog();

        // Run your queries
        $events = Event::with(['eventSpace', 'staff'])->get();

        return DB::getQueryLog();
    });
}
```

---

## Best Practices

### Eager Loading

```php
// Always eager load relationships
Event::with(['eventSpace', 'creator', 'staff.user'])->get();

// Use lazy eager loading when needed
$events = Event::all();
$events->load('staff');
```

### Chunking Large Datasets

```php
// Process large datasets in chunks
Event::chunk(100, function ($events) {
    foreach ($events as $event) {
        // Process event
    }
});

// Or use cursor for memory efficiency
foreach (Event::cursor() as $event) {
    // Process event
}
```

### Avoid SELECT \*

```php
// BAD
Event::all();

// GOOD
Event::select('id', 'title', 'start_date', 'status')->get();
```

### Use Database Transactions

```php
DB::transaction(function () {
    $event = Event::create($data);
    $event->staff()->attach($staffIds);
    // More operations...
});
```

---

## Performance Checklist

- [ ] Database indexes created on frequently queried columns
- [ ] N+1 queries eliminated with eager loading
- [ ] Query results cached where appropriate
- [ ] Configuration cached (config:cache)
- [ ] Routes cached (route:cache)
- [ ] Views cached (view:cache)
- [ ] Composer optimized (--optimize-autoloader)
- [ ] Assets compiled and minified (npm run build)
- [ ] Redis configured for cache and sessions
- [ ] Queue workers running for background jobs
- [ ] Gzip compression enabled
- [ ] Static assets have long cache times
- [ ] Images optimized and lazy loaded
- [ ] Unnecessary logging disabled in production
- [ ] Opcache enabled and configured
- [ ] PHP-FPM properly tuned

---

## Monitoring Commands

```bash
# Check database connections
php artisan db:show

# Monitor queue
php artisan queue:work --once --verbose

# Check scheduled tasks
php artisan schedule:list

# Clear specific cache
php artisan cache:forget key_name

# Monitor logs
tail -f storage/logs/laravel.log

# Check Redis
redis-cli monitor
```

---

## Performance Testing

```bash
# Apache Bench
ab -n 1000 -c 10 https://yourdomain.com/

# Siege
siege -c 10 -t 30S https://yourdomain.com/

# Laravel Telescope (Development)
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```
