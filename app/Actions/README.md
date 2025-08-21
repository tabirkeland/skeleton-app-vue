# Action Pattern Implementation

## Overview

This directory contains Action classes that implement the Command pattern, demonstrating advanced Laravel architecture patterns. All actions implement the `App\Contracts\Action` interface, ensuring consistency and maintainability.

## Contract Definition

All actions must implement the `Action` interface:

```php
interface Action
{
    public function execute(array $parameters = []): mixed;
}
```

## Key Principles

1. **Single Responsibility**: Each action handles one specific business operation
2. **Validation Separation**: Input validation happens at the GraphQL layer, actions focus on business logic
3. **Dependency Injection**: Actions receive dependencies through constructor injection
4. **Transaction Safety**: Database operations are wrapped in transactions where appropriate
5. **Error Handling**: Actions throw domain-specific exceptions for better error management

## Directory Structure

```
app/Actions/
├── Recipe/
│   └── CreateRecipeAction.php    # Creates new recipes with slug generation
├── Search/
│   └── SearchRecipesAction.php   # Handles recipe search with filters
└── README.md                      # This documentation
```

## Implementation Examples

### Creating a Recipe

```php
class CreateRecipeAction implements Action
{
    public function __construct(private Recipe $recipe) {}
    
    public function execute(array $parameters = []): mixed
    {
        // Business logic for recipe creation
        // - Data normalization
        // - Slug generation
        // - Transaction handling
    }
}
```

### Searching Recipes

```php
class SearchRecipesAction implements Action
{
    public function __construct(protected Recipe $recipe) {}
    
    public function execute(array $parameters = []): mixed
    {
        // Business logic for recipe search
        // - Filter normalization
        // - Query building via RecipeBuilder
        // - Pagination
    }
}
```

## Usage in GraphQL Resolvers

Actions are injected into GraphQL resolvers via dependency injection:

```php
class CreateRecipe
{
    public function __construct(CreateRecipeAction $createAction)
    {
        $this->createAction = $createAction;
    }
    
    public function __invoke($rootValue, array $args)
    {
        return $this->createAction->execute($args['input']);
    }
}
```

## Testing

Each action has comprehensive unit tests in `tests/Unit/Actions/`:

- `CreateRecipeActionTest.php` - 10 tests covering creation scenarios
- `SearchRecipesActionTest.php` - 14 tests covering search functionality

## Benefits

1. **Testability**: Actions can be tested in isolation
2. **Reusability**: Actions can be called from controllers, commands, or jobs
3. **Maintainability**: Business logic is centralized and organized
4. **Consistency**: All actions follow the same interface contract
5. **Separation of Concerns**: Clear boundary between validation and business logic