<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingCollectionFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->unique()->words(3, true), 'user_id' => User::factory()];
    }
}
