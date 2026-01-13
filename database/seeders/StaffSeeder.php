<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Staff\Models\Staff;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users and departments
        $staff1 = User::where('email', 'staff1@example.com')->first();
        $staff2 = User::where('email', 'staff2@example.com')->first();
        $staff3 = User::where('email', 'staff3@example.com')->first();
        $staff4 = User::where('email', 'staff4@example.com')->first();
        $staff5 = User::where('email', 'staff5@example.com')->first();

        $itDept = Department::where('code', 'IT')->first();
        $hrDept = Department::where('code', 'HR')->first();
        $finDept = Department::where('code', 'FIN')->first();
        $opsDept = Department::where('code', 'OPS')->first();

        // Staff 1 - IT Department
        if ($staff1) {
            Staff::updateOrCreate(
                ['user_id' => $staff1->id],
                [
                    'department_id' => $itDept?->id,
                    'position' => 'Event Coordinator',
                    'specializations' => ['Audio/Visual', 'Setup'],
                    'is_available' => true,
                    'annual_leave_total' => 15,
                    'annual_leave_used' => 0,
                    'sick_leave_total' => 10,
                    'sick_leave_used' => 0,
                    'emergency_leave_total' => 5,
                    'emergency_leave_used' => 0,
                ]
            );
        }

        // Staff 2 - HR Department
        if ($staff2) {
            Staff::updateOrCreate(
                ['user_id' => $staff2->id],
                [
                    'department_id' => $hrDept?->id,
                    'position' => 'Technical Support',
                    'specializations' => ['Audio/Visual', 'Lighting'],
                    'is_available' => true,
                    'annual_leave_total' => 15,
                    'annual_leave_used' => 2,
                    'sick_leave_total' => 10,
                    'sick_leave_used' => 1,
                    'emergency_leave_total' => 5,
                    'emergency_leave_used' => 0,
                ]
            );
        }

        // Staff 3 - HR Department
        if ($staff3) {
            Staff::updateOrCreate(
                ['user_id' => $staff3->id],
                [
                    'department_id' => $hrDept?->id,
                    'position' => 'Event Assistant',
                    'specializations' => ['Setup', 'Cleanup'],
                    'is_available' => true,
                    'annual_leave_total' => 15,
                    'annual_leave_used' => 0,
                    'sick_leave_total' => 10,
                    'sick_leave_used' => 0,
                    'emergency_leave_total' => 5,
                    'emergency_leave_used' => 0,
                ]
            );
        }

        // Staff 4 - Finance Department
        if ($staff4) {
            Staff::updateOrCreate(
                ['user_id' => $staff4->id],
                [
                    'department_id' => $finDept?->id,
                    'position' => 'Catering Manager',
                    'specializations' => ['Catering'],
                    'is_available' => true,
                    'annual_leave_total' => 15,
                    'annual_leave_used' => 3,
                    'sick_leave_total' => 10,
                    'sick_leave_used' => 0,
                    'emergency_leave_total' => 5,
                    'emergency_leave_used' => 1,
                ]
            );
        }

        // Staff 5 - Operations Department
        if ($staff5) {
            Staff::updateOrCreate(
                ['user_id' => $staff5->id],
                [
                    'department_id' => $opsDept?->id,
                    'position' => 'Security Officer',
                    'specializations' => ['Security', 'Crowd Control'],
                    'is_available' => true,
                    'annual_leave_total' => 15,
                    'annual_leave_used' => 1,
                    'sick_leave_total' => 10,
                    'sick_leave_used' => 0,
                    'emergency_leave_total' => 5,
                    'emergency_leave_used' => 0,
                ]
            );
        }

        $this->command->info('Staff seeded successfully!');
    }
}
