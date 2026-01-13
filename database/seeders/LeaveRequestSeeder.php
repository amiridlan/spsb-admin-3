<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Staff\Models\LeaveRequest;
use Modules\Staff\Models\Staff;

class LeaveRequestSeeder extends Seeder
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

        // Get reviewers
        $admin1 = User::where('email', 'admin1@example.com')->first();
        $admin2 = User::where('email', 'admin2@example.com')->first();
        $head1 = User::where('email', 'head1@example.com')->first();
        $head2 = User::where('email', 'head2@example.com')->first();
        $head3 = User::where('email', 'head3@example.com')->first();

        // === PENDING - No reviews (5 requests) ===

        if ($staff1) {
            LeaveRequest::create([
                'staff_id' => $staff1->id,
                'leave_type' => 'annual',
                'start_date' => now()->addDays(20),
                'end_date' => now()->addDays(22),
                'total_days' => 3,
                'reason' => 'Family vacation trip',
                'status' => 'pending',
                'hr_reviewed_by' => null,
                'hr_review_notes' => null,
                'hr_reviewed_at' => null,
                'head_reviewed_by' => null,
                'head_review_notes' => null,
                'head_reviewed_at' => null,
            ]);
        }

        if ($staff2) {
            LeaveRequest::create([
                'staff_id' => $staff2->id,
                'leave_type' => 'sick',
                'start_date' => now()->addDays(5),
                'end_date' => now()->addDays(6),
                'total_days' => 2,
                'reason' => 'Medical checkup and recovery',
                'status' => 'pending',
                'hr_reviewed_by' => null,
                'hr_review_notes' => null,
                'hr_reviewed_at' => null,
                'head_reviewed_by' => null,
                'head_review_notes' => null,
                'head_reviewed_at' => null,
            ]);
        }

        if ($staff3) {
            LeaveRequest::create([
                'staff_id' => $staff3->id,
                'leave_type' => 'annual',
                'start_date' => now()->addDays(30),
                'end_date' => now()->addDays(35),
                'total_days' => 6,
                'reason' => 'Extended holiday with family',
                'status' => 'pending',
                'hr_reviewed_by' => null,
                'hr_review_notes' => null,
                'hr_reviewed_at' => null,
                'head_reviewed_by' => null,
                'head_review_notes' => null,
                'head_reviewed_at' => null,
            ]);
        }

        if ($staff4) {
            LeaveRequest::create([
                'staff_id' => $staff4->id,
                'leave_type' => 'emergency',
                'start_date' => now()->addDays(2),
                'end_date' => now()->addDays(3),
                'total_days' => 2,
                'reason' => 'Family emergency - urgent',
                'status' => 'pending',
                'hr_reviewed_by' => null,
                'hr_review_notes' => null,
                'hr_reviewed_at' => null,
                'head_reviewed_by' => null,
                'head_review_notes' => null,
                'head_reviewed_at' => null,
            ]);
        }

        if ($staff5) {
            LeaveRequest::create([
                'staff_id' => $staff5->id,
                'leave_type' => 'annual',
                'start_date' => now()->addDays(14),
                'end_date' => now()->addDays(16),
                'total_days' => 3,
                'reason' => 'Personal matters',
                'status' => 'pending',
                'hr_reviewed_by' => null,
                'hr_review_notes' => null,
                'hr_reviewed_at' => null,
                'head_reviewed_by' => null,
                'head_review_notes' => null,
                'head_reviewed_at' => null,
            ]);
        }

        // === PENDING - HR approved, awaiting Head (3 requests) ===

        if ($staff1 && $admin1) {
            LeaveRequest::create([
                'staff_id' => $staff1->id,
                'leave_type' => 'sick',
                'start_date' => now()->addDays(10),
                'end_date' => now()->addDays(11),
                'total_days' => 2,
                'reason' => 'Doctor appointment and rest',
                'status' => 'pending',
                'hr_reviewed_by' => $admin1->id,
                'hr_review_notes' => 'Approved by HR - medical documentation provided',
                'hr_reviewed_at' => now()->subDays(1),
                'head_reviewed_by' => null,
                'head_review_notes' => null,
                'head_reviewed_at' => null,
            ]);
        }

        if ($staff3 && $admin2) {
            LeaveRequest::create([
                'staff_id' => $staff3->id,
                'leave_type' => 'annual',
                'start_date' => now()->addDays(25),
                'end_date' => now()->addDays(27),
                'total_days' => 3,
                'reason' => 'Wedding anniversary celebration',
                'status' => 'pending',
                'hr_reviewed_by' => $admin2->id,
                'hr_review_notes' => 'Approved by HR',
                'hr_reviewed_at' => now()->subHours(12),
                'head_reviewed_by' => null,
                'head_review_notes' => null,
                'head_reviewed_at' => null,
            ]);
        }

        if ($staff4 && $admin1) {
            LeaveRequest::create([
                'staff_id' => $staff4->id,
                'leave_type' => 'annual',
                'start_date' => now()->addDays(40),
                'end_date' => now()->addDays(44),
                'total_days' => 5,
                'reason' => 'Long overdue vacation',
                'status' => 'pending',
                'hr_reviewed_by' => $admin1->id,
                'hr_review_notes' => 'Approved - employee has sufficient balance',
                'hr_reviewed_at' => now()->subDays(2),
                'head_reviewed_by' => null,
                'head_review_notes' => null,
                'head_reviewed_at' => null,
            ]);
        }

        // === PENDING - Head approved, awaiting HR (3 requests) ===

        if ($staff2 && $head2) {
            LeaveRequest::create([
                'staff_id' => $staff2->id,
                'leave_type' => 'annual',
                'start_date' => now()->addDays(18),
                'end_date' => now()->addDays(20),
                'total_days' => 3,
                'reason' => 'Short break to recharge',
                'status' => 'pending',
                'hr_reviewed_by' => null,
                'hr_review_notes' => null,
                'hr_reviewed_at' => null,
                'head_reviewed_by' => $head2->id,
                'head_review_notes' => 'Approved by department head - team coverage arranged',
                'head_reviewed_at' => now()->subHours(8),
            ]);
        }

        if ($staff3 && $head2) {
            LeaveRequest::create([
                'staff_id' => $staff3->id,
                'leave_type' => 'sick',
                'start_date' => now()->addDays(7),
                'end_date' => now()->addDays(8),
                'total_days' => 2,
                'reason' => 'Medical procedure scheduled',
                'status' => 'pending',
                'hr_reviewed_by' => null,
                'hr_review_notes' => null,
                'hr_reviewed_at' => null,
                'head_reviewed_by' => $head2->id,
                'head_review_notes' => 'Approved - medical reasons',
                'head_reviewed_at' => now()->subDays(1),
            ]);
        }

        if ($staff1 && $head1) {
            LeaveRequest::create([
                'staff_id' => $staff1->id,
                'leave_type' => 'annual',
                'start_date' => now()->addDays(50),
                'end_date' => now()->addDays(54),
                'total_days' => 5,
                'reason' => 'Attending family reunion',
                'status' => 'pending',
                'hr_reviewed_by' => null,
                'hr_review_notes' => null,
                'hr_reviewed_at' => null,
                'head_reviewed_by' => $head1->id,
                'head_review_notes' => 'Approved by IT head',
                'head_reviewed_at' => now()->subHours(6),
            ]);
        }

        // === APPROVED - Both approved (4 requests) ===
        // Important: Update staff leave_used balances for these

        if ($staff1 && $admin1 && $head1) {
            LeaveRequest::create([
                'staff_id' => $staff1->id,
                'leave_type' => 'annual',
                'start_date' => now()->subDays(10),
                'end_date' => now()->subDays(7),
                'total_days' => 4,
                'reason' => 'Personal time off',
                'status' => 'approved',
                'hr_reviewed_by' => $admin1->id,
                'hr_review_notes' => 'Approved by HR',
                'hr_reviewed_at' => now()->subDays(15),
                'head_reviewed_by' => $head1->id,
                'head_review_notes' => 'Approved by department head',
                'head_reviewed_at' => now()->subDays(14),
            ]);
            // Update staff balance - this was already used
            // (staff1 currently has annual_leave_used = 0, so add 4 days)
            $staff1->annual_leave_used += 4;
            $staff1->save();
        }

        if ($staff2 && $admin2 && $head2) {
            LeaveRequest::create([
                'staff_id' => $staff2->id,
                'leave_type' => 'sick',
                'start_date' => now()->subDays(20),
                'end_date' => now()->subDays(19),
                'total_days' => 2,
                'reason' => 'Flu recovery',
                'status' => 'approved',
                'hr_reviewed_by' => $admin2->id,
                'hr_review_notes' => 'Approved - medical certificate provided',
                'hr_reviewed_at' => now()->subDays(22),
                'head_reviewed_by' => $head2->id,
                'head_review_notes' => 'Approved',
                'head_reviewed_at' => now()->subDays(21),
            ]);
            // Update staff balance - this was already used
            // (staff2 currently has sick_leave_used = 1, so add 2 more = 3 total)
            $staff2->sick_leave_used += 2;
            $staff2->save();
        }

        if ($staff4 && $admin1 && $head3) {
            LeaveRequest::create([
                'staff_id' => $staff4->id,
                'leave_type' => 'annual',
                'start_date' => now()->subDays(30),
                'end_date' => now()->subDays(26),
                'total_days' => 5,
                'reason' => 'Summer holiday',
                'status' => 'approved',
                'hr_reviewed_by' => $admin1->id,
                'hr_review_notes' => 'Approved',
                'hr_reviewed_at' => now()->subDays(35),
                'head_reviewed_by' => $head3->id,
                'head_review_notes' => 'Approved by Finance head',
                'head_reviewed_at' => now()->subDays(34),
            ]);
            // Update staff balance
            // (staff4 currently has annual_leave_used = 3, so add 5 more = 8 total)
            $staff4->annual_leave_used += 5;
            $staff4->save();
        }

        if ($staff5 && $admin2 && $head1) {
            LeaveRequest::create([
                'staff_id' => $staff5->id,
                'leave_type' => 'annual',
                'start_date' => now()->subDays(5),
                'end_date' => now()->subDays(3),
                'total_days' => 3,
                'reason' => 'Short personal break',
                'status' => 'approved',
                'hr_reviewed_by' => $admin2->id,
                'hr_review_notes' => 'Approved by HR',
                'hr_reviewed_at' => now()->subDays(8),
                'head_reviewed_by' => $head1->id,
                'head_review_notes' => 'Approved - adequate coverage',
                'head_reviewed_at' => now()->subDays(7),
            ]);
            // Update staff balance
            // (staff5 currently has annual_leave_used = 1, so add 3 more = 4 total)
            $staff5->annual_leave_used += 3;
            $staff5->save();
        }

        // === REJECTED (3 requests) ===

        if ($staff2 && $admin1) {
            LeaveRequest::create([
                'staff_id' => $staff2->id,
                'leave_type' => 'annual',
                'start_date' => now()->addDays(15),
                'end_date' => now()->addDays(20),
                'total_days' => 6,
                'reason' => 'Extended vacation request',
                'status' => 'rejected',
                'hr_reviewed_by' => $admin1->id,
                'hr_review_notes' => 'Rejected - insufficient leave balance remaining for the year',
                'hr_reviewed_at' => now()->subDays(3),
                'head_reviewed_by' => null,
                'head_review_notes' => null,
                'head_reviewed_at' => null,
            ]);
        }

        if ($staff3 && $head2) {
            LeaveRequest::create([
                'staff_id' => $staff3->id,
                'leave_type' => 'annual',
                'start_date' => now()->addDays(8),
                'end_date' => now()->addDays(10),
                'total_days' => 3,
                'reason' => 'Last minute vacation',
                'status' => 'rejected',
                'hr_reviewed_by' => null,
                'hr_review_notes' => null,
                'hr_reviewed_at' => null,
                'head_reviewed_by' => $head2->id,
                'head_review_notes' => 'Rejected - critical project deadline, unable to provide coverage',
                'head_reviewed_at' => now()->subDays(1),
            ]);
        }

        if ($staff5 && $admin2 && $head1) {
            LeaveRequest::create([
                'staff_id' => $staff5->id,
                'leave_type' => 'emergency',
                'start_date' => now()->addDays(3),
                'end_date' => now()->addDays(5),
                'total_days' => 3,
                'reason' => 'Personal urgent matter',
                'status' => 'rejected',
                'hr_reviewed_by' => $admin2->id,
                'hr_review_notes' => 'Rejected - does not meet emergency leave criteria',
                'hr_reviewed_at' => now()->subHours(18),
                'head_reviewed_by' => null,
                'head_review_notes' => null,
                'head_reviewed_at' => null,
            ]);
        }

        // === CANCELLED (2 requests) ===

        if ($staff1) {
            LeaveRequest::create([
                'staff_id' => $staff1->id,
                'leave_type' => 'annual',
                'start_date' => now()->addDays(12),
                'end_date' => now()->addDays(14),
                'total_days' => 3,
                'reason' => 'Trip cancelled due to circumstances',
                'status' => 'cancelled',
                'hr_reviewed_by' => null,
                'hr_review_notes' => null,
                'hr_reviewed_at' => null,
                'head_reviewed_by' => null,
                'head_review_notes' => null,
                'head_reviewed_at' => null,
            ]);
        }

        if ($staff4) {
            LeaveRequest::create([
                'staff_id' => $staff4->id,
                'leave_type' => 'sick',
                'start_date' => now()->addDays(6),
                'end_date' => now()->addDays(7),
                'total_days' => 2,
                'reason' => 'Medical appointment - no longer needed',
                'status' => 'cancelled',
                'hr_reviewed_by' => null,
                'hr_review_notes' => null,
                'hr_reviewed_at' => null,
                'head_reviewed_by' => null,
                'head_review_notes' => null,
                'head_reviewed_at' => null,
            ]);
        }

        $this->command->info('Leave requests seeded successfully!');
    }
}
