<?php

namespace App\Actions\Recipe;

use App\Models\Recipe;
use App\Contracts\Action;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class SearchRecipesAction implements Action
{
    /**
     * Create a new SearchRecipesAction instance.
     */
    public function __construct(protected Recipe $recipe) { }

    /**
     * Execute recipe search with GraphQL-validated parameters.
     *
     * Returns a query builder for Lighthouse's @paginate directive.
     * Lighthouse will handle the actual pagination.
     *
     * @param  array  $parameters Search parameters already validated by GraphQL
     * @return Builder Query builder for Lighthouse to paginate
     */
    public function execute(array $parameters = []): mixed
    {
        // Prepare and normalize search filters
        $filters = $this->prepareSearchFilters($parameters);

        // Build and return the search query
        return $this->buildSearchQuery($filters);
    }

    /**
     * Prepare and normalize search filters from GraphQL-validated parameters.
     *
     * Focuses on business logic for search optimization and data normalization.
     * Parameters are already validated by GraphQL - this handles formatting.
     *
     * @param  array  $parameters GraphQL-validated search parameters
     *
     * @return array Normalized filters ready for query building
     */
    protected function prepareSearchFilters(array $parameters): array
    {
        $filters = [];

        // Normalize author email(s) for consistent searching
        if (!empty($parameters['author_email'])) {
            $filters['author_emails'] = $this->normalizeEmails($parameters['author_email']);
        }

        // Normalize keyword for better search matching
        if (!empty($parameters['keyword'])) {
            $filters['keyword'] = $this->normalizeSearchKeyword($parameters['keyword']);
        }

        // Normalize ingredient(s) for consistent ingredient matching
        if (!empty($parameters['ingredient'])) {
            $filters['ingredients'] = $this->normalizeIngredients($parameters['ingredient']);
        }

        return $filters;
    }

    /**
     * Normalize a single value based on type.
     *
     * @param string $value The value to normalize
     * @param bool $lowercase Whether to convert to lowercase
     * @return string The normalized value
     */
    private function normalize(string $value, bool $lowercase = false): string
    {
        return Str::of(trim($value))
            ->when($lowercase, fn ($str) => $str->lower())
            ->value();
    }

    /**
     * Normalize comma-separated values into an array.
     *
     * @param string $values Comma-separated values
     * @param bool $lowercase Whether to convert each value to lowercase
     * @return array Normalized array of values
     */
    private function normalizeMultiple(string $values, bool $lowercase = false): array
    {
        return collect(explode(',', $values))
            ->map(fn ($value) => $this->normalize($value, $lowercase))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Normalize emails (supports comma-separated values).
     */
    private function normalizeEmails(string $emails): array
    {
        return $this->normalizeMultiple($emails, true);
    }

    /**
     * Normalize search keyword for optimal matching.
     */
    private function normalizeSearchKeyword(string $keyword): string
    {
        return $this->normalize($keyword, false);
    }

    /**
     * Normalize ingredients (supports comma-separated values).
     */
    private function normalizeIngredients(string $ingredients): array
    {
        return $this->normalizeMultiple($ingredients, true);
    }

    /**
     * Build the search query with the given filters.
     * Returns a query builder for Lighthouse to paginate.
     */
    protected function buildSearchQuery(array $filters): Builder
    {
        $query = $this->recipe->query();

        // Apply all search filters through RecipeBuilder's unified search method
        if (!empty($filters)) {
            $query = $query->search($filters);
        }

        // Apply consistent ordering and eager loading
        return $query->popular()
            ->withCounts()
            ->with(['authors', 'ingredients', 'steps']);
    }
}
