<?php

namespace Database\Factories;

use App\Models\FollowUp;
use Illuminate\Database\Eloquent\Factories\Factory;

class FollowUpFactory extends Factory
{
    protected $model = FollowUp::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'user_add' => fake()->userName(),
        ];
    }
}
