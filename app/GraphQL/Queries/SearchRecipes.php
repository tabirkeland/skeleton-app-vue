<?php

namespace App\GraphQL\Queries;

use App\Actions\Search\SearchRecipesAction;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

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
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Builder
    {
        // Pass search parameters to the action
        // The action returns a query builder that Lighthouse will paginate
        return $this->action->execute([
            'author_email' => $args['author_email'] ?? null,
            'author_name' => $args['author_name'] ?? null,
            'keyword' => $args['keyword'] ?? null,
            'ingredient' => $args['ingredient'] ?? null,
        ]);
    }
}
