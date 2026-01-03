<?php

namespace App\Console\Commands;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CompletePassedEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:complete-passed
                            {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically mark confirmed events as completed when their end date has passed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('Checking for confirmed events with passed end dates...');

        // Get all confirmed events where end_date is in the past
        $events = Event::where('status', 'confirmed')
            ->where('end_date', '<', Carbon::today())
            ->get();

        if ($events->isEmpty()) {
            $this->info('No events found that need to be completed.');
            return self::SUCCESS;
        }

        $this->info("Found {$events->count()} event(s) to mark as completed.");

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $completed = 0;

        foreach ($events as $event) {
            $this->line("- {$event->title} (ID: {$event->id}) - End Date: {$event->end_date->format('Y-m-d')}");

            if (!$isDryRun) {
                $event->update(['status' => 'completed']);
                $completed++;
            }
        }

        $this->newLine();

        if ($isDryRun) {
            $this->info("{$events->count()} event(s) would be marked as completed.");
        } else {
            $this->info("{$completed} event(s) successfully marked as completed.");
        }

        return self::SUCCESS;
    }
}
