<?php

namespace App\Actions\Recipe;

use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

class GenerateRecipeAction
{
    protected GetBatchImageUrlsAction $batchImageAction;

    public function __construct(GetBatchImageUrlsAction $batchImageAction)
    {
        $this->batchImageAction = $batchImageAction;
    }

    /**
     * Execute recipe generation with factory or provided data.
     *
     * This action provides a centralized way to generate recipes for:
     * - Console commands (GenerateRecipesCommand)
     * - Database seeders (RecipeSeeder)
     * - Tests
     *
     * @param  array  $generationOptions Configuration for generation:
     *   - data: (array|null) Specific recipe data to use
     *   - count: (int) Number of recipes to generate (default: 1)
     *   - with_relationships: (bool) Include authors, ingredients, steps (default: true)
     *   - min_ingredients: (int) Minimum ingredients per recipe (default: 3)
     *   - max_ingredients: (int) Maximum ingredients per recipe (default: 12)
     *   - min_steps: (int) Minimum steps per recipe (default: 3)
     *   - max_steps: (int) Maximum steps per recipe (default: 10)
     *   - min_authors: (int) Minimum authors per recipe (default: 1)
     *   - max_authors: (int) Maximum authors per recipe (default: 2)
     *   - image_source: (string) Image source: picsum or loremflickr (default: random)
     *   - progress_callback: (callable|null) Callback for progress updates
     * @return Recipe|array Single Recipe or array of Recipes
     */
    public function execute(array $generationOptions): mixed
    {
        $count = $generationOptions['count'] ?? 1;
        $withRelationships = $generationOptions['with_relationships'] ?? true;
        $progressCallback = $generationOptions['progress_callback'] ?? null;

        // If specific data is provided, use it
        if (isset($generationOptions['data'])) {
            $recipe = $this->generateFromData($generationOptions['data'], $withRelationships);
            // Fetch image if not provided
            if (empty($recipe->getRawOriginal('image_url'))) {
                $this->fetchImagesForRecipes([$recipe]);
            }

            return $recipe;
        }

        // Generate using factory
        $recipes = [];
        for ($i = 0; $i < $count; $i++) {
            $recipes[] = $this->generateWithFactory($generationOptions, $withRelationships);

            if ($progressCallback && is_callable($progressCallback)) {
                $progressCallback($i + 1, $count);
            }
        }

        // Fetch images for all recipes that don't have them
        $this->fetchImagesForRecipes($recipes);

        return $count === 1 ? $recipes[0] : $recipes;
    }

    /**
     * Generate recipe from specific data (for seeders).
     */
    private function generateFromData(array $data, bool $withRelationships): Recipe
    {
        return DB::transaction(function () use ($data, $withRelationships) {
            // Extract relationships
            $authors = $data['authors'] ?? [];
            $ingredients = $data['ingredients'] ?? [];
            $steps = $data['steps'] ?? [];

            // Remove relationships from main data
            unset($data['authors'], $data['ingredients'], $data['steps']);

            // Ensure category is set if not provided
            if (!isset($data['category'])) {
                $data['category'] = $this->determineCategory($data['name'] ?? '');
            }

            // Use updateOrCreate for idempotent seeding
            $recipe = Recipe::updateOrCreate(
                ['name' => $data['name']],
                $data
            );

            if ($withRelationships) {
                // Sync authors
                $recipe->authors()->delete();
                foreach ($authors as $author) {
                    $recipe->authors()->create($author);
                }

                // Sync ingredients
                $recipe->ingredients()->delete();
                foreach ($ingredients as $ingredient) {
                    $recipe->ingredients()->create($ingredient);
                }

                // Sync steps
                $recipe->steps()->delete();
                foreach ($steps as $index => $step) {
                    $step['order'] = $step['order'] ?? ($index + 1);
                    $recipe->steps()->create($step);
                }
            }

            return $recipe->load(['authors', 'ingredients', 'steps']);
        });
    }

