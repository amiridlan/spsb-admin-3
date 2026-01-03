# Auto-Complete Past Confirmed Events

This feature automatically marks confirmed events as "completed" when their end date has passed.

## How It Works

The system uses Laravel's task scheduling to run a command daily at midnight (00:00) that:

1. Finds all events with status "confirmed"
2. Checks if their end_date is before today
3. Updates their status to "completed"

## Components

### 1. Command: `CompletePassedEvents.php`

**Location:** `app/Console/Commands/CompletePassedEvents.php`

This Artisan command handles the logic for finding and updating events.

**Usage:**

```bash
# Run the command manually
php artisan events:complete-passed

# Run in dry-run mode (preview without making changes)
php artisan events:complete-passed --dry-run
```

### 2. Schedule Configuration

**Location:** `routes/console.php`

The command is scheduled to run daily at midnight:

```php
Schedule::command('events:complete-passed')->daily()->at('00:00');
```

## Server Setup

For the scheduler to work, you need to add a single cron entry to your server:

### Linux/Unix (crontab)

1. Open your crontab:

```bash
crontab -e
```

2. Add this line:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Replace `/path-to-your-project` with the actual path to your Laravel installation.

### What This Does

The cron entry runs every minute and checks if any scheduled tasks need to run. Laravel's scheduler then determines which tasks should execute based on their schedule.

## Testing

### Test the Command Manually

```bash
# Create a test confirmed event with a past end date in your database
# Then run:
php artisan events:complete-passed --dry-run

# If it shows the event, run without dry-run:
php artisan events:complete-passed
```

### Test the Scheduler

```bash
# Run all scheduled tasks that are due (useful for testing)
php artisan schedule:run

# List all scheduled tasks
php artisan schedule:list
```

## Development vs Production

### Development

In development, you can manually run the command or use:

```bash
php artisan schedule:work
```

This will run the scheduler in the foreground, executing tasks as they become due.

### Production

In production, always use the cron entry method. This ensures the scheduler runs even if your application restarts.

## Monitoring

### Check Scheduled Tasks

```bash
php artisan schedule:list
```

### View Last Run Time

Laravel tracks when scheduled tasks last ran in the `schedule` cache. You can monitor this through your application logs or by checking the cache.

### Logs

The command will log its activities. Check:

- `storage/logs/laravel.log` for general application logs
- Command output if run manually

## Customization

### Change Schedule Time

Edit `routes/console.php` to change when the command runs:

```php
// Run every hour
Schedule::command('events:complete-passed')->hourly();

// Run twice daily (8am and 8pm)
Schedule::command('events:complete-passed')->twiceDaily(8, 20);

// Run every day at 2am
Schedule::command('events:complete-passed')->dailyAt('02:00');

// Run on weekdays only at midnight
Schedule::command('events:complete-passed')->weekdays()->at('00:00');
```

### Add Notifications

You can add email notifications when events are completed:

```php
Schedule::command('events:complete-passed')
    ->daily()
    ->at('00:00')
    ->emailOutputTo('admin@example.com');
```

### Add Webhook

You can ping a URL after the command runs:

```php
Schedule::command('events:complete-passed')
    ->daily()
    ->at('00:00')
    ->pingBefore('https://your-app.com/webhook/before')
    ->thenPing('https://your-app.com/webhook/after');
```

## Troubleshooting

### Command Not Running

1. Verify cron is set up correctly:

```bash
crontab -l
```

2. Check if the scheduler sees your command:

```bash
php artisan schedule:list
```

3. Manually run the scheduler to see if there are errors:

```bash
php artisan schedule:run -v
```

### Events Not Being Completed

1. Run the command with dry-run to see what would be updated:

```bash
php artisan events:complete-passed --dry-run
```

2. Check if events meet the criteria:
    - Status must be "confirmed"
    - End date must be before today

3. Check application logs for errors:

```bash
tail -f storage/logs/laravel.log
```

### Permission Issues

Ensure the web server user has permission to write to:

- `storage/logs/`
- `storage/framework/cache/`
- `bootstrap/cache/`

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Additional Resources

- [Laravel Task Scheduling Documentation](https://laravel.com/docs/11.x/scheduling)
- [Laravel Artisan Console Documentation](https://laravel.com/docs/11.x/artisan)
