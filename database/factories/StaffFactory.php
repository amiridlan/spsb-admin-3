<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffFactory extends Factory
{
    protected $model = Staff::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'position' => fake()->randomElement(['Event Coordinator', 'Event Manager', 'Technical Support', 'Catering Manager']),
            'specializations' => fake()->randomElements(['Audio/Visual', 'Catering', 'Security', 'Setup', 'Cleanup'], 2),
            'is_available' => true,
            'notes' => null,
            'annual_leave_total' => 15,
            'annual_leave_used' => 0,
            'sick_leave_total' => 10,
            'sick_leave_used' => 0,
            'emergency_leave_total' => 5,
            'emergency_leave_used' => 0,
            'leave_notes' => null,
        ];
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }
}
