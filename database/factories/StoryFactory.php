<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'body' => fake()->paragraphs(3, true),
            'category' => 'ghost',
            'status' => 'visible',
            'author_id' => User::factory(),
            'location_id' => Location::factory(),
        ];
    }
}
