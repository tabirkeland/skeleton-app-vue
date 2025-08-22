# Recipe Search 3000

A GraphQL-powered recipe search application demonstrating advanced Laravel patterns including Custom Eloquent Builders, the Action pattern, and proper relational database design with a Vue 3 frontend using Apollo Client.

## Features

-   🔍 **Advanced Recipe Search**: Search by keyword, ingredient, or author email
-   🚀 **GraphQL API**: Built with Laravel Lighthouse
-   🏗️ **Relational Database**: Properly normalized database with related tables
-   🎯 **Action Pattern**: Clean business logic separation with Action contracts
-   ⚡ **Vue 3 + Apollo**: Modern reactive frontend with GraphQL integration
-   ✅ **Comprehensive Testing**: Unit and feature tests with high coverage
-   🗄️ **Redis Ready**: Caching infrastructure configured (implementation on hold)
-   🔄 **Automatic Slug Generation**: Model-level slug generation with uniqueness handling

## Tech Stack

-   **Backend**: Laravel 10 with GraphQL (Lighthouse)
-   **Frontend**: Vue 3 with Apollo Client
-   **Database**: MySQL 8.0 with relational structure
-   **Cache**: Redis (infrastructure ready)
-   **Containerization**: Docker with Laravel Sail

## Getting Started

### Prerequisites

-   Docker
-   Docker Compose

### Installation

1. **Clone the repository**

```bash
git clone [repository-url]
cd recipe-search-3000
```

2. **Install backend dependencies**

```bash
docker run --rm \
    --pull=always \
    -v "$(pwd)":/opt \
    -w /opt \
    laravelsail/php82-composer:latest \
    bash -c "composer install"
```

3. **Set up environment**

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

4. **Seed the database**

```bash
./vendor/bin/sail artisan db:seed --class=RecipeSeeder
```

5. **Install frontend dependencies**

```bash
./vendor/bin/sail npm install --prefix frontend
```

6. **Start the frontend development server**

```bash
./vendor/bin/sail npm run dev --prefix frontend
```

### Access the Application

-   **Frontend**: http://localhost:3000 (or next available port - Vite will display the correct URL)
-   **Backend API**: http://localhost:8888/graphql
-   **GraphQL Playground**: http://localhost:8888/graphql-playground (when APP_DEBUG=true)
-   **MySQL**: localhost:3333
-   **Redis**: localhost:6379

## Database Architecture

### Relational Structure

```
recipes
├── id, name, description, slug (unique)
├── image_url, prep_time, cook_time, servings
└── timestamps

recipe_authors (1:N with recipes)
├── id, recipe_id (FK)
├── name, email, about
└── timestamps

recipe_ingredients (1:N with recipes)
├── id, recipe_id (FK)
├── name, quantity, unit
├── is_checked
└── timestamps

recipe_steps (1:N with recipes)
├── id, recipe_id (FK)
├── title, description, order
├── completed
└── timestamps
```

## Application Architecture

### Backend Structure

```
app/
├── Actions/           # Business logic actions with Action contract
│   ├── Recipe/       # Recipe-specific actions
│   └── Search/       # Search-related actions
├── Builders/         # Custom Eloquent query builders
├── Contracts/        # Interface definitions
├── Exceptions/       # Domain-specific exceptions
├── GraphQL/          # GraphQL resolvers
│   ├── Queries/     # Query resolvers
│   └── Mutations/   # Mutation resolvers
├── Models/          # Eloquent models with relationships
│   ├── Recipe.php
│   ├── RecipeAuthor.php
│   ├── RecipeIngredient.php
│   └── RecipeStep.php
└── Services/        # Service classes (including caching)
```

### Key Patterns Demonstrated

1. **Relational Database Design**: Properly normalized tables with foreign keys
2. **Custom Eloquent Builders**: `RecipeBuilder` provides chainable search methods
3. **Action Pattern**: All business logic encapsulated in Action classes
4. **Model Events**: Automatic slug generation using Laravel model events
5. **GraphQL with Laravel**: Full GraphQL API implementation using Lighthouse
6. **Domain Exceptions**: Custom exceptions for better error handling

## Testing

Run the complete test suite:

```bash
./vendor/bin/sail artisan test
```

Run specific test suites:

```bash
# Unit tests only
./vendor/bin/sail artisan test --testsuite=Unit

# Feature tests only
./vendor/bin/sail artisan test --testsuite=Feature
```

Run with coverage:

```bash
./vendor/bin/sail artisan test --coverage
```

## GraphQL API

### Query Examples

**Search recipes with filters:**

```graphql
query SearchRecipes {
    recipes(
        keyword: "chocolate"
        ingredient: "flour"
        author_email: "chef@example.com"
        first: 10
        page: 1
    ) {
        data {
            id
            name
            description
            slug
            prep_time
            cook_time
            servings
            primary_author {
                name
                email
            }
            ingredients {
                name
                quantity
                unit
                formatted
            }
            steps {
                order
                title
                description
            }
        }
        paginatorInfo {
            currentPage
            hasMorePages
            total
        }
    }
}
```

**Get single recipe:**

```graphql
query GetRecipe {
    recipe(slug: "chocolate-cake") {
        id
        name
        description
        authors {
            name
            email
            about
        }
        ingredients {
            name
            quantity
            unit
            is_checked
        }
        steps {
            order
            title
            description
            completed
        }
    }
}
```


## Development Commands

```bash
# Start services
./vendor/bin/sail up -d

# Stop services
./vendor/bin/sail down

# Run migrations
./vendor/bin/sail artisan migrate

# Fresh migration with seeding
./vendor/bin/sail artisan migrate:fresh --seed

# Seed database
./vendor/bin/sail artisan db:seed --class=RecipeSeeder

# Clear caches
./vendor/bin/sail artisan cache:clear

# Validate GraphQL schema
./vendor/bin/sail artisan lighthouse:validate-schema

# Run tests
./vendor/bin/sail artisan test

# Access MySQL CLI
./vendor/bin/sail mysql

# Access Redis CLI
./vendor/bin/sail redis

# Laravel Tinker (REPL)
./vendor/bin/sail artisan tinker
```

## Implementation Status

✅ **Completed:**

-   Relational database structure with proper normalization
-   Core recipe CRUD operations with related data
-   Advanced search functionality across relationships
-   GraphQL API with pagination and nested queries
-   Vue 3 frontend with Apollo Client
-   Automatic slug generation at model level
-   Comprehensive test suite
-   Database seeding with realistic data
-   Redis infrastructure setup

🚧 **On Hold:**

-   Redis caching implementation (service created, not actively used)

⏳ **Future Enhancements:**

-   Update/Delete mutations for recipes
-   User authentication and authorization
-   Recipe ratings and reviews
-   Recipe categories and tags
-   Image upload functionality
-   Advanced filtering (by prep time, servings, etc.)
-   Recipe collections/favorites
-   API rate limiting
-   Full-text search with Elasticsearch
-   Deployment configurations

## Documentation

-   [Implementation Status](./docs/IMPLEMENTATION_STATUS.md) - Detailed implementation tracking
-   [Database Refactor](./docs/database-refactor.md) - Database normalization documentation
-   [Frontend README](./frontend/README.md) - Frontend-specific documentation

## License

This project is a demonstration of Laravel and Vue.js capabilities.
