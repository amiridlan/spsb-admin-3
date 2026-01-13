<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Events\Models\Event;
use Modules\Events\Models\EventSpace;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get event spaces
        $grandBallroom = EventSpace::where('name', 'Grand Ballroom')->first();
        $confRoomA = EventSpace::where('name', 'Conference Room A')->first();
        $confRoomB = EventSpace::where('name', 'Conference Room B')->first();
        $outdoorPavilion = EventSpace::where('name', 'Outdoor Pavilion')->first();
        $boardroom = EventSpace::where('name', 'Boardroom')->first();
        $exhibitionHall = EventSpace::where('name', 'Exhibition Hall')->first();

        // Get admin users for created_by
        $superadmin = User::where('email', 'superadmin@example.com')->first();
        $admin1 = User::where('email', 'admin1@example.com')->first();
        $admin2 = User::where('email', 'admin2@example.com')->first();

        $events = [
            // Confirmed future events
            [
                'event_space_id' => $grandBallroom?->id,
                'title' => 'Annual Company Gala',
                'description' => 'Year-end celebration event with dinner and entertainment',
                'client_name' => 'ABC Corporation',
                'client_email' => 'contact@abc-corp.com',
                'client_phone' => '555-1001',
                'start_date' => now()->addDays(30),
                'end_date' => now()->addDays(30),
                'start_time' => '18:00:00',
                'end_time' => '23:00:00',
                'status' => 'confirmed',
                'created_by' => $admin1?->id,
                'notes' => 'VIP event - requires extra security and premium catering',
            ],
            [
                'event_space_id' => $confRoomA?->id,
                'title' => 'Tech Summit 2026',
                'description' => 'Technology conference with multiple speakers and workshops',
                'client_name' => 'Tech Innovators Inc',
                'client_email' => 'events@techinnovators.com',
                'client_phone' => '555-1002',
                'start_date' => now()->addDays(45),
                'end_date' => now()->addDays(47),
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'status' => 'confirmed',
                'created_by' => $admin2?->id,
                'notes' => 'Need AV equipment and WiFi setup',
            ],
            [
                'event_space_id' => $outdoorPavilion?->id,
                'title' => 'Summer Garden Wedding',
                'description' => 'Outdoor wedding ceremony and reception',
                'client_name' => 'Sarah Johnson',
                'client_email' => 'sarah.j@email.com',
                'client_phone' => '555-1003',
                'start_date' => now()->addDays(60),
                'end_date' => now()->addDays(60),
                'start_time' => '15:00:00',
                'end_time' => '22:00:00',
                'status' => 'confirmed',
                'created_by' => $superadmin?->id,
                'notes' => 'Weather contingency plan required',
            ],

            // Pending requests
            [
                'event_space_id' => $exhibitionHall?->id,
                'title' => 'Product Launch Event',
                'description' => 'New product showcase and demonstration',
                'client_name' => 'Global Brands Ltd',
                'client_email' => 'marketing@globalbrands.com',
                'client_phone' => '555-1004',
                'start_date' => now()->addDays(90),
                'end_date' => now()->addDays(91),
                'start_time' => '10:00:00',
                'end_time' => '18:00:00',
                'status' => 'pending',
                'created_by' => $admin1?->id,
                'notes' => 'Awaiting final confirmation from client',
            ],
            [
                'event_space_id' => $boardroom?->id,
                'title' => 'Executive Board Meeting',
                'description' => 'Quarterly board meeting for stakeholders',
                'client_name' => 'Finance Corp',
                'client_email' => 'admin@financecorp.com',
                'client_phone' => '555-1005',
                'start_date' => now()->addDays(20),
                'end_date' => now()->addDays(20),
                'start_time' => '14:00:00',
                'end_time' => '17:00:00',
                'status' => 'pending',
                'created_by' => $admin2?->id,
                'notes' => 'Confidential - high-profile attendees',
            ],
            [
                'event_space_id' => $confRoomB?->id,
                'title' => 'Training Workshop',
                'description' => 'Professional development workshop for team',
                'client_name' => 'HR Solutions',
                'client_email' => 'training@hrsolutions.com',
                'client_phone' => '555-1006',
                'start_date' => now()->addDays(15),
                'end_date' => now()->addDays(16),
                'start_time' => '09:00:00',
                'end_time' => '16:00:00',
                'status' => 'pending',
                'created_by' => $admin1?->id,
                'notes' => 'Need projector and whiteboard',
            ],

            // Completed past events
            [
                'event_space_id' => $grandBallroom?->id,
                'title' => 'New Year Celebration 2026',
                'description' => 'New year party with live band and catering',
                'client_name' => 'City Events Company',
                'client_email' => 'bookings@cityevents.com',
                'client_phone' => '555-1007',
                'start_date' => now()->subDays(13),
                'end_date' => now()->subDays(13),
                'start_time' => '20:00:00',
                'end_time' => '02:00:00',
                'status' => 'completed',
                'created_by' => $superadmin?->id,
                'notes' => 'Successfully completed - excellent feedback',
            ],
            [
                'event_space_id' => $confRoomA?->id,
                'title' => 'Monthly Business Networking',
                'description' => 'Professional networking event for local businesses',
                'client_name' => 'Chamber of Commerce',
                'client_email' => 'info@chamber.org',
                'client_phone' => '555-1008',
                'start_date' => now()->subDays(7),
                'end_date' => now()->subDays(7),
                'start_time' => '18:00:00',
                'end_time' => '21:00:00',
                'status' => 'completed',
                'created_by' => $admin2?->id,
                'notes' => 'Well attended - 45 participants',
            ],
            [
                'event_space_id' => $exhibitionHall?->id,
                'title' => 'Holiday Craft Fair',
                'description' => 'Local artisans showcase and sell handmade items',
                'client_name' => 'Artisan Collective',
                'client_email' => 'collective@artisans.com',
                'client_phone' => '555-1009',
                'start_date' => now()->subDays(30),
                'end_date' => now()->subDays(28),
                'start_time' => '10:00:00',
                'end_time' => '18:00:00',
                'status' => 'completed',
                'created_by' => $admin1?->id,
                'notes' => 'Great turnout - requested booking for next year',
            ],

            // Cancelled events
            [
                'event_space_id' => $outdoorPavilion?->id,
                'title' => 'Spring Music Festival',
                'description' => 'Outdoor music festival with multiple bands',
                'client_name' => 'Music Promotions Ltd',
                'client_email' => 'info@musicpromo.com',
                'client_phone' => '555-1010',
                'start_date' => now()->addDays(25),
                'end_date' => now()->addDays(25),
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'status' => 'cancelled',
                'created_by' => $admin2?->id,
                'notes' => 'Cancelled by client - weather concerns',
            ],
            [
                'event_space_id' => $confRoomB?->id,
                'title' => 'Team Building Workshop',
                'description' => 'Corporate team building activities',
                'client_name' => 'Dynamic Enterprises',
                'client_email' => 'hr@dynamic.com',
                'client_phone' => '555-1011',
                'start_date' => now()->addDays(10),
                'end_date' => now()->addDays(10),
                'start_time' => '09:00:00',
                'end_time' => '15:00:00',
                'status' => 'cancelled',
                'created_by' => $admin1?->id,
                'notes' => 'Client conflict - rescheduling requested',
            ],

            // Additional confirmed events
            [
                'event_space_id' => $boardroom?->id,
                'title' => 'Investment Meeting',
                'description' => 'Quarterly investment portfolio review',
                'client_name' => 'Wealth Management Co',
                'client_email' => 'meetings@wealthmgmt.com',
                'client_phone' => '555-1012',
                'start_date' => now()->addDays(7),
                'end_date' => now()->addDays(7),
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'status' => 'confirmed',
                'created_by' => $superadmin?->id,
                'notes' => 'Regular client - refreshments needed',
            ],
            [
                'event_space_id' => $grandBallroom?->id,
                'title' => 'Charity Gala Dinner',
                'description' => 'Fundraising dinner for local charity',
                'client_name' => 'Community Foundation',
                'client_email' => 'events@foundation.org',
                'client_phone' => '555-1013',
                'start_date' => now()->addDays(50),
                'end_date' => now()->addDays(50),
                'start_time' => '19:00:00',
                'end_time' => '23:00:00',
                'status' => 'confirmed',
                'created_by' => $admin1?->id,
                'notes' => 'Silent auction - need display tables',
            ],
            [
                'event_space_id' => $confRoomA?->id,
                'title' => 'Medical Conference',
                'description' => 'Healthcare professionals continuing education',
                'client_name' => 'Medical Association',
                'client_email' => 'admin@medassoc.org',
                'client_phone' => '555-1014',
                'start_date' => now()->addDays(35),
                'end_date' => now()->addDays(36),
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'status' => 'confirmed',
                'created_by' => $admin2?->id,
                'notes' => 'Need medical-grade AV for presentations',
            ],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }

        $this->command->info('Events seeded successfully!');
    }
}
