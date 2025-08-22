<?php

namespace App\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use App\Actions\Search\SearchRecipesAction;

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
