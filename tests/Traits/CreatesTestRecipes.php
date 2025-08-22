<?php

namespace Tests\Traits;

use App\Actions\Recipe\GenerateRecipeAction;
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
     * Uses the centralized GenerateRecipeAction for consistency.
     */
    protected function createCompleteRecipe(array $overrides = []): Recipe
    {
        $recipeData = array_merge([
            'name' => 'Test Recipe',
            'description' => 'A test recipe description',
            'category' => 'Main Course',
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

        $generateRecipeAction = app(GenerateRecipeAction::class);

        return $generateRecipeAction->execute([
            'data' => array_merge($recipeData, [
                'authors' => [$authorData],
                'ingredients' => $ingredients,
                'steps' => $steps,
            ]),
            'with_relationships' => true,
        ]);
    }

    /**
     * Create multiple recipes using the factory with FakerRestaurant.
     * Leverages the improved RecipeFactory with realistic food data.
     */
    protected function createMultipleRecipes(int $count = 5, array $options = []): array
    {
        $generateRecipeAction = app(GenerateRecipeAction::class);

        return $generateRecipeAction->execute(array_merge([
            'count' => $count,
            'with_relationships' => true,
        ], $options));
    }

    /**
     * Create a recipe using factory without relationships.
     */
    protected function createSimpleRecipe(array $overrides = []): Recipe
    {
        return Recipe::factory()->create($overrides);
    }

    /**
     * Create a recipe with factory complete method.
     * Uses the enhanced factory with FakerRestaurant data.
     */
    protected function createFactoryCompleteRecipe(array $options = []): Recipe
    {
        return Recipe::factory()->complete($options)->create();
    }
}
