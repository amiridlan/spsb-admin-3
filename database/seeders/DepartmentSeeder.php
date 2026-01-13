<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get head users
        $head1 = User::where('email', 'head1@example.com')->first();
        $head2 = User::where('email', 'head2@example.com')->first();
        $head3 = User::where('email', 'head3@example.com')->first();

        $departments = [
            [
                'name' => 'Information Technology',
                'code' => 'IT',
                'description' => 'Manages technology infrastructure, software development, and IT support',
                'head_user_id' => $head1?->id,
            ],
            [
                'name' => 'Human Resources',
                'code' => 'HR',
                'description' => 'Handles recruitment, employee relations, and personnel management',
                'head_user_id' => $head2?->id,
            ],
            [
                'name' => 'Finance',
                'code' => 'FIN',
                'description' => 'Manages financial planning, accounting, and budgeting',
                'head_user_id' => $head3?->id,
            ],
            [
                'name' => 'Operations',
                'code' => 'OPS',
                'description' => 'Oversees day-to-day business operations and processes',
                'head_user_id' => null,
            ],
            [
                'name' => 'Marketing',
                'code' => 'MKT',
                'description' => 'Handles marketing strategies, campaigns, and brand management',
                'head_user_id' => null,
            ],
            [
                'name' => 'Sales',
                'code' => 'SALES',
                'description' => 'Manages sales activities, client relationships, and revenue generation',
                'head_user_id' => null,
            ],
            [
                'name' => 'Administration',
                'code' => 'ADMIN',
                'description' => 'Provides administrative support and general office management',
                'head_user_id' => null,
            ],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['code' => $department['code']],
                $department
            );
        }

        $this->command->info('Departments seeded successfully!');
    }
}
