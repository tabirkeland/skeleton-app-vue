<?php

namespace App\Actions\Search;

use App\Contracts\Action;
use App\Models\Recipe;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchRecipesAction implements Action
{
    /**
     * @var int
     */
    public const DEFAULT_PAGE = 1;

    /**
     * @var int
     */
    public const DEFAULT_PER_PAGE = 15;

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
     * GraphQL Lighthouse handles parameter validation through query arguments.
     * This action focuses on search business logic and result optimization.
     *
     * @param  array  $parameters Search parameters already validated by GraphQL
     * @return LengthAwarePaginator Paginated search results
     */
    public function execute(array $parameters = []): mixed
    {
        // Extract pagination parameters with defaults
        $page = $parameters['page'] ?? self::DEFAULT_PAGE;
        $perPage = $parameters['perPage'] ?? self::DEFAULT_PER_PAGE;

        // Prepare and normalize search filters
        $filters = $this->prepareSearchFilters($parameters);

        // Execute the search with business logic applied
        return $this->performSearch($filters, $page, $perPage);
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
        if (! empty($parameters['author_email'])) {
            $filters['author_emails'] = $this->normalizeEmails($parameters['author_email']);
        }

        // Normalize author name for consistent searching
        if (! empty($parameters['author_name'])) {
            $filters['author_name'] = $this->normalizeSearchKeyword($parameters['author_name']);
        }

        // Normalize keyword for better search matching
        if (! empty($parameters['keyword'])) {
            $filters['keyword'] = $this->normalizeSearchKeyword($parameters['keyword']);
        }

        // Normalize ingredient(s) for consistent ingredient matching
        if (! empty($parameters['ingredient'])) {
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
     * Perform the search with the given filters and pagination.
     */
    protected function performSearch(array $filters, int $page, int $perPage): LengthAwarePaginator
    {
        $query = $this->recipe->query();

        // Apply search filters using the RecipeBuilder's search method
        if (! empty($filters)) {
            // Handle multiple ingredients separately
            if (isset($filters['ingredients'])) {
                $ingredients = $filters['ingredients'];
                unset($filters['ingredients']);

                // Use withAnyIngredient for multiple ingredients (OR logic)
                if (count($ingredients) > 1) {
                    $query = $query->withAnyIngredient($ingredients);
                } else {
                    $query = $query->withIngredient($ingredients[0]);
                }
            }

            // Handle multiple author emails separately
            if (isset($filters['author_emails'])) {
                $emails = $filters['author_emails'];
                unset($filters['author_emails']);

                // Use withAnyAuthor for multiple emails (OR logic)
                if (count($emails) > 1) {
                    $query = $query->withAnyAuthor($emails);
                } else {
                    $query = $query->byAuthor($emails[0]);
                }
            }

            // Apply other filters
            if (! empty($filters)) {
                $query = $query->search($filters);
            }
        }

        // Order by creation date (newest first) for consistent results
        $query = $query->popular();

        // Add ingredient and step counts for better GraphQL response
        $query = $query->withCounts();

        // Load relationships for GraphQL
        $query = $query->with(['authors', 'ingredients', 'steps']);

        // Execute pagination
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Check if any search parameters are provided.
     */
    public function hasSearchCriteria(array $parameters): bool
    {
        return ! empty($parameters['author_email']) ||
               ! empty($parameters['author_name']) ||
               ! empty($parameters['keyword']) ||
               ! empty($parameters['ingredient']);
    }
}
