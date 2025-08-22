<?php

namespace App\Actions\Search;

use App\Contracts\Action;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Builder;

class SearchRecipesAction implements Action
{
    /**
     * Create a new SearchRecipesAction instance.
     */
    public function __construct(
        protected Recipe $recipe
    ) {
    }

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
     * @return array Normalized filters ready for query building
     */
    protected function prepareSearchFilters(array $parameters): array
    {
        $filters = [];

        // Normalize author email(s) for consistent searching
        if (!empty($parameters['author_email'])) {
            $filters['author_emails'] = $this->normalizeEmails($parameters['author_email']);
        }

        // Normalize author name for consistent searching
        if (!empty($parameters['author_name'])) {
            $filters['author_name'] = $this->normalizeSearchKeyword($parameters['author_name']);
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
     * Normalize email for consistent searching.
     */
    private function normalizeEmail(string $email): string
    {
        return trim(strtolower($email));
    }

    /**
     * Normalize emails (supports comma-separated values).
     */
    private function normalizeEmails(string $emails): array
    {
        // Split by comma and normalize each email
        return array_filter(
            array_map(
                fn ($email) => $this->normalizeEmail($email),
                explode(',', $emails)
            )
        );
    }

    /**
     * Normalize search keyword for optimal matching.
     */
    private function normalizeSearchKeyword(string $keyword): string
    {
        // Trim whitespace and normalize for search
        return trim($keyword);
    }

    /**
     * Normalize ingredient name for consistent matching.
     */
    private function normalizeIngredient(string $ingredient): string
    {
        // Trim and normalize case for ingredient searching
        return trim(strtolower($ingredient));
    }

    /**
     * Normalize ingredients (supports comma-separated values).
     */
    private function normalizeIngredients(string $ingredients): array
    {
        // Split by comma and normalize each ingredient
        return array_filter(
            array_map(
                fn ($ingredient) => $this->normalizeIngredient($ingredient),
                explode(',', $ingredients)
            )
        );
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
        $query = $query->popular()
            ->withCounts()
            ->with(['authors', 'ingredients', 'steps']);

        // Return the query builder for Lighthouse to paginate
        return $query;
    }
}
