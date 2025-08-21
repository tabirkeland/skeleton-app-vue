# Recipe Search 3000

A GraphQL-powered recipe search application demonstrating advanced Laravel patterns including Custom Eloquent Builders and the Action pattern, with a Vue 3 frontend using Apollo Client.

## Features

- 🔍 **Advanced Recipe Search**: Search by keyword, ingredient, or author email
- 🚀 **GraphQL API**: Built with Laravel Lighthouse
- 🏗️ **Custom Eloquent Builders**: Advanced query building patterns
- 🎯 **Action Pattern**: Clean business logic separation with Action contracts
- ⚡ **Vue 3 + Apollo**: Modern reactive frontend with GraphQL integration
- ✅ **Comprehensive Testing**: 67 tests with 213 assertions
- 🗄️ **Redis Ready**: Caching infrastructure configured (implementation on hold)

## Tech Stack

- **Backend**: Laravel 10 with GraphQL (Lighthouse)
- **Frontend**: Vue 3 with Apollo Client
- **Database**: MySQL 8.0
- **Cache**: Redis (infrastructure ready)
- **Containerization**: Docker with Laravel Sail

## Getting Started

### Prerequisites
- Docker
- Docker Compose

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

- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8888/graphql
- **GraphQL Playground**: http://localhost:8888/graphql-playground (when APP_DEBUG=true)
- **MySQL**: localhost:3333
- **Redis**: localhost:6379

## Architecture

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
├── Models/          # Eloquent models
└── Services/        # Service classes (including caching)
```

### Key Patterns Demonstrated

1. **Custom Eloquent Builders**: `RecipeBuilder` provides chainable search methods
2. **Action Pattern**: All business logic encapsulated in Action classes implementing the Action contract
3. **GraphQL with Laravel**: Full GraphQL API implementation using Lighthouse
4. **Domain Exceptions**: Custom exceptions for better error handling

## Testing

Run the complete test suite:
```bash
./vendor/bin/sail artisan test
```

Run with coverage:
```bash
./vendor/bin/sail artisan test --coverage
```

Current test coverage:
- 67 total tests
- 213 assertions
- Covers Models, Builders, Actions, and GraphQL endpoints

## GraphQL API

### Query Examples

**Search recipes:**
```graphql
query SearchRecipes {
  recipes(
    keyword: "chocolate"
    ingredient: "flour"
    author_email: "chef@example.com"
  ) {
    data {
      id
      name
      description
      slug
      author_email
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
    ingredients
    steps
    author_email
  }
}
```

**Create recipe:**
```graphql
mutation CreateRecipe {
  createRecipe(input: {
    name: "New Recipe"
    description: "Delicious recipe"
    ingredients: ["ingredient 1", "ingredient 2"]
    steps: ["Step 1", "Step 2"]
    author_email: "chef@example.com"
  }) {
    id
    slug
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

# Seed database
./vendor/bin/sail artisan db:seed

# Clear caches
./vendor/bin/sail artisan cache:clear

# Run tests
./vendor/bin/sail artisan test

# Access MySQL CLI
./vendor/bin/sail mysql

# Access Redis CLI
./vendor/bin/sail redis
```

## Implementation Status

✅ **Completed:**
- Core recipe CRUD operations
- Advanced search functionality
- GraphQL API with pagination
- Vue 3 frontend with Apollo
- Comprehensive test suite
- Database seeding
- Redis infrastructure

🚧 **On Hold:**
- Redis caching implementation (service created, not actively used)

⏳ **Future Enhancements:**
- Update/Delete mutations
- Advanced error handling
- API documentation generation
- Deployment configurations
- Monitoring and observability

## Documentation

- [Implementation Guide](./docs/implementation-prompt.md)
- [Technical Specifications](./docs/technical-specifications.md)
- [Test Specifications](./docs/test-specifications.md)
- [Progress Tracking](./PROGRESS.md)

## License

This project is a demonstration of Laravel and Vue.js capabilities.