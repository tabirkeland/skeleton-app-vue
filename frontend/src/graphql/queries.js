import { gql } from '@apollo/client/core'

// Fragment for author fields
export const AUTHOR_FRAGMENT = gql`
  fragment AuthorFields on RecipeAuthor {
    id
    name
    email
    about
  }
`

// Fragment for ingredient fields
export const INGREDIENT_FRAGMENT = gql`
  fragment IngredientFields on RecipeIngredient {
    id
    name
    quantity
    unit
    is_checked
    formatted
  }
`

// Fragment for step fields
export const STEP_FRAGMENT = gql`
  fragment StepFields on RecipeStep {
    id
    title
    description
    order
    completed
  }
`

// Fragment for recipe fields
export const RECIPE_FRAGMENT = gql`
  fragment RecipeFields on Recipe {
    id
    name
    description
    slug
    image_url
    prep_time
    cook_time
    servings
    authors {
      ...AuthorFields
    }
    ingredients {
      ...IngredientFields
    }
    steps {
      ...StepFields
    }
    primary_author {
      ...AuthorFields
    }
    author_email
    ingredient_count
    step_count
    created_at
    updated_at
  }
  ${AUTHOR_FRAGMENT}
  ${INGREDIENT_FRAGMENT}
  ${STEP_FRAGMENT}
`

// Search recipes with pagination
export const SEARCH_RECIPES = gql`
  query SearchRecipes($author_email: String, $author_name: String, $keyword: String, $ingredient: String, $first: Int, $page: Int) {
    recipes(author_email: $author_email, author_name: $author_name, keyword: $keyword, ingredient: $ingredient, first: $first, page: $page) {
      data {
        id
        name
        description
        slug
        image_url
        prep_time
        cook_time
        servings
        primary_author {
          name
          email
        }
        ingredients {
          name
        }
        author_email
        ingredient_count
        step_count
        created_at
      }
      paginatorInfo {
        count
        total
        currentPage
        lastPage
        hasMorePages
        perPage
      }
    }
  }
`

// Get single recipe by slug
export const GET_RECIPE = gql`
  query GetRecipe($slug: String!) {
    recipe(slug: $slug) {
      ...RecipeFields
    }
  }
  ${RECIPE_FRAGMENT}
`

// Create new recipe mutation
export const CREATE_RECIPE = gql`
  mutation CreateRecipe($input: CreateRecipeInput!) {
    createRecipe(input: $input) {
      ...RecipeFields
    }
  }
  ${RECIPE_FRAGMENT}
`

// Health check query for testing
export const HEALTH_CHECK = gql`
  query HealthCheck {
    health
  }
`