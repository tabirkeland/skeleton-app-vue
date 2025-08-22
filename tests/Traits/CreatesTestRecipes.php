<?php

namespace Tests\Traits;

use App\Models\Recipe;

trait CreatesTestRecipes
{
    /**
     * Create a recipe with specific ingredients.
     */
    protected function createRecipeWithIngredients(array $recipeData, array $ingredients): Recipe
    {
        $recipe = Recipe::factory()->create($recipeData);

        foreach ($ingredients as $ingredient) {
            $recipe->ingredients()->create([
                'name' => $ingredient['name'],
                'quantity' => $ingredient['quantity'] ?? 1,
                'unit' => $ingredient['unit'] ?? null,
                'is_checked' => $ingredient['is_checked'] ?? false,
            ]);
        }

        return $recipe;
    }

    /**
     * Create a recipe with specific steps.
     */
    protected function createRecipeWithSteps(array $recipeData, array $steps): Recipe
    {
        $recipe = Recipe::factory()->create($recipeData);

        foreach ($steps as $index => $step) {
            $recipe->steps()->create([
                'title' => $step['title'] ?? null,
                'description' => $step['description'],
                'order' => $step['order'] ?? ($index + 1),
                'completed' => $step['completed'] ?? false,
            ]);
        }

        return $recipe;
    }

    /**
     * Create a recipe with an author.
     */
    protected function createRecipeWithAuthor(array $recipeData, array $authorData): Recipe
    {
        $recipe = Recipe::factory()->create($recipeData);

        $recipe->authors()->create([
            'name' => $authorData['name'],
            'email' => $authorData['email'],
            'about' => $authorData['about'] ?? null,
        ]);

        return $recipe;
    }

    /**
     * Create a complete recipe with all relationships.
     */
    protected function createCompleteRecipe(array $overrides = []): Recipe
    {
        $recipeData = array_merge([
            'name' => 'Test Recipe',
            'description' => 'A test recipe description',
        ], $overrides['recipe'] ?? []);

        $authorData = array_merge([
            'name' => 'Test Author',
            'email' => 'test@example.com',
        ], $overrides['author'] ?? []);

        $ingredients = $overrides['ingredients'] ?? [
            ['name' => 'ingredient1', 'quantity' => 1, 'unit' => 'cup'],
            ['name' => 'ingredient2', 'quantity' => 2, 'unit' => 'tbsp'],
        ];

        $steps = $overrides['steps'] ?? [
            ['description' => 'Step 1', 'order' => 1],
            ['description' => 'Step 2', 'order' => 2],
        ];

        $recipe = Recipe::factory()->create($recipeData);

        $recipe->authors()->create($authorData);

        foreach ($ingredients as $ingredient) {
            $recipe->ingredients()->create($ingredient);
        }

        foreach ($steps as $step) {
            $recipe->steps()->create($step);
        }

        return $recipe;
    }
}
