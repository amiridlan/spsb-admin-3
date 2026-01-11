<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        $departments = [
            ['name' => 'Information Technology', 'code' => 'IT'],
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Finance', 'code' => 'FIN'],
            ['name' => 'Operations', 'code' => 'OPS'],
            ['name' => 'Marketing', 'code' => 'MKT'],
            ['name' => 'Sales', 'code' => 'SALES'],
            ['name' => 'Administration', 'code' => 'ADMIN'],
        ];

        $department = fake()->randomElement($departments);

        return [
            'name' => $department['name'] . ' ' . fake()->numberBetween(1, 100),
            'code' => $department['code'] . fake()->numberBetween(1, 100),
            'description' => fake()->sentence(),
            'head_user_id' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withHead(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'head_user_id' => $userId,
        ]);
    }
}
