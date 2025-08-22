<?php

namespace Tests\Unit\Builders;

use App\Models\Recipe;
use App\Models\RecipeAuthor;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeBuilderAndLogicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that search uses AND logic for email + ingredient + keyword combination
     */
    public function test_search_uses_and_logic_for_combined_parameters(): void
    {
        // Create test recipes with specific combinations
        
        // Recipe 1: Has all three criteria (should be found)
        $recipe1 = Recipe::factory()->create([
            'name' => 'Scallop Pasta',
            'description' => 'Delicious seafood pasta'
        ]);
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe1->id,
            'email' => 'foo@bar.com'
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe1->id,
            'name' => 'potato'
        ]);
        
        // Recipe 2: Has email and ingredient, but no keyword "scallop" (should NOT be found)
        $recipe2 = Recipe::factory()->create([
            'name' => 'Chicken Stew',
            'description' => 'Hearty chicken and vegetable stew'
        ]);
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe2->id,
            'email' => 'foo@bar.com'
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe2->id,
            'name' => 'potato'
        ]);
        
        // Recipe 3: Has email and keyword, but no potato (should NOT be found)
        $recipe3 = Recipe::factory()->create([
            'name' => 'Scallop Salad',
            'description' => 'Fresh scallops with greens'
        ]);
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe3->id,
            'email' => 'foo@bar.com'
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe3->id,
            'name' => 'lettuce'
        ]);
        
        // Recipe 4: Has ingredient and keyword, but different email (should NOT be found)
        $recipe4 = Recipe::factory()->create([
            'name' => 'Scallop and Potato Gratin',
            'description' => 'Creamy scallop dish'
        ]);
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe4->id,
            'email' => 'other@example.com'
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe4->id,
            'name' => 'potato'
        ]);
        
        // Recipe 5: Has keyword in ingredient name (should be found if keyword searches ingredients)
        $recipe5 = Recipe::factory()->create([
            'name' => 'Seafood Platter',
            'description' => 'Mixed seafood dish'
        ]);
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe5->id,
            'email' => 'foo@bar.com'
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe5->id,
            'name' => 'potato'
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe5->id,
            'name' => 'scallop'
        ]);
        
        // Recipe 6: Has keyword in step title (should be found if keyword searches steps)
        $recipe6 = Recipe::factory()->create([
            'name' => 'Seafood Medley',
            'description' => 'Various seafood'
        ]);
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe6->id,
            'email' => 'foo@bar.com'
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe6->id,
            'name' => 'potato'
        ]);
        RecipeStep::factory()->create([
            'recipe_id' => $recipe6->id,
            'title' => 'Prepare the scallop garnish'
        ]);
        
        // Execute search with all three parameters
        $filters = [
            'author_emails' => ['foo@bar.com'],
            'keyword' => 'scallop',
            'ingredients' => ['potato']
        ];
        
        $results = Recipe::query()->search($filters)->get();
        
        // Assert only recipes with ALL three criteria are found
        $this->assertCount(3, $results, 'Should find exactly 3 recipes matching all criteria');
        
        $foundIds = $results->pluck('id')->toArray();
        $this->assertContains($recipe1->id, $foundIds, 'Recipe 1 with all criteria should be found');
        $this->assertNotContains($recipe2->id, $foundIds, 'Recipe 2 without keyword should NOT be found');
        $this->assertNotContains($recipe3->id, $foundIds, 'Recipe 3 without potato should NOT be found');
        $this->assertNotContains($recipe4->id, $foundIds, 'Recipe 4 with different email should NOT be found');
        $this->assertContains($recipe5->id, $foundIds, 'Recipe 5 with keyword in ingredient should be found');
        $this->assertContains($recipe6->id, $foundIds, 'Recipe 6 with keyword in step should be found');
    }
    
    /**
     * Test that multiple author emails use OR logic (recipe by any of the authors)
     */
    public function test_multiple_author_emails_use_or_logic(): void
    {
        // Recipe with first author
        $recipe1 = Recipe::factory()->create();
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe1->id,
            'email' => 'author1@example.com'
        ]);
        
        // Recipe with second author
        $recipe2 = Recipe::factory()->create();
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe2->id,
            'email' => 'author2@example.com'
        ]);
        
        // Recipe with different author
        $recipe3 = Recipe::factory()->create();
        RecipeAuthor::factory()->create([
            'recipe_id' => $recipe3->id,
            'email' => 'other@example.com'
        ]);
        
        // Search for recipes with either author1 OR author2
        $filters = [
            'author_emails' => ['author1@example.com', 'author2@example.com']
        ];
        
        $results = Recipe::query()->search($filters)->get();
        
        // Both recipe1 and recipe2 should be found (OR logic)
        $this->assertCount(2, $results);
        $foundIds = $results->pluck('id')->toArray();
        $this->assertContains($recipe1->id, $foundIds);
        $this->assertContains($recipe2->id, $foundIds);
        $this->assertNotContains($recipe3->id, $foundIds);
    }
    
    /**
     * Test that multiple ingredients use AND logic (recipe must have all ingredients)
     */
    public function test_multiple_ingredients_use_and_logic(): void
    {
        // Recipe with both ingredients
        $recipe1 = Recipe::factory()->create();
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe1->id,
            'name' => 'flour'
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe1->id,
            'name' => 'sugar'
        ]);
        
        // Recipe with only flour
        $recipe2 = Recipe::factory()->create();
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe2->id,
            'name' => 'flour'
        ]);
        
        // Recipe with only sugar
        $recipe3 = Recipe::factory()->create();
        RecipeIngredient::factory()->create([
            'recipe_id' => $recipe3->id,
            'name' => 'sugar'
        ]);
        
        // Search for recipes with BOTH ingredients
        $filters = [
            'ingredients' => ['flour', 'sugar']
        ];
        
        $results = Recipe::query()->search($filters)->get();
        
        // Only recipe1 should be found (has both ingredients)
        $this->assertCount(1, $results);
        $this->assertEquals($recipe1->id, $results->first()->id);
    }
}