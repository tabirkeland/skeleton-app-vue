<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecipeFactory extends Factory
{
    protected $model = Recipe::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'name' => ucwords($name),
            'description' => $this->faker->paragraph(2),
            'ingredients' => $this->generateIngredients(),
            'steps' => $this->generateSteps(),
            'author_email' => $this->faker->email,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function generateIngredients(): array
    {
        $commonIngredients = [
            'flour', 'sugar', 'butter', 'eggs', 'milk', 'vanilla extract',
            'baking powder', 'salt', 'olive oil', 'garlic', 'onion',
            'tomatoes', 'cheese', 'chicken', 'beef', 'rice', 'pasta',
            'herbs', 'spices', 'lemon', 'pepper', 'cream', 'chocolate',
            'carrots', 'potatoes', 'broth', 'wine', 'bread crumbs',
        ];

        $count = $this->faker->numberBetween(3, 8);

        return $this->faker->randomElements($commonIngredients, $count);
    }

    private function generateSteps(): array
    {
        $stepTemplates = [
            'Preheat oven to 350°F',
            'Mix dry ingredients in a large bowl',
            'Combine wet ingredients separately',
            'Gradually add wet ingredients to dry ingredients',
            'Pour mixture into prepared pan',
            'Bake for 25-30 minutes',
            'Let cool before serving',
            'Heat oil in a large pan',
            'Add onions and cook until translucent',
            'Add garlic and cook for 1 minute',
            'Season with salt and pepper',
            'Simmer for 15-20 minutes',
            'Serve hot and enjoy',
        ];

        $count = $this->faker->numberBetween(3, 6);

        return $this->faker->randomElements($stepTemplates, $count);
    }

    public function withIngredient(string $ingredient): self
    {
        return $this->state(function (array $attributes) use ($ingredient) {
            $ingredients = $attributes['ingredients'] ?? $this->generateIngredients();
            if (! in_array($ingredient, $ingredients)) {
                $ingredients[] = $ingredient;
            }

            return ['ingredients' => $ingredients];
        });
    }

    public function withAuthor(string $email): self
    {
        return $this->state(['author_email' => $email]);
    }

    public function withName(string $name): self
    {
        return $this->state([
            'name' => $name,
        ]);
    }
}
