<?php

namespace Database\Factories;

use App\Models\Action;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ActionFactory extends Factory
{
    protected $model = Action::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'department' => 'VILLE',
            'user_add' => fake()->userName(),
        ];
    }
}
