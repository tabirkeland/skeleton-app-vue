# Recipe Search 3000 Frontend

Vue 3 single-page application with Apollo Client for GraphQL integration.

## Tech Stack

- **Vue 3**: Composition API with script setup
- **Apollo Client**: GraphQL client with caching
- **Vue Router**: Client-side routing
- **Vite**: Fast build tooling
- **Vitest**: Unit testing framework

## Project Structure

```
frontend/
├── src/
│   ├── apollo/          # Apollo Client configuration
│   │   └── client.js    # GraphQL client setup
│   ├── components/      # Reusable Vue components
│   │   └── RecipeCard.vue
│   ├── graphql/         # GraphQL queries and mutations
│   │   └── queries.js   # Centralized query definitions
│   ├── router/          # Vue Router configuration
│   │   └── index.js
│   ├── views/           # Page components
│   │   ├── HomeView.vue
│   │   ├── RecipeSearchView.vue
│   │   └── RecipeDetailView.vue
│   ├── App.vue          # Root component
│   └── main.js          # Application entry point
├── public/              # Static assets
└── package.json         # Dependencies and scripts
```

## Features

### Recipe Search View
- Real-time search with debouncing (300ms)
- Multiple filter types:
  - Keyword search (name, description, ingredients, steps)
  - Ingredient filter
  - Author email filter
  - Author name filter
- Responsive grid layout with cards
- "Load More" pagination
- Loading states and error handling

### Recipe Detail View
- Full recipe display with relational data:
  - Author information
  - Ingredients list with quantities and units
  - Step-by-step instructions
  - Cooking times and servings
- Breadcrumb navigation
- Error handling with retry capability
- Clean, responsive layout

### GraphQL Integration
- Apollo Client with automatic caching
- Optimized queries with fragments
- Support for nested relational data
- Error handling and loading states
- Pagination support with cursor-based navigation

## Development Setup

### Prerequisites
- Node.js 16+ 
- npm or yarn
- Backend API running on http://localhost:8888

### Installation

```bash
# Install dependencies
npm install

# Start development server
npm run dev
```

The application will be available at http://localhost:3000

### Available Scripts

```bash
# Development server with hot reload
npm run dev

# Production build
npm run build

# Preview production build
npm run preview

# Run unit tests
npm run test:unit

# Run tests in watch mode
npm run test:unit:watch
```

## GraphQL Queries

### Search Recipes
```graphql
query SearchRecipes {
  recipes(
    keyword: "salmon"
    author_name: "Chef"
    first: 10
    page: 1
  ) {
    data {
      id
      name
      description
      slug
      primary_author {
        name
        email
      }
      ingredient_count
      step_count
    }
    paginatorInfo {
      hasMorePages
      total
    }
  }
}
```

### Get Recipe Details
```graphql
query GetRecipe($slug: String!) {
  recipe(slug: $slug) {
    id
    name
    description
    prep_time
    cook_time
    servings
    authors {
      name
      email
      about
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
}
```

## Component Architecture

### RecipeCard Component
- Displays recipe summary information
- Shows primary author
- Displays ingredient and step counts
- Links to recipe detail view
- Responsive design with hover effects

### Search Filters
- Debounced input for performance
- Clear filter buttons
- Visual feedback for active filters
- Keyboard navigation support

### Error Handling
- Network error recovery
- GraphQL error display
- Retry mechanisms
- User-friendly error messages

## State Management

Currently using component-level state with Apollo Client's cache for data management. The cache handles:
- Query result caching
- Optimistic updates
- Cache normalization
- Automatic refetching

## Styling

- Utility-first CSS approach
- Responsive design patterns
- Consistent color scheme
- Accessible contrast ratios
- Mobile-first approach

## Testing

Unit tests are configured with Vitest. Run tests with:

```bash
# Run tests once
npm run test:unit

# Watch mode for development
npm run test:unit:watch
```

## Build & Deployment

### Production Build

```bash
# Create optimized production build
npm run build

# Preview production build locally
npm run preview
```

Build output will be in the `dist/` directory.

### Environment Variables

Create a `.env` file for environment-specific configuration:

```env
VITE_GRAPHQL_ENDPOINT=http://localhost:8888/graphql
```

## Performance Optimizations

- Lazy loading of routes
- Apollo Client query caching
- Debounced search inputs
- Optimized bundle splitting
- Image lazy loading (when implemented)

## Future Enhancements

- [ ] Add unit test coverage
- [ ] Implement E2E tests
- [ ] Add recipe image display
- [ ] Implement recipe favoriting
- [ ] Add print-friendly recipe view
- [ ] Implement recipe sharing
- [ ] Add nutritional information display
- [ ] Implement advanced filtering UI
- [ ] Add recipe collections/lists
- [ ] Implement user authentication