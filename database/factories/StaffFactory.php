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
            'department' => fake()->randomElement(['Events', 'Facilities', 'Catering', 'Technical']),
            'phone' => fake()->phoneNumber(),
            'is_available' => true,
        ];
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }
}
