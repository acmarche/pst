<?php

namespace Database\Factories;

use App\Models\OperationalObjective;
use Illuminate\Database\Eloquent\Factories\Factory;

final class OperationalObjectiveFactory extends Factory
{
    protected $model = OperationalObjective::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'position' => fake()->numberBetween(1, 100),
            'department' => 'VILLE',
        ];
    }
}
