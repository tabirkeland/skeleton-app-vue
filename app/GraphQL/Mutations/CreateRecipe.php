<?php

namespace App\GraphQL\Mutations;

use App\Actions\Recipe\CreateRecipeAction;
use App\Models\Recipe;
use Illuminate\Validation\ValidationException;

class CreateRecipe
{
    protected $createAction;

    public function __construct(CreateRecipeAction $createAction)
    {
        $this->createAction = $createAction;
    }

    /**
     * Create a new recipe
     */
    public function __invoke($rootValue, array $args, $context, $info)
    {
        $input = $args['input'];

        try {
            // Pass the entire input array to the action
            // The action will handle the new relational structure
            return $this->createAction->execute($input);
        } catch (ValidationException $e) {
            throw new \GraphQL\Error\Error(
                'Validation failed: '.implode(', ', $e->errors()['general'] ?? ['Invalid input'])
            );
        } catch (\Exception $e) {
            throw new \GraphQL\Error\Error('Failed to create recipe: '.$e->getMessage());
        }
    }
}
