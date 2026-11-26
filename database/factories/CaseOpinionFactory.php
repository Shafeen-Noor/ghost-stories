<?php

namespace Database\Factories;

use App\Models\CaseOpinion;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaseOpinionFactory extends Factory
{
    public function definition(): array
    {
        return ['story_id' => Story::factory(), 'user_id' => User::factory(), 'choice' => fake()->randomElement(array_keys(CaseOpinion::CHOICES)), 'revision' => str_repeat('0', 40)];
    }
}
