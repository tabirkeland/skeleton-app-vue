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
     * Searches in: recipe.name, recipe.description, ingredients.name, steps.title
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
                    $q->where('title', 'LIKE', $searchTerm);
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
     * This is the single source of truth for search logic.
     */
    public function search(array $filters): self
    {
        // Handle author emails (supports arrays and single values)
        if (!empty($filters['author_emails'])) {
            if (is_array($filters['author_emails'])) {
                if (count($filters['author_emails']) > 1) {
                    $this->withAnyAuthor($filters['author_emails']);
                } else {
                    $this->byAuthor($filters['author_emails'][0]);
                }
            } else {
                $this->byAuthor($filters['author_emails']);
            }
        } elseif (!empty($filters['author_email'])) {
            $this->byAuthor($filters['author_email']);
        }

        // Handle author name
        if (!empty($filters['author_name'])) {
            $this->byAuthorName($filters['author_name']);
        }

        // Handle keyword search across multiple fields
        if (!empty($filters['keyword'])) {
            $this->searchKeyword($filters['keyword']);
        }

        // Handle ingredients (supports arrays and single values)
        // Multiple ingredients should be combined with AND logic per requirements
        if (!empty($filters['ingredients'])) {
            if (is_array($filters['ingredients'])) {
                if (count($filters['ingredients']) > 1) {
                    $this->withAllIngredients($filters['ingredients']);
                } else {
                    $this->withIngredient($filters['ingredients'][0]);
                }
            } else {
                $this->withIngredient($filters['ingredients']);
            }
        } elseif (!empty($filters['ingredient'])) {
            $this->withIngredient($filters['ingredient']);
        }

        return $this;
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