    /**
     * Generate recipe using factory with configurable options.
     */
    private function generateWithFactory(array $options, bool $withRelationships): Recipe
    {
        $factory = Recipe::factory();

        // Don't set image_url here - we'll fetch from Pexels API after creation

        // Add relationships if requested
        if ($withRelationships) {
            $relationshipOptions = [
                'min_authors' => $options['min_authors'] ?? 1,
                'max_authors' => $options['max_authors'] ?? 2,
                'min_ingredients' => $options['min_ingredients'] ?? 3,
                'max_ingredients' => $options['max_ingredients'] ?? 12,
                'min_steps' => $options['min_steps'] ?? 3,
                'max_steps' => $options['max_steps'] ?? 10,
            ];

            $factory = $factory->complete($relationshipOptions);
        }

        return $factory->create();
    }

    /**
     * Fetch images for recipes that don't have them.
     */
    private function fetchImagesForRecipes(array $recipes): void
    {
        // Filter recipes that need images
        $recipesNeedingImages = collect($recipes)->filter(
            fn ($r) => empty($r->getRawOriginal('image_url'))
        );

        if ($recipesNeedingImages->isEmpty()) {
            return;
        }

        // Fetch images in batch
        $imageMap = $this->batchImageAction->execute($recipesNeedingImages);

        // Update each recipe with its fetched image
        foreach ($recipesNeedingImages as $recipe) {
            if (isset($imageMap[$recipe->id])) {
                $recipe->update(['image_url' => $imageMap[$recipe->id]]);
                // Refresh the model to reflect the update
                $recipe->refresh();
            }
        }
    }

    /**
     * Determine recipe category based on name.
     * This provides intelligent categorization for recipes without explicit categories.
     */
    private function determineCategory(string $name): string
    {
        $lowercaseName = strtolower($name);

        // Map keywords to categories
        $categoryMappings = [
            'Breakfast' => ['pancake', 'waffle', 'toast', 'egg', 'bacon', 'omelette', 'cereal', 'oatmeal'],
            'Dessert' => ['cookie', 'cake', 'pie', 'cheesecake', 'chocolate', 'brownie', 'ice cream', 'pudding', 'tart'],
            'Soup' => ['soup', 'chowder', 'bisque', 'broth', 'stew'],
            'Salad' => ['salad', 'slaw', 'greens'],
            'Pasta' => ['pasta', 'spaghetti', 'lasagna', 'ravioli', 'noodle', 'macaroni', 'fettuccine', 'carbonara'],
            'Seafood' => ['fish', 'salmon', 'tuna', 'shrimp', 'lobster', 'crab', 'seafood', 'clam', 'oyster'],
            'Beverage' => ['smoothie', 'shake', 'juice', 'coffee', 'tea', 'cocktail', 'mocktail', 'drink'],
            'Appetizer' => ['dip', 'wings', 'nachos', 'bruschetta', 'crostini', 'tapas'],
            'Asian' => ['stir fry', 'pad thai', 'curry', 'sushi', 'ramen', 'dim sum', 'teriyaki'],
            'Mexican' => ['taco', 'burrito', 'enchilada', 'quesadilla', 'fajita', 'salsa', 'guacamole'],
            'Italian' => ['pizza', 'risotto', 'italian', 'marinara', 'alfredo', 'parmesan'],
            'Vegetarian' => ['vegetable', 'veggie', 'tofu', 'quinoa', 'plant-based'],
            'Snack' => ['snack', 'chips', 'popcorn', 'crackers', 'nuts'],
        ];

        // Check for keyword matches
        foreach ($categoryMappings as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($lowercaseName, $keyword)) {
                    return $category;
                }
            }
        }

        // Check for cooking method keywords
        if (str_contains($lowercaseName, 'grilled') || str_contains($lowercaseName, 'bbq')) {
            return 'Main Course';
        }

        if (str_contains($lowercaseName, 'baked') || str_contains($lowercaseName, 'roasted')) {
            return 'Main Course';
        }

        // Default category
        return 'Main Course';
    }
}
