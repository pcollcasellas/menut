<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecipeFactory extends Factory
{
    protected $model = Recipe::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'household_id' => fn (array $attributes) => User::find($attributes['user_id'])->household_id,
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'ingredients' => fake()->paragraph(),
            'instructions' => fake()->paragraphs(2, true),
        ];
    }
}
