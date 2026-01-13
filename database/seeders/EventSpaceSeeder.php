<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Events\Models\EventSpace;

class EventSpaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spaces = [
            [
                'name' => 'Grand Ballroom',
                'location' => 'Building A, Floor 1',
                'description' => 'Large event space suitable for weddings, conferences, and major events',
                'capacity' => 200,
                'is_active' => true,
            ],
            [
                'name' => 'Conference Room A',
                'location' => 'Building B, Floor 2',
                'description' => 'Medium-sized meeting room with presentation facilities',
                'capacity' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Conference Room B',
                'location' => 'Building B, Floor 2',
                'description' => 'Smaller meeting room perfect for workshops and team meetings',
                'capacity' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Outdoor Pavilion',
                'location' => 'Garden Area',
                'description' => 'Open-air venue ideal for summer events and outdoor celebrations',
                'capacity' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Boardroom',
                'location' => 'Building A, Floor 3',
                'description' => 'Executive meeting room with premium amenities',
                'capacity' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Exhibition Hall',
                'location' => 'Building C, Ground Floor',
                'description' => 'Large open space for exhibitions, trade shows, and markets',
                'capacity' => 150,
                'is_active' => true,
            ],
        ];

        foreach ($spaces as $space) {
            EventSpace::updateOrCreate(
                ['name' => $space['name']],
                $space
            );
        }

        $this->command->info('Event spaces seeded successfully!');
    }
}
