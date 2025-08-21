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
            return $this->createAction->execute([
                'name' => $input['name'],
                'description' => $input['description'],
                'ingredients' => $input['ingredients'],
                'steps' => $input['steps'],
                'author_email' => $input['author_email'],
            ]);
        } catch (ValidationException $e) {
            throw new \GraphQL\Error\Error(
                'Validation failed: '.implode(', ', $e->errors()['general'] ?? ['Invalid input'])
            );
        } catch (\Exception $e) {
            throw new \GraphQL\Error\Error('Failed to create recipe: '.$e->getMessage());
        }
    }
}
