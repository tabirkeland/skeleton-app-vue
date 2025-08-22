<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class RecipeBuilder extends Builder
{
    /**
     * Search by author email through relationship.
     */
    public function byAuthor(string $email): self
    {
        return $this->whereHas('authors', function ($query) use ($email) {
            $query->where('email', $email);
        });
    }

    /**
     * Search with any of the author emails.
     */
    public function withAnyAuthor(array $emails): self
    {
        return $this->whereHas('authors', function ($query) use ($emails) {
            $query->whereIn('email', $emails);
        });
    }

    /**
     * Search by author name through relationship.
     */
    public function byAuthorName(string $name): self
    {
        return $this->whereHas('authors', function ($query) use ($name) {
            $query->where('name', 'LIKE', "%{$name}%");
        });
    }

    /**
     * Search by keyword across multiple fields.
     */
    public function searchKeyword(string $keyword): self
    {
        $searchTerm = "%{$keyword}%";

        return $this->where(function ($query) use ($searchTerm) {
            $query->where('name', 'LIKE', $searchTerm)
                ->orWhere('description', 'LIKE', $searchTerm)
                ->orWhereHas('ingredients', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', $searchTerm);
                })
                ->orWhereHas('steps', function ($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', $searchTerm)
                        ->orWhere('description', 'LIKE', $searchTerm);
                });
        });
    }

    /**
     * Search by ingredient name through relationship.
     */
    public function withIngredient(string $ingredient): self
    {
        return $this->whereHas('ingredients', function ($query) use ($ingredient) {
            $query->where('name', 'LIKE', "%{$ingredient}%");
        });
    }

    /**
     * Search with all ingredients.
     */
    public function withAllIngredients(array $ingredients): self
    {
        foreach ($ingredients as $ingredient) {
            $this->withIngredient($ingredient);
        }

        return $this;
    }

    /**
     * Search with any of the ingredients.
     */
    public function withAnyIngredient(array $ingredients): self
    {
        return $this->whereHas('ingredients', function ($query) use ($ingredients) {
            $query->where(function ($q) use ($ingredients) {
                foreach ($ingredients as $ingredient) {
                    $q->orWhere('name', 'LIKE', "%{$ingredient}%");
                }
            });
        });
    }

    /**
     * Combined search with filters.
     */
    public function search(array $filters): self
    {
        return $this->when(
            isset($filters['author_email']),
            fn ($query) => $query->byAuthor($filters['author_email'])
        )->when(
            isset($filters['author_name']),
            fn ($query) => $query->byAuthorName($filters['author_name'])
        )->when(
            isset($filters['keyword']),
            fn ($query) => $query->searchKeyword($filters['keyword'])
        )->when(
            isset($filters['ingredient']),
            fn ($query) => $query->withIngredient($filters['ingredient'])
        );
    }

    /**
     * Order by the number of steps.
     */
    public function orderByStepCount(string $direction = 'asc'): self
    {
        return $this->withCount('steps')
            ->orderBy('steps_count', $direction);
    }

    /**
     * Order by the number of ingredients.
     */
    public function orderByIngredientCount(string $direction = 'asc'): self
    {
        return $this->withCount('ingredients')
            ->orderBy('ingredients_count', $direction);
    }

    /**
     * Popular recipes (ordered by creation date desc).
     */
    public function popular(): self
    {
        return $this->orderBy('created_at', 'desc');
    }

    /**
     * Recent recipes.
     */
    public function recent(int $days = 7): self
    {
        return $this->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Add ingredient and step counts.
     */
    public function withCounts(): self
    {
        return $this->withCount(['ingredients', 'steps']);
    }
}
