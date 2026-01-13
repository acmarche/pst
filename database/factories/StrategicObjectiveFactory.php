<?php

namespace Database\Factories;

use App\Models\StrategicObjective;
use Illuminate\Database\Eloquent\Factories\Factory;

final class StrategicObjectiveFactory extends Factory
{
    protected $model = StrategicObjective::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'position' => fake()->numberBetween(1, 100),
            'department' => 'VILLE',
            'is_internal' => fake()->boolean(),
        ];
    }
}
