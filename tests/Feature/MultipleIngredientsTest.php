<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\RecipeAuthor;
use App\Models\RecipeIngredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultipleIngredientsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that searching with multiple ingredients uses AND logic
     * (recipe must have ALL specified ingredients)
     */
    public function test_multiple_ingredients_search_uses_and_logic(): void
    {
        // Recipe 1: Has flour AND sugar AND eggs
        $recipe1 = Recipe::factory()->create([
            'name' => 'Chocolate Cake',
            'description' => 'Delicious chocolate cake'
        ]);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe1->id, 'name' => 'flour']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe1->id, 'name' => 'sugar']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe1->id, 'name' => 'eggs']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe1->id, 'name' => 'chocolate']);
        
        // Recipe 2: Has flour AND sugar but NO eggs
        $recipe2 = Recipe::factory()->create([
            'name' => 'Sugar Cookies',
            'description' => 'Simple sugar cookies'
        ]);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe2->id, 'name' => 'flour']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe2->id, 'name' => 'sugar']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe2->id, 'name' => 'butter']);
        
        // Recipe 3: Has flour AND eggs but NO sugar
        $recipe3 = Recipe::factory()->create([
            'name' => 'Pasta Dough',
            'description' => 'Fresh pasta dough'
        ]);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe3->id, 'name' => 'flour']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe3->id, 'name' => 'eggs']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe3->id, 'name' => 'salt']);
        
        // Recipe 4: Has only flour
        $recipe4 = Recipe::factory()->create([
            'name' => 'Flour Tortillas',
            'description' => 'Simple flour tortillas'
        ]);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe4->id, 'name' => 'flour']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe4->id, 'name' => 'water']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe4->id, 'name' => 'salt']);
        
        // Test searching for recipes with flour AND sugar AND eggs
        $query = '
            query SearchRecipes($ingredient: String) {
                recipes(
                    ingredient: $ingredient,
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
                'ingredient' => 'flour,sugar,eggs'  // Multiple ingredients comma-separated
            ]
        ]);
        
        $response->assertStatus(200);
        
        $data = $response->json('data.recipes.data');
        $total = $response->json('data.recipes.paginatorInfo.total');
        
        // Only Recipe 1 (Chocolate Cake) should be found
        $this->assertEquals(1, $total, 'Should find exactly 1 recipe with ALL three ingredients');
        $this->assertEquals($recipe1->id, $data[0]['id']);
        $this->assertEquals('Chocolate Cake', $data[0]['name']);
    }
    
    /**
     * Test searching with two ingredients
     */
    public function test_search_with_two_ingredients(): void
    {
        // Recipe 1: Has both butter AND milk
        $recipe1 = Recipe::factory()->create(['name' => 'Butter Cake']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe1->id, 'name' => 'butter']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe1->id, 'name' => 'milk']);
        
        // Recipe 2: Has butter but NO milk
        $recipe2 = Recipe::factory()->create(['name' => 'Butter Cookies']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe2->id, 'name' => 'butter']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe2->id, 'name' => 'flour']);
        
        // Recipe 3: Has milk but NO butter
        $recipe3 = Recipe::factory()->create(['name' => 'Milk Shake']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe3->id, 'name' => 'milk']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe3->id, 'name' => 'ice cream']);
        
        // Recipe 4: Has both butter AND milk
        $recipe4 = Recipe::factory()->create(['name' => 'Creamy Pasta']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe4->id, 'name' => 'butter']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe4->id, 'name' => 'milk']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe4->id, 'name' => 'pasta']);
        
        $query = '
            query SearchRecipes($ingredient: String) {
                recipes(
                    ingredient: $ingredient,
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
                'ingredient' => 'butter,milk'  // Search for recipes with BOTH
            ]
        ]);
        
        $response->assertStatus(200);
        
        $data = $response->json('data.recipes.data');
        $total = $response->json('data.recipes.paginatorInfo.total');
        
        // Should find Recipe 1 and Recipe 4 (both have butter AND milk)
        $this->assertEquals(2, $total, 'Should find exactly 2 recipes with both butter AND milk');
        
        $names = array_column($data, 'name');
        $this->assertContains('Butter Cake', $names);
        $this->assertContains('Creamy Pasta', $names);
        $this->assertNotContains('Butter Cookies', $names);
        $this->assertNotContains('Milk Shake', $names);
    }
    
    /**
     * Test the exact example from the user:
     * Multiple search criteria including multiple ingredients
     */
    public function test_combined_search_with_multiple_ingredients(): void
    {
        // Recipe that matches ALL criteria
        $recipe1 = Recipe::factory()->create([
            'name' => 'Seafood Pasta with Scallops',
            'description' => 'Delicious seafood pasta'
        ]);
        RecipeAuthor::factory()->create(['recipe_id' => $recipe1->id, 'email' => 'foo@bar.com']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe1->id, 'name' => 'potato']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe1->id, 'name' => 'tomato']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe1->id, 'name' => 'scallops']);
        
        // Recipe missing tomato
        $recipe2 = Recipe::factory()->create([
            'name' => 'Scallop and Potato Dish',
            'description' => 'Simple scallop dish'
        ]);
        RecipeAuthor::factory()->create(['recipe_id' => $recipe2->id, 'email' => 'foo@bar.com']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe2->id, 'name' => 'potato']);
        RecipeIngredient::factory()->create(['recipe_id' => $recipe2->id, 'name' => 'scallops']);
        
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
        
        // Search with multiple ingredients
        $response = $this->postJson('/graphql', [
            'query' => $query,
            'variables' => [
                'email' => 'foo@bar.com',
                'ingredient' => 'potato,tomato',  // Multiple ingredients
                'keyword' => 'scallop'
            ]
        ]);
        
        $response->assertStatus(200);
        
        $data = $response->json('data.recipes.data');
        $total = $response->json('data.recipes.paginatorInfo.total');
        
        // Only recipe1 should match (has all ingredients AND keyword AND author)
        $this->assertEquals(1, $total, 'Should find only recipe with ALL criteria');
        $this->assertEquals('Seafood Pasta with Scallops', $data[0]['name']);
    }
}