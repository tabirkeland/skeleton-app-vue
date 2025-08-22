<?php

namespace App\GraphQL\Queries;

use App\Actions\Recipe\SearchRecipesAction;
use Illuminate\Database\Eloquent\Builder;

class SearchRecipes
{
    /**
     * Create a new SearchRecipes instance.
     *
     * @return void
     */
    public function __construct(protected SearchRecipesAction $action)
    {
    }

    /**
     * Build the search query for recipes.
     * This is called by Lighthouse's @paginate directive with the builder argument.
     * It expects standard GraphQL resolver arguments.
     *
     * @param  mixed  $root
     */
    public function __invoke($root, array $args): Builder
    {
        return $this->action->execute($args);
    }
}
