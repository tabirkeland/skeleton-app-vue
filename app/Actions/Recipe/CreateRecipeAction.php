<?php

namespace App\Actions\Recipe;

use App\Contracts\Action;
use App\Exceptions\RecipeCreationException;
use App\Models\Recipe;
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
     * @return Recipe The created recipe instance with all relationships
     *
     * @throws RecipeCreationException When database operations fail
     */
    public function execute(array $validatedData = []): mixed
    {
        try {
            // Use transaction for data integrity
            return DB::transaction(function () use ($validatedData) {
                // Prepare and create the main recipe
                $recipeData = $this->prepareRecipeData($validatedData);
                $recipe = $this->recipe->create($recipeData);

                // Create related authors
                if (isset($validatedData['authors'])) {
                    foreach ($validatedData['authors'] as $authorData) {
                        $recipe->authors()->create($this->prepareAuthorData($authorData));
                    }
                }

                // Create related ingredients
                if (isset($validatedData['ingredients'])) {
                    foreach ($validatedData['ingredients'] as $ingredientData) {
                        $recipe->ingredients()->create($this->prepareIngredientData($ingredientData));
                    }
                }

                // Create related steps
                if (isset($validatedData['steps'])) {
                    foreach ($validatedData['steps'] as $index => $stepData) {
                        $stepData['order'] = $stepData['order'] ?? ($index + 1);
                        $recipe->steps()->create($this->prepareStepData($stepData));
                    }
                }

                // Load all relationships before returning
                return $recipe->load(['authors', 'ingredients', 'steps']);
            });
        } catch (\Exception $e) {
            // Log detailed error information for debugging
            logger()->error('Recipe creation failed', [
                'recipe_data' => $validatedData,
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
     * The Recipe model handles slug generation automatically.
     *
     * @param  array  $data GraphQL-validated input data
     * @return array Normalized data ready for database storage
     */
    private function prepareRecipeData(array $data): array
    {
        $preparedData = [
            'name' => $this->normalizeRecipeName($data['name']),
            'description' => trim($data['description']),
        ];

        // Only include slug if explicitly provided (let model handle auto-generation otherwise)
        if (isset($data['slug'])) {
            $preparedData['slug'] = $data['slug'];
        }

        // Add optional fields if present
        if (isset($data['image_url'])) {
            $preparedData['image_url'] = trim($data['image_url']);
        }

        if (isset($data['prep_time'])) {
            $preparedData['prep_time'] = (int) $data['prep_time'];
        }

        if (isset($data['cook_time'])) {
            $preparedData['cook_time'] = (int) $data['cook_time'];
        }

        if (isset($data['servings'])) {
            $preparedData['servings'] = (int) $data['servings'];
        }

        return $preparedData;
    }

    /**
     * Prepare author data for storage.
     */
    private function prepareAuthorData(array $data): array
    {
        $preparedData = [
            'name' => trim($data['name']),
            'email' => $this->normalizeEmail($data['email']),
        ];

        if (isset($data['about'])) {
            $preparedData['about'] = trim($data['about']);
        }

        return $preparedData;
    }

    /**
     * Prepare ingredient data for storage.
     */
    private function prepareIngredientData(array $data): array
    {
        $preparedData = [
            'name' => trim($data['name']),
            'quantity' => $data['quantity'] ?? 1,
        ];

        if (isset($data['unit'])) {
            $preparedData['unit'] = trim($data['unit']);
        }

        $preparedData['is_checked'] = $data['is_checked'] ?? false;

        return $preparedData;
    }

    /**
     * Prepare step data for storage.
     */
    private function prepareStepData(array $data): array
    {
        $preparedData = [
            'description' => trim($data['description']),
            'order' => (int) ($data['order'] ?? 1),
            'completed' => $data['completed'] ?? false,
        ];

        if (isset($data['title'])) {
            $preparedData['title'] = trim($data['title']);
        }

        return $preparedData;
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
     * Normalize email address for consistent storage.
     */
    private function normalizeEmail(string $email): string
    {
        return trim(strtolower($email));
    }
}
