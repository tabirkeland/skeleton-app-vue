<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\RecipeAuthor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecipeAuthor>
 */
class RecipeAuthorFactory extends Factory
{
    protected $model = RecipeAuthor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipe_id' => Recipe::factory(),
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'about' => $this->faker->optional()->paragraph,
        ];
    }
}
