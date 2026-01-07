<?php

namespace Database\Factories;

use App\Models\EventSpace;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventSpaceFactory extends Factory
{
    protected $model = EventSpace::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true) . ' Room',
            'location' => fake()->address(),
            'capacity' => fake()->numberBetween(10, 200),
            'description' => fake()->paragraph(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
