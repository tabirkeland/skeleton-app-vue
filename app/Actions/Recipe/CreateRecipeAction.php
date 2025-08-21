<?php

namespace App\Actions\Recipe;

use App\Contracts\Action;
use App\Models\Recipe;
use App\Exceptions\RecipeCreationException;
use Illuminate\Support\Facades\DB;

class CreateRecipeAction implements Action
{
    public function __construct(
        private Recipe $recipe
    ) {
    }

    /**
     * Execute recipe creation with GraphQL-validated data.
     * 
     * GraphQL schema validation ensures data integrity before reaching this point.
     * This action focuses on business logic, data transformation, and persistence.
     *
     * @param  array  $validatedData Recipe data already validated by GraphQL schema
     * @return Recipe The created recipe instance
     *
     * @throws RecipeCreationException When database operations fail
     */
    public function execute(array $validatedData = []): mixed
    {
        // Transform and normalize validated data for storage
        $recipeData = $this->prepareRecipeData($validatedData);

        try {
            // Use transaction for data integrity
            return DB::transaction(function () use ($recipeData) {
                return $this->recipe->create($recipeData);
            });
        } catch (\Exception $e) {
            // Log detailed error information for debugging
            logger()->error('Recipe creation failed', [
                'recipe_data' => $recipeData,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);

            throw new RecipeCreationException(
                'Unable to create recipe. Please try again later.',
                previous: $e
            );
        }
    }

    /**
     * Prepare validated data for database storage.
     * 
     * Applies business rules and data transformation/normalization.
     * Data is already validated by GraphQL schema - this focuses on formatting.
     *
     * @param  array  $data GraphQL-validated input data
     * @return array Normalized data ready for database storage
     */
    private function prepareRecipeData(array $data): array
    {
        return [
            'name' => $this->normalizeRecipeName($data['name']),
            'description' => trim($data['description']),
            'ingredients' => $this->normalizeIngredients($data['ingredients']),
            'steps' => $this->normalizeSteps($data['steps']),
            'author_email' => $this->normalizeEmail($data['author_email']),
        ];
    }

    /**
     * Normalize recipe name for consistent formatting.
     */
    private function normalizeRecipeName(string $name): string
    {
        // Remove excessive whitespace and normalize spacing
        return trim(preg_replace('/\s+/', ' ', $name));
    }

    /**
     * Normalize ingredients list.
     */
    private function normalizeIngredients(array $ingredients): array
    {
        return array_map(fn ($ingredient) => trim($ingredient), $ingredients);
    }

    /**
     * Normalize cooking steps.
     */
    private function normalizeSteps(array $steps): array
    {
        return array_map(fn ($step) => trim($step), $steps);
    }

    /**
     * Normalize email address for consistent storage.
     */
    private function normalizeEmail(string $email): string
    {
        return trim(strtolower($email));
    }
}
