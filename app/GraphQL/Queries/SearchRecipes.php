<?php

namespace App\GraphQL\Queries;

use App\Actions\Search\SearchRecipesAction;

class SearchRecipes
{
    protected $searchAction;

    public function __construct(SearchRecipesAction $searchAction)
    {
        $this->searchAction = $searchAction;
    }

    /**
     * Search recipes with filters and pagination
     */
    public function __invoke($_, array $args, $context, $info)
    {
        return $this->searchAction->execute([
            'author_email' => $args['author_email'] ?? null,
            'keyword' => $args['keyword'] ?? null,
            'ingredient' => $args['ingredient'] ?? null,
            'page' => $args['page'] ?? 1,
            'perPage' => $args['first'] ?? 15,
        ]);
    }
}
