<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class RecipeBuilder extends Builder
{
    /**
     * Filter recipes by author email.
     */
    public function byAuthor(string $email): self
    {
        return $this->where('author_email', $email);
    }

    /**
     * Search across name, description, ingredients, and steps using LIKE and JSON_SEARCH.
     */
    public function searchKeyword(string $keyword): self
    {
        $searchTerm = "%{$keyword}%";

        return $this->where(function ($query) use ($searchTerm) {
            $query->where('name', 'LIKE', $searchTerm)
                ->orWhere('description', 'LIKE', $searchTerm)
                ->orWhereRaw("JSON_SEARCH(ingredients, 'one', ?) IS NOT NULL", [$searchTerm])
                ->orWhereRaw("JSON_SEARCH(steps, 'one', ?) IS NOT NULL", [$searchTerm]);
        });
    }

    /**
     * Case-insensitive ingredient matching using JSON_SEARCH.
     */
    public function withIngredient(string $ingredient): self
    {
        return $this->whereRaw(
            "JSON_SEARCH(LOWER(ingredients), 'one', LOWER(?)) IS NOT NULL",
            ["%{$ingredient}%"]
        );
    }

    /**
     * Combined search supporting AND logic across multiple filters.
     */
    public function search(array $filters): self
    {
        return $this->when(
            isset($filters['author_email']),
            fn ($query) => $query->byAuthor($filters['author_email'])
        )->when(
            isset($filters['keyword']),
            fn ($query) => $query->searchKeyword($filters['keyword'])
        )->when(
            isset($filters['ingredient']),
            fn ($query) => $query->withIngredient($filters['ingredient'])
        );
    }

    /**
     * Order by creation date (newest first).
     */
    public function popular(): self
    {
        return $this->orderBy('created_at', 'desc');
    }

    /**
     * Filter recipes created within the last N days.
     */
    public function recent(int $days = 30): self
    {
        return $this->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Add ingredient and step counts using JSON_LENGTH.
     */
    public function withCounts(): self
    {
        return $this->selectRaw('*,
            JSON_LENGTH(ingredients) as ingredient_count,
            JSON_LENGTH(steps) as step_count'
        );
    }
}
