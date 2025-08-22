<?php

namespace App\Actions\Recipe;

use App\Models\Recipe;
use App\Services\PexelApiClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GetBatchImageUrlsAction
{
    public function __construct(private PexelApiClient $pexelClient)
    {
    }

    /**
     * Get image URLs for multiple recipes in a batch.
     * Minimizes API calls by deduplicating search queries.
     *
     * @param  Collection|array  $recipes Collection or array of Recipe models
     * @return array<int, string> Map of recipe ID => image URL
     */
    public function execute(Collection|array $recipes): array
    {
        $recipes = collect($recipes);

        // Check cache for the entire batch first
        $cacheKey = $this->getBatchCacheKey($recipes);
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        // Separate recipes that already have images from those that need them
        // Use getRawOriginal to avoid triggering the accessor
        $recipesWithImages = $recipes->filter(fn ($r) => !empty($r->getRawOriginal('image_url')));
        $recipesNeedingImages = $recipes->filter(fn ($r) => empty($r->getRawOriginal('image_url')));

        // Start with existing images
        $imageMap = $recipesWithImages->mapWithKeys(fn ($r) => [$r->id => $r->getRawOriginal('image_url')])->all();

        if ($recipesNeedingImages->isEmpty()) {
            return $imageMap;
        }

        // Build search queries for recipes without images
        $recipeQueries = $this->buildRecipeQueries($recipesNeedingImages);

        // Get unique queries to minimize API calls
        $uniqueQueries = $recipeQueries->unique()->values()->all();

        // Fetch images for all unique queries in batch
        $queryResults = $this->fetchImagesForQueries($uniqueQueries);

        // Map results back to recipes
        $recipesNeedingImages->each(function ($recipe) use ($recipeQueries, $queryResults, &$imageMap) {
            $query = $recipeQueries->get($recipe->id);
            $normalizedQuery = $this->normalizeQuery($query);

            $imageUrl = data_get($queryResults, "{$normalizedQuery}.photos.0.src.medium");
            $imageMap[$recipe->id] = $imageUrl ?: $this->getDefaultPlaceholder($recipe);
        });

        // Cache the entire batch result for 24 hours
        Cache::put($cacheKey, $imageMap, now()->addHours(24));

        return $imageMap;
    }

    /**
     * Build search queries for recipes based on their attributes.
     *
     * @param  Collection  $recipes
     * @return Collection Map of recipe ID => search query
     */
    protected function buildRecipeQueries(Collection $recipes): Collection
    {
        return $recipes->mapWithKeys(function ($recipe) {
            $query = $this->buildSearchQuery($recipe);

            return [$recipe->id => $query];
        });
    }

    /**
     * Build search query from recipe attributes.
     * Same logic as GetRecipeImageUrlAction for consistency.
     */
    protected function buildSearchQuery(Recipe $recipe): string
    {
        $queryParts = collect();

        // Extract main food items from recipe name first
        $nameKeywords = $this->extractFoodKeywords($recipe->name);
        if ($nameKeywords) {
            $queryParts->push($nameKeywords);
        }

        // Map categories to better search terms for Pexels
        $categoryMap = [
            'Breakfast' => 'breakfast food',
            'Lunch' => 'lunch meal',
            'Dinner' => 'dinner plate',
            'Dessert' => 'dessert sweet',
            'Appetizer' => 'appetizer starter',
            'Salad' => 'fresh salad',
            'Soup' => 'soup bowl',
            'Main Course' => 'main dish',
            'Pasta' => 'pasta dish',
            'Seafood' => 'seafood dish',
            'Vegetarian' => 'vegetarian meal',
            'Vegan' => 'vegan food',
            'Asian' => 'asian cuisine',
            'Italian' => 'italian food',
            'Mexican' => 'mexican food',
        ];

        // Add mapped category term if available
        if ($recipe->category && isset($categoryMap[$recipe->category])) {
            $queryParts->push($categoryMap[$recipe->category]);
        }

        // If we don't have enough keywords, add generic food term
        if ($queryParts->count() < 2) {
            $queryParts->push('food');
        }

        // Combine query parts, prioritizing specific terms
        $query = $queryParts->unique()->implode(' ');

        // Limit query length for API (Pexels works better with shorter, focused queries)
        return Str::limit($query, 50, '');
    }

    /**
     * Extract food-related keywords from recipe name.
     */
    protected function extractFoodKeywords(string $name): string
    {
        // Remove common non-food words
        $exclude = collect(['the', 'and', 'with', 'for', 'recipe', 'homemade', 'best', 'easy', 'quick']);

        return Str::of($name)
            ->lower()
            ->explode(' ')
            ->filter() // Removes empty strings
            ->diff($exclude)
            ->take(3)
            ->implode(' ');
    }

    /**
     * Fetch images for multiple queries efficiently.
     *
     * @param  array  $queries
     * @return array
     */
    protected function fetchImagesForQueries(array $queries): array
    {
        // Configure search options for food photography
        $options = [
            'per_page' => 5,  // Get a few options per query
            'orientation' => 'landscape',  // Better for recipe cards
            'size' => 'medium',  // Good balance of quality and performance
        ];

        // Use the batch search method from PexelApiClient
        return $this->pexelClient->batchSearchPhotos($queries, $options);
    }

    /**
     * Generate batch cache key based on recipe IDs and attributes.
     *
     * @param  Collection  $recipes
     * @return string
     */
    protected function getBatchCacheKey(Collection $recipes): string
    {
        // Create a hash of recipe IDs and key attributes that affect image selection
        $key = $recipes->map(fn ($r) => "{$r->id}:{$r->name}:{$r->category}")
            ->sort()
            ->implode('|');

        return 'batch_recipe_images_'.md5($key);
    }

    /**
     * Normalize a query for consistent matching.
     *
     * @param  string  $query
     * @return string
     */
    protected function normalizeQuery(string $query): string
    {
        return Str::of($query)
            ->trim()
            ->lower()
            ->replaceMatches('/\s+/', ' ')
            ->value();
    }

    /**
     * Get default placeholder URL.
     *
     * @param  Recipe  $recipe
     * @return string
     */
    protected function getDefaultPlaceholder(Recipe $recipe): string
    {
        $text = urlencode($recipe->name);

        return "https://placehold.co/640x480?text={$text}";
    }
}
