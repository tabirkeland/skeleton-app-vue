<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecipeIngredient>
 */
class RecipeIngredientFactory extends Factory
{
    protected $model = \App\Models\RecipeIngredient::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $commonIngredients = [
            'flour', 'sugar', 'butter', 'eggs', 'milk', 'vanilla extract',
            'baking powder', 'salt', 'olive oil', 'garlic', 'onion',
            'tomatoes', 'cheese', 'chicken', 'beef', 'rice', 'pasta',
            'herbs', 'spices', 'lemon', 'pepper', 'cream', 'chocolate',
            'carrots', 'potatoes', 'broth', 'wine', 'bread crumbs',
        ];

        return [
            'recipe_id' => \App\Models\Recipe::factory(),
            'name' => $this->faker->randomElement($commonIngredients),
            'quantity' => $this->faker->randomFloat(2, 0.5, 5),
            'unit' => $this->faker->optional(0.7)->randomElement(['cups', 'tbsp', 'tsp', 'oz', 'lbs', 'g', 'kg', 'ml', 'l']),
            'is_checked' => false,
        ];
    }
}
