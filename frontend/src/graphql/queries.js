import { gql } from '@apollo/client/core'

// Fragment for recipe fields
export const RECIPE_FRAGMENT = gql`
  fragment RecipeFields on Recipe {
    id
    name
    description
    ingredients
    steps
    author_email
    slug
    created_at
    updated_at
    ingredient_count
    step_count
  }
`

// Search recipes with pagination
export const SEARCH_RECIPES = gql`
  query SearchRecipes($author_email: String, $keyword: String, $ingredient: String, $first: Int, $page: Int) {
    recipes(author_email: $author_email, keyword: $keyword, ingredient: $ingredient, first: $first, page: $page) {
      data {
        ...RecipeFields
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
  ${RECIPE_FRAGMENT}
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