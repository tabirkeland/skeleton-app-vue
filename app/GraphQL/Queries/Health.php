<?php

namespace App\GraphQL\Queries;

class Health
{
    /**
     * Simple health check resolver
     */
    public function resolve($rootValue, array $args, $context, $info): string
    {
        return 'GraphQL server is healthy!';
    }
}
