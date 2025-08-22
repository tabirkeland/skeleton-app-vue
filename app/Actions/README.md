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
├── Search/
│   └── SearchRecipesAction.php   # Handles recipe search with filters
└── README.md                      # This documentation
```

## Implementation Examples

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

Actions are injected into GraphQL resolvers via dependency injection. For example, the SearchRecipes query uses SearchRecipesAction to handle complex recipe filtering.

## Testing

Each action has comprehensive unit tests in `tests/Unit/Actions/`:

- `SearchRecipesActionTest.php` - 14 tests covering search functionality

## Benefits

1. **Testability**: Actions can be tested in isolation
2. **Reusability**: Actions can be called from controllers, commands, or jobs
3. **Maintainability**: Business logic is centralized and organized
4. **Consistency**: All actions follow the same interface contract
5. **Separation of Concerns**: Clear boundary between validation and business logic