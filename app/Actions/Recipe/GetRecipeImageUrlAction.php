<?php

namespace App\Actions\Recipe;

use App\Models\Recipe;
use App\Services\PexelApiClient;
use Illuminate\Support\Facades\Cache;

class GetRecipeImageUrlAction
{
    protected PexelApiClient $pexelClient;

    public function __construct(PexelApiClient $pexelClient)
    {
        $this->pexelClient = $pexelClient;
    }

    /**
     * Get image URL for a recipe
     *
     * @param  Recipe  $recipe The recipe to get an image URL for
     * @return string The image URL
     */
    public function execute(Recipe $recipe): string
    {
        // If recipe already has an image URL, return it (use raw attribute to avoid accessor recursion)
        if (!empty($recipe->image_url)) {
            return $recipe->image_url;
        }

        // Generate cache key based on recipe attributes
        $cacheKey = $this->getCacheKey($recipe);

        // Try to get from cache
        return Cache::remember(
            $cacheKey,
            config('recipes.image_url_cache_time'),
            fn () => $this->fetchImageUrl($recipe)
        );
    }

    /**
     * Generate cache key for the recipe
     */
    protected function getCacheKey(Recipe $recipe): string
    {
        // Use recipe ID if available, otherwise use slug
        $identifier = $recipe->id ?? $recipe->slug ?? md5($recipe->name);

        return "recipe_image_url_{$identifier}";
    }

    /**
     * Fetch image URL from Pexels API
     */
    protected function fetchImageUrl(Recipe $recipe): string
    {
        // Build search query from recipe attributes
        $query = $this->buildSearchQuery($recipe);

        // Configure search options for food photography
        $options = [
            'per_page' => 3,  // Get a few options to pick from
            'orientation' => 'landscape',  // Better for recipe cards
            'size' => 'medium',  // Good balance of quality and performance
        ];

        // Search for photos
        $response = $this->pexelClient->searchPhotos($query, $options);

        // Extract and return the first photo URL
        if ($response && isset($response['photos'][0]['src']['medium'])) {
            return $response['photos'][0]['src']['medium'];
        }

        // Fallback to placeholder if no results
        return $this->getDefaultPlaceholder($recipe);
    }

    /**
     * Build search query from recipe attributes
     */
    protected function buildSearchQuery(Recipe $recipe): string
    {
        $queryParts = [];

        // Extract main food items from recipe name first
        $nameKeywords = $this->extractFoodKeywords($recipe->name);
        if ($nameKeywords) {
            $queryParts[] = $nameKeywords;
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
            $queryParts[] = $categoryMap[$recipe->category];
        }

        // If we don't have enough keywords, add generic food term
        if (count($queryParts) < 2) {
            $queryParts[] = 'food';
        }

        // Combine query parts, prioritizing specific terms
        $query = implode(' ', array_unique($queryParts));

        // Limit query length for API (Pexels works better with shorter, focused queries)
        return substr($query, 0, 50);
    }

    /**
     * Extract food-related keywords from recipe name
     */
    protected function extractFoodKeywords(string $name): string
    {
        // Remove common non-food words
        $stopWords = ['the', 'and', 'with', 'for', 'recipe', 'homemade', 'best', 'easy', 'quick'];

        $words = explode(' ', strtolower($name));
        $keywords = array_diff($words, $stopWords);

        // Take first 3 keywords
        $keywords = array_slice($keywords, 0, 3);

        return implode(' ', $keywords);
    }

    /**
     * Get default placeholder URL
     */
    protected function getDefaultPlaceholder(Recipe $recipe): string
    {
        $text = urlencode($recipe->name);

        return "https://placehold.co/640x480?text={$text}";
    }
}
