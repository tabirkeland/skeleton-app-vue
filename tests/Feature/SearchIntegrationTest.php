<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\RecipeAuthor;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the exact search scenario provided by the user:
     * email: foo@bar.com
     * ingredient: potato
     * keyword: scallop
     *
     * This should match all recipes that:
     * - Have an author email of foo@bar.com
     * - AND have potato as an ingredient
     * - AND have the keyword scallop in name, description, ingredients, or steps
     */
    public function test_user_specific_search_scenario(): void
    {
        // Create recipes to test all combinations

        // Recipe 1: MATCHES - Has all three criteria
        $recipe1 = Recipe::factory()->create([
            'name' => 'Pan-Seared Scallops with Potato Puree',
            'description' => 'Delicious scallops served over creamy potato puree',
        ]);
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe1->id,
            'email' => 'foo@bar.com',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe1->id,
            'name' => 'potato',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe1->id,
            'name' => 'scallop',
        ]);

        // Recipe 2: NO MATCH - Missing keyword "scallop"
        $recipe2 = Recipe::factory()->create([
            'name' => 'Chicken and Potato Casserole',
            'description' => 'Hearty chicken dish with potatoes',
        ]);
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe2->id,
            'email' => 'foo@bar.com',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe2->id,
            'name' => 'potato',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe2->id,
            'name' => 'chicken',
        ]);

        // Recipe 3: NO MATCH - Missing potato ingredient
        $recipe3 = Recipe::factory()->create([
            'name' => 'Grilled Scallops with Rice',
            'description' => 'Perfectly grilled scallops over jasmine rice',
        ]);
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe3->id,
            'email' => 'foo@bar.com',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe3->id,
            'name' => 'rice',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe3->id,
            'name' => 'scallop',
        ]);

        // Recipe 4: NO MATCH - Wrong email
        $recipe4 = Recipe::factory()->create([
            'name' => 'Scallop and Potato Skillet',
            'description' => 'Quick and easy scallop dish',
        ]);
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe4->id,
            'email' => 'different@email.com',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe4->id,
            'name' => 'potato',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe4->id,
            'name' => 'scallop',
        ]);

        // Recipe 5: MATCHES - Has keyword "scallop" only in description
        $recipe5 = Recipe::factory()->create([
            'name' => 'Seafood Medley',
            'description' => 'A mix of shrimp, scallop, and fish with potatoes',
        ]);
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe5->id,
            'email' => 'foo@bar.com',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe5->id,
            'name' => 'potato',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe5->id,
            'name' => 'shrimp',
        ]);

        // Recipe 6: MATCHES - Has keyword "scallop" only in steps
        $recipe6 = Recipe::factory()->create([
            'name' => 'Seafood Surprise',
            'description' => 'A delightful seafood dish',
        ]);
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe6->id,
            'email' => 'foo@bar.com',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe6->id,
            'name' => 'potato',
        ]);
        RecipeStep::factory()->create([
            'recipe_id' => $recipe6->id,
            'title' => 'Prepare the scallop topping',
            'description' => 'Slice scallops thinly',
        ]);

        // Recipe 7: NO MATCH - Has all criteria but different case email (emails are case-insensitive, so this SHOULD match)
        $recipe7 = Recipe::factory()->create([
            'name' => 'Scallop Special',
            'description' => 'Amazing scallops',
        ]);
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe7->id,
            'email' => 'FOO@BAR.COM',  // Different case
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe7->id,
            'name' => 'Potato',  // Different case
        ]);

        // Test via GraphQL query
        $query = '
            query SearchRecipes($email: String, $ingredient: String, $keyword: String) {
                recipes(
                    author_email: $email,
                    ingredient: $ingredient,
                    keyword: $keyword,
                    first: 10
                ) {
                    data {
                        id
                        name
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ';

        $response = $this->postJson('/graphql', [
            'query' => $query,
            'variables' => [
                'email' => 'foo@bar.com',
                'ingredient' => 'potato',
                'keyword' => 'scallop',
            ],
        ]);

        $response->assertJsonStructure([
            'data' => [
                'recipes' => [
                    'data' => [
                        '*' => ['id', 'name'],
                    ],
                    'paginatorInfo' => ['total'],
                ],
            ],
        ]);

        $data = $response->json('data.recipes.data');
        $total = $response->json('data.recipes.paginatorInfo.total');

        // Should find exactly 4 recipes (recipe1, recipe5, recipe6, recipe7)
        $this->assertEquals(4, $total, 'Should find exactly 4 recipes matching all criteria');

        $foundNames = array_column($data, 'name');
        $this->assertContains('Pan-Seared Scallops with Potato Puree', $foundNames);
        $this->assertContains('Seafood Medley', $foundNames);
        $this->assertContains('Seafood Surprise', $foundNames);
        $this->assertContains('Scallop Special', $foundNames);

        // These should NOT be found
        $this->assertNotContains('Chicken and Potato Casserole', $foundNames);
        $this->assertNotContains('Grilled Scallops with Rice', $foundNames);
        $this->assertNotContains('Scallop and Potato Skillet', $foundNames);
    }

    /**
     * Test that the search correctly handles partial matches
     */
    public function test_partial_ingredient_matching(): void
    {
        // Recipe with "sweet potato"
        $recipe1 = Recipe::factory()->create([
            'name' => 'Scallop Dish',
            'description' => 'With vegetables',
        ]);
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe1->id,
            'email' => 'foo@bar.com',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe1->id,
            'name' => 'sweet potato',  // Contains "potato"
        ]);

        // Recipe with "potato chips"
        $recipe2 = Recipe::factory()->create([
            'name' => 'Scallop Appetizer',
            'description' => 'Party food',
        ]);
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe2->id,
            'email' => 'foo@bar.com',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe2->id,
            'name' => 'potato chips',  // Contains "potato"
        ]);

        // Search for "potato" should match both
        $query = '
            query SearchRecipes($email: String, $ingredient: String, $keyword: String) {
                recipes(
                    author_email: $email,
                    ingredient: $ingredient,
                    keyword: $keyword,
                    first: 10
                ) {
                    data {
                        name
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ';

        $response = $this->postJson('/graphql', [
            'query' => $query,
            'variables' => [
                'email' => 'foo@bar.com',
                'ingredient' => 'potato',
                'keyword' => 'scallop',
            ],
        ]);

        $total = $response->json('data.recipes.paginatorInfo.total');
        $this->assertEquals(2, $total, 'Should find both recipes with partial potato match');
    }
}
