<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Events\Models\Event;
use Modules\Staff\Models\Staff;
use App\Models\User;

class EventStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get staff members
        $staff1 = Staff::whereHas('user', function ($q) {
            $q->where('email', 'staff1@example.com');
        })->first();

        $staff2 = Staff::whereHas('user', function ($q) {
            $q->where('email', 'staff2@example.com');
        })->first();

        $staff3 = Staff::whereHas('user', function ($q) {
            $q->where('email', 'staff3@example.com');
        })->first();

        $staff4 = Staff::whereHas('user', function ($q) {
            $q->where('email', 'staff4@example.com');
        })->first();

        $staff5 = Staff::whereHas('user', function ($q) {
            $q->where('email', 'staff5@example.com');
        })->first();

        // Get events
        $annualGala = Event::where('title', 'Annual Company Gala')->first();
        $techSummit = Event::where('title', 'Tech Summit 2026')->first();
        $wedding = Event::where('title', 'Summer Garden Wedding')->first();
        $productLaunch = Event::where('title', 'Product Launch Event')->first();
        $boardMeeting = Event::where('title', 'Executive Board Meeting')->first();
        $trainingWorkshop = Event::where('title', 'Training Workshop')->first();
        $newYear = Event::where('title', 'New Year Celebration 2026')->first();
        $networking = Event::where('title', 'Monthly Business Networking')->first();
        $craftFair = Event::where('title', 'Holiday Craft Fair')->first();
        $musicFestival = Event::where('title', 'Spring Music Festival')->first();
        $teamBuilding = Event::where('title', 'Team Building Workshop')->first();
        $investmentMeeting = Event::where('title', 'Investment Meeting')->first();
        $charityGala = Event::where('title', 'Charity Gala Dinner')->first();
        $medicalConf = Event::where('title', 'Medical Conference')->first();

        $assignments = [];

        // Annual Company Gala - Large VIP event needs multiple staff
        if ($annualGala && $staff1 && $staff2 && $staff5) {
            $assignments[] = [
                'event_id' => $annualGala->id,
                'staff_id' => $staff1->id,
                'role' => 'Event Coordinator',
                'notes' => 'Lead coordinator - oversee entire event',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $annualGala->id,
                'staff_id' => $staff2->id,
                'role' => 'Technical Support',
                'notes' => 'Handle AV equipment and lighting',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $annualGala->id,
                'staff_id' => $staff5->id,
                'role' => 'Security Officer',
                'notes' => 'VIP security and crowd control',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Tech Summit - Needs technical staff
        if ($techSummit && $staff1 && $staff2 && $staff3) {
            $assignments[] = [
                'event_id' => $techSummit->id,
                'staff_id' => $staff1->id,
                'role' => 'Event Coordinator',
                'notes' => 'Coordinate with speakers and manage schedule',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $techSummit->id,
                'staff_id' => $staff2->id,
                'role' => 'Technical Support',
                'notes' => 'WiFi setup and AV for presentations',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $techSummit->id,
                'staff_id' => $staff3->id,
                'role' => 'Setup Crew',
                'notes' => 'Room setup and breakdown',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Summer Garden Wedding - Outdoor event needs setup and security
        if ($wedding && $staff1 && $staff3 && $staff4 && $staff5) {
            $assignments[] = [
                'event_id' => $wedding->id,
                'staff_id' => $staff1->id,
                'role' => 'Event Coordinator',
                'notes' => 'Main contact for bride and groom',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $wedding->id,
                'staff_id' => $staff3->id,
                'role' => 'Setup Crew',
                'notes' => 'Outdoor pavilion decoration and setup',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $wedding->id,
                'staff_id' => $staff4->id,
                'role' => 'Catering Manager',
                'notes' => 'Wedding reception catering',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $wedding->id,
                'staff_id' => $staff5->id,
                'role' => 'Security',
                'notes' => 'Parking and guest management',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Product Launch - Needs AV and setup
        if ($productLaunch && $staff1 && $staff2) {
            $assignments[] = [
                'event_id' => $productLaunch->id,
                'staff_id' => $staff1->id,
                'role' => 'Event Coordinator',
                'notes' => 'Coordinate product demonstration',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $productLaunch->id,
                'staff_id' => $staff2->id,
                'role' => 'Technical Support',
                'notes' => 'Display setup and audio visual',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Executive Board Meeting - Smaller, needs minimal staff
        if ($boardMeeting && $staff3) {
            $assignments[] = [
                'event_id' => $boardMeeting->id,
                'staff_id' => $staff3->id,
                'role' => 'Event Assistant',
                'notes' => 'Setup refreshments and materials',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Training Workshop
        if ($trainingWorkshop && $staff2 && $staff3) {
            $assignments[] = [
                'event_id' => $trainingWorkshop->id,
                'staff_id' => $staff2->id,
                'role' => 'Technical Support',
                'notes' => 'Setup projector and presentation equipment',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $trainingWorkshop->id,
                'staff_id' => $staff3->id,
                'role' => 'Setup Crew',
                'notes' => 'Room arrangement and materials',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // New Year Celebration (completed)
        if ($newYear && $staff1 && $staff2 && $staff4 && $staff5) {
            $assignments[] = [
                'event_id' => $newYear->id,
                'staff_id' => $staff1->id,
                'role' => 'Event Coordinator',
                'notes' => 'Successfully coordinated event',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $newYear->id,
                'staff_id' => $staff2->id,
                'role' => 'Technical Support',
                'notes' => 'Live band sound system',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $newYear->id,
                'staff_id' => $staff4->id,
                'role' => 'Catering Manager',
                'notes' => 'New year party catering',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $newYear->id,
                'staff_id' => $staff5->id,
                'role' => 'Security Officer',
                'notes' => 'Late night security',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Monthly Business Networking (completed)
        if ($networking && $staff3 && $staff4) {
            $assignments[] = [
                'event_id' => $networking->id,
                'staff_id' => $staff3->id,
                'role' => 'Event Assistant',
                'notes' => 'Registration and setup',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $networking->id,
                'staff_id' => $staff4->id,
                'role' => 'Catering Manager',
                'notes' => 'Light refreshments',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Holiday Craft Fair (completed)
        if ($craftFair && $staff1 && $staff3 && $staff5) {
            $assignments[] = [
                'event_id' => $craftFair->id,
                'staff_id' => $staff1->id,
                'role' => 'Event Coordinator',
                'notes' => 'Vendor coordination',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $craftFair->id,
                'staff_id' => $staff3->id,
                'role' => 'Setup Crew',
                'notes' => 'Booth setup and layout',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $craftFair->id,
                'staff_id' => $staff5->id,
                'role' => 'Security',
                'notes' => 'Crowd control and vendor security',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Spring Music Festival (cancelled) - Staff was assigned before cancellation
        if ($musicFestival && $staff2 && $staff5) {
            $assignments[] = [
                'event_id' => $musicFestival->id,
                'staff_id' => $staff2->id,
                'role' => 'Technical Support',
                'notes' => 'Outdoor sound system setup',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $musicFestival->id,
                'staff_id' => $staff5->id,
                'role' => 'Security Officer',
                'notes' => 'Event security and parking',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Investment Meeting - Small meeting
        if ($investmentMeeting && $staff3) {
            $assignments[] = [
                'event_id' => $investmentMeeting->id,
                'staff_id' => $staff3->id,
                'role' => 'Event Assistant',
                'notes' => 'Prepare refreshments and materials',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Charity Gala Dinner - Large event
        if ($charityGala && $staff1 && $staff3 && $staff4 && $staff5) {
            $assignments[] = [
                'event_id' => $charityGala->id,
                'staff_id' => $staff1->id,
                'role' => 'Event Coordinator',
                'notes' => 'Coordinate with charity organizers',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $charityGala->id,
                'staff_id' => $staff3->id,
                'role' => 'Setup Crew',
                'notes' => 'Silent auction display tables',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $charityGala->id,
                'staff_id' => $staff4->id,
                'role' => 'Catering Manager',
                'notes' => 'Formal dinner service',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $charityGala->id,
                'staff_id' => $staff5->id,
                'role' => 'Security',
                'notes' => 'Secure auction items and donations',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Medical Conference
        if ($medicalConf && $staff1 && $staff2 && $staff3) {
            $assignments[] = [
                'event_id' => $medicalConf->id,
                'staff_id' => $staff1->id,
                'role' => 'Event Coordinator',
                'notes' => 'Speaker coordination and schedule',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $medicalConf->id,
                'staff_id' => $staff2->id,
                'role' => 'Technical Support',
                'notes' => 'Medical-grade AV setup',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assignments[] = [
                'event_id' => $medicalConf->id,
                'staff_id' => $staff3->id,
                'role' => 'Setup Crew',
                'notes' => 'Conference materials and signage',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all assignments
        if (!empty($assignments)) {
            DB::table('event_staff')->insert($assignments);
        }

        $this->command->info('Event staff assignments seeded successfully!');
    }
}
