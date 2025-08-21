<?php

namespace App\GraphQL\Queries;

use App\Models\Recipe;

class GetRecipe
{
    /**
     * Get a single recipe by slug
     */
    public function __invoke($_, array $args, $context, $info)
    {
        return Recipe::where('slug', $args['slug'])->firstOrFail();
    }
}
