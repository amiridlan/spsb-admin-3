<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Staff\Models\LeaveRequest;
use Modules\Staff\Models\Staff;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 week', '+2 months');
        $endDate = fake()->dateTimeBetween($startDate, $startDate->format('Y-m-d') . ' +1 week');
        $days = $startDate->diff($endDate)->days + 1;

        return [
            'staff_id' => Staff::factory(),
            'leave_type' => fake()->randomElement(['annual', 'sick', 'emergency']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $days,
            'reason' => fake()->sentence(),
            'status' => 'pending',
            'conflict_events' => null,
        ];
    }

    /**
     * Indicate that the leave request is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the leave request is HR approved.
     */
    public function hrApproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'hr_approved',
            'hr_reviewed_at' => now(),
        ]);
    }

    /**
     * Indicate that the leave request is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'hr_reviewed_at' => now()->subDays(1),
            'head_reviewed_at' => now(),
        ]);
    }

    /**
     * Indicate that the leave request is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'hr_reviewed_at' => now(),
        ]);
    }

    /**
     * Indicate that the leave request is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
