<?php

namespace Tests\Unit\Actions;

use App\Actions\Search\SearchRecipesAction;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchRecipesActionTest extends TestCase
{
    use RefreshDatabase;

    private SearchRecipesAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new SearchRecipesAction(new Recipe());
    }

    /**
     * @test
     */
    public function it_returns_query_builder()
    {
        Recipe::factory()->count(15)->create();

        $result = $this->action->execute([]);

        $this->assertInstanceOf(Builder::class, $result);
        $this->assertEquals(15, $result->count());
    }

    /**
     * @test
     */
    public function it_filters_by_author_email()
    {
        $recipe1 = Recipe::factory()->create();
        $recipe1->authors()->delete();
        $recipe1->authors()->create(['name' => 'Chef 1', 'email' => 'chef@example.com']);

        $recipe2 = Recipe::factory()->create();
        $recipe2->authors()->delete();
        $recipe2->authors()->create(['name' => 'Chef 2', 'email' => 'chef@example.com']);

        $recipe3 = Recipe::factory()->create();
        $recipe3->authors()->delete();
        $recipe3->authors()->create(['name' => 'Other', 'email' => 'other@example.com']);

        $query = $this->action->execute(['author_email' => 'chef@example.com']);
        $result = $query->get();

        $this->assertEquals(2, $result->count());
        foreach ($result as $recipe) {
            $this->assertEquals('chef@example.com', $recipe->author_email);
        }
    }

    /**
     * @test
     */
    public function it_filters_by_keyword()
    {
        Recipe::factory()->create(['name' => 'Chocolate Cake']);
        Recipe::factory()->create(['name' => 'Vanilla Cake']);
        Recipe::factory()->create(['name' => 'Apple Pie']);

        $query = $this->action->execute(['keyword' => 'Cake']);
        $result = $query->get();

        $this->assertEquals(2, $result->count());
    }

    /**
     * @test
     */
    public function it_filters_by_ingredient()
    {
        $recipe1 = Recipe::factory()->create();
        $recipe1->ingredients()->delete();
        $recipe1->ingredients()->create(['name' => 'chocolate', 'quantity' => 1]);

        $recipe2 = Recipe::factory()->create();
        $recipe2->ingredients()->delete();
        $recipe2->ingredients()->create(['name' => 'vanilla', 'quantity' => 1]);

        $recipe3 = Recipe::factory()->create();
        $recipe3->ingredients()->delete();
        $recipe3->ingredients()->create(['name' => 'chocolate chips', 'quantity' => 2]);

        $query = $this->action->execute(['ingredient' => 'chocolate']);
        $result = $query->get();

        $this->assertEquals(2, $result->count());
    }

    /**
     * @test
     */
    public function it_combines_multiple_filters()
    {
        $recipe1 = Recipe::factory()->create(['name' => 'Chocolate Cake']);
        $recipe1->authors()->delete();
        $recipe1->ingredients()->delete();
        $recipe1->authors()->create(['name' => 'Chef', 'email' => 'chef@example.com']);
        $recipe1->ingredients()->create(['name' => 'chocolate', 'quantity' => 1]);

        $recipe2 = Recipe::factory()->create(['name' => 'Vanilla Cake']);
        $recipe2->authors()->delete();
        $recipe2->ingredients()->delete();
        $recipe2->authors()->create(['name' => 'Chef', 'email' => 'chef@example.com']);
        $recipe2->ingredients()->create(['name' => 'vanilla', 'quantity' => 1]);

        $recipe3 = Recipe::factory()->create(['name' => 'Chocolate Pie']);
        $recipe3->authors()->delete();
        $recipe3->ingredients()->delete();
        $recipe3->authors()->create(['name' => 'Other', 'email' => 'other@example.com']);
        $recipe3->ingredients()->create(['name' => 'chocolate', 'quantity' => 1]);

        $query = $this->action->execute([
            'author_email' => 'chef@example.com',
            'keyword' => 'Chocolate',
            'ingredient' => 'chocolate',
        ]);
        $result = $query->get();

        $this->assertEquals(1, $result->count());
        $this->assertEquals('Chocolate Cake', $result->first()->name);
    }

    /**
     * @test
     */
    public function it_returns_query_builder_for_pagination()
    {
        Recipe::factory()->count(20)->create();

        $query = $this->action->execute([]);

        // Test that the query builder can be paginated
        $paginated = $query->paginate(5);
        $this->assertEquals(5, $paginated->perPage());
        $this->assertEquals(5, $paginated->count());
        $this->assertEquals(20, $paginated->total());
    }

    /**
     * @test
     */
    public function it_normalizes_email_filter_to_lowercase()
    {
        $recipe = Recipe::factory()->create();
        $recipe->authors()->create(['name' => 'Chef', 'email' => 'chef@example.com']);

        $query = $this->action->execute(['author_email' => 'CHEF@EXAMPLE.COM']);
        $result = $query->get();

        $this->assertEquals(1, $result->count());
    }

    /**
     * @test
     */
    public function it_orders_results_by_popularity()
    {
        $older = Recipe::factory()->create(['created_at' => now()->subDays(2)]);
        $newer = Recipe::factory()->create(['created_at' => now()->subDay()]);
        $newest = Recipe::factory()->create(['created_at' => now()]);

        $query = $this->action->execute([]);
        $result = $query->get();

        $this->assertEquals($newest->id, $result[0]->id);
        $this->assertEquals($newer->id, $result[1]->id);
        $this->assertEquals($older->id, $result[2]->id);
    }

    /**
     * @test
     */
    public function it_returns_empty_results_when_no_matches()
    {
        Recipe::factory()->count(5)->create();

        $query = $this->action->execute(['keyword' => 'NonExistentKeyword']);
        $result = $query->get();

        $this->assertEquals(0, $result->count());
        $this->assertEmpty($result);
    }

    /**
     * @test
     */
    public function it_handles_empty_filters_array()
    {
        Recipe::factory()->count(3)->create();

        $query = $this->action->execute([]);
        $result = $query->get();

        $this->assertEquals(3, $result->count());
    }

    /**
     * @test
     */
    public function it_ignores_null_filter_values()
    {
        Recipe::factory()->count(3)->create();

        $query = $this->action->execute([
            'author_email' => null,
            'keyword' => null,
            'ingredient' => null,
        ]);
        $result = $query->get();

        $this->assertEquals(3, $result->count());
    }

    /**
     * @test
     */
    public function it_handles_special_characters_in_keyword_search()
    {
        Recipe::factory()->create(['name' => 'Recipe with Special Characters & Symbols!']);
        Recipe::factory()->create(['name' => 'Normal Recipe']);

        $query = $this->action->execute(['keyword' => 'Special Characters & Symbols']);
        $result = $query->get();

        $this->assertEquals(1, $result->count());
    }

    /**
     * @test
     */
    public function it_returns_query_builder_with_filters()
    {
        $recipes = [];
        for ($i = 1; $i <= 10; $i++) {
            $recipe = Recipe::factory()->create(['name' => "Chocolate Recipe $i"]);
            $recipe->authors()->create(['name' => 'Chef', 'email' => 'chef@example.com']);
        }

        $query = $this->action->execute([
            'author_email' => 'chef@example.com',
            'keyword' => 'Chocolate',
        ]);

        // Test that the filtered query can be paginated
        $paginated = $query->paginate(3, ['*'], 'page', 2);

        $this->assertEquals(3, $paginated->perPage());
        $this->assertEquals(2, $paginated->currentPage());
        $this->assertEquals(10, $paginated->total());
        $this->assertEquals(3, $paginated->count());
    }

    /**
     * @test
     */
    public function it_applies_all_three_filters_with_and_logic()
    {
        // Recipe that matches all filters
        $match = Recipe::factory()->create(['name' => 'Chocolate Cake']);
        $match->authors()->delete();
        $match->ingredients()->delete();
        $match->authors()->create(['name' => 'Chef', 'email' => 'chef@example.com']);
        $match->ingredients()->create(['name' => 'chocolate', 'quantity' => 1]);

        // Recipe that only matches two filters
        $partial1 = Recipe::factory()->create(['name' => 'Chocolate Pie']);
        $partial1->authors()->delete();
        $partial1->ingredients()->delete();
        $partial1->authors()->create(['name' => 'Other', 'email' => 'other@example.com']);
        $partial1->ingredients()->create(['name' => 'chocolate', 'quantity' => 1]);

        // Recipe that only matches one filter
        $partial2 = Recipe::factory()->create(['name' => 'Vanilla Cake']);
        $partial2->authors()->delete();
        $partial2->ingredients()->delete();
        $partial2->authors()->create(['name' => 'Chef', 'email' => 'chef@example.com']);
        $partial2->ingredients()->create(['name' => 'vanilla', 'quantity' => 1]);

        $query = $this->action->execute([
            'author_email' => 'chef@example.com',
            'keyword' => 'Chocolate',
            'ingredient' => 'chocolate',
        ]);
        $result = $query->get();

        $this->assertEquals(1, $result->count());
        $this->assertEquals('Chocolate Cake', $result->first()->name);
    }

    /**
     * @test
     */
    public function it_handles_multiple_author_emails_with_comma_separation()
    {
        $recipe1 = Recipe::factory()->create(['name' => 'Recipe 1']);
        $recipe1->authors()->create(['name' => 'Chef 1', 'email' => 'chef1@example.com']);

        $recipe2 = Recipe::factory()->create(['name' => 'Recipe 2']);
        $recipe2->authors()->create(['name' => 'Chef 2', 'email' => 'chef2@example.com']);

        $recipe3 = Recipe::factory()->create(['name' => 'Recipe 3']);
        $recipe3->authors()->create(['name' => 'Chef 3', 'email' => 'chef3@example.com']);

        $query = $this->action->execute([
            'author_email' => 'chef1@example.com,chef3@example.com',
        ]);
        $result = $query->get();

        $this->assertEquals(2, $result->count());
        $names = $result->pluck('name')->toArray();
        $this->assertEqualsCanonicalizing(['Recipe 1', 'Recipe 3'], $names);
    }

    /**
     * @test
     * @deprecated This test validates the old OR behavior for multiple ingredients
     * The new requirement is to use AND logic - see it_handles_multiple_ingredients_with_and_logic
     */
    public function it_handles_multiple_ingredients_with_comma_separation_old_behavior()
    {
        // This test is kept for historical reference but should be removed
        // once the AND logic is fully validated
        $this->markTestSkipped('Old OR behavior test - keeping for reference');
    }

    /**
     * @test
     */
    public function it_handles_multiple_ingredients_with_and_logic()
    {
        // Recipe with both potato AND tomato
        $recipe1 = Recipe::factory()->create(['name' => 'Mixed Vegetable Stew']);
        $recipe1->ingredients()->create(['name' => 'potatoes', 'quantity' => 3]);
        $recipe1->ingredients()->create(['name' => 'tomatoes', 'quantity' => 2]);
        $recipe1->ingredients()->create(['name' => 'onions', 'quantity' => 1]);

        // Recipe with only potato
        $recipe2 = Recipe::factory()->create(['name' => 'Potato Soup']);
        $recipe2->ingredients()->create(['name' => 'potatoes', 'quantity' => 3]);
        $recipe2->ingredients()->create(['name' => 'cream', 'quantity' => 1]);

        // Recipe with only tomato
        $recipe3 = Recipe::factory()->create(['name' => 'Tomato Salad']);
        $recipe3->ingredients()->create(['name' => 'tomatoes', 'quantity' => 2]);
        $recipe3->ingredients()->create(['name' => 'lettuce', 'quantity' => 1]);

        // Recipe with neither
        $recipe4 = Recipe::factory()->create(['name' => 'Carrot Cake']);
        $recipe4->ingredients()->create(['name' => 'carrots', 'quantity' => 1]);

        // Search for recipes with BOTH potato AND tomato
        $query = $this->action->execute([
            'ingredient' => 'potato,tomato',
        ]);
        $result = $query->get();

        // Should only return the recipe that has BOTH ingredients
        $this->assertEquals(1, $result->count());
        $this->assertEquals('Mixed Vegetable Stew', $result->first()->name);
    }

    /**
     * @test
     */
    public function it_properly_delegates_to_recipe_builder()
    {
        // Create diverse test data
        $recipe1 = Recipe::factory()->create([
            'name' => 'Chocolate Chip Cookies',
            'description' => 'Sweet treats',
        ]);
        $recipe1->authors()->create([
            'name' => 'Baker Bob',
            'email' => 'baker@example.com',
        ]);
        $recipe1->ingredients()->create([
            'name' => 'chocolate chips',
            'quantity' => 2,
            'unit' => 'cups',
        ]);
        $recipe1->steps()->create([
            'title' => 'Mix chocolate',
            'description' => 'Fold in chips',
            'order' => 1,
        ]);

        // Test that all search paths work through the action
        $query = $this->action->execute([
            'keyword' => 'chocolate',
        ]);
        $result = $query->get();

        $this->assertEquals(1, $result->count());
        $this->assertEquals('Chocolate Chip Cookies', $result->first()->name);

        // Verify relationships are loaded
        $item = $result->first();
        $this->assertTrue($item->relationLoaded('authors'));
        $this->assertTrue($item->relationLoaded('ingredients'));
        $this->assertTrue($item->relationLoaded('steps'));
    }

    /**
     * @test
     */
    public function it_searches_keyword_in_all_required_fields()
    {
        // Test recipe.name
        $recipe1 = Recipe::factory()->create([
            'name' => 'Chocolate Cake',
            'description' => 'A dessert',
        ]);

        // Test recipe.description
        $recipe2 = Recipe::factory()->create([
            'name' => 'Vanilla Cake',
            'description' => 'With chocolate frosting',
        ]);

        // Test ingredients.name
        $recipe3 = Recipe::factory()->create([
            'name' => 'Salad',
            'description' => 'Healthy',
        ]);
        $recipe3->ingredients()->create([
            'name' => 'chocolate dressing',
            'quantity' => 1,
        ]);

        // Test steps.title
        $recipe4 = Recipe::factory()->create([
            'name' => 'Cookies',
            'description' => 'Snack',
        ]);
        $recipe4->steps()->create([
            'title' => 'Add chocolate',
            'description' => 'Mix well',
            'order' => 1,
        ]);

        $query = $this->action->execute([
            'keyword' => 'chocolate',
        ]);
        $result = $query->get();

        $this->assertEquals(4, $result->count());
        $names = $result->pluck('name')->toArray();
        $this->assertEqualsCanonicalizing(
            ['Chocolate Cake', 'Vanilla Cake', 'Salad', 'Cookies'],
            $names
        );
    }

    /**
     * @test
     */
    public function it_validates_and_logic_per_requirements_document()
    {
        // This test validates the exact example from section 2.2 of the requirements:
        // email: foo@bar.com, ingredient: potato, keyword: scallop
        // Should match recipes with ALL three conditions

        // Recipe that matches ALL conditions
        $matchingRecipe = Recipe::factory()->create([
            'name' => 'Scallop and Potato Gratin',
            'description' => 'A delicious seafood dish',
        ]);
        $matchingRecipe->authors()->create(['name' => 'Chef', 'email' => 'foo@bar.com']);
        $matchingRecipe->ingredients()->create(['name' => 'potatoes', 'quantity' => 3]);
        $matchingRecipe->ingredients()->create(['name' => 'scallops', 'quantity' => 1]);

        // Recipe missing the author email
        $noAuthor = Recipe::factory()->create([
            'name' => 'Scallop and Potato Casserole',
            'description' => 'Another seafood dish',
        ]);
        $noAuthor->authors()->create(['name' => 'Other Chef', 'email' => 'other@example.com']);
        $noAuthor->ingredients()->create(['name' => 'potatoes', 'quantity' => 2]);

        // Recipe missing the ingredient
        $noIngredient = Recipe::factory()->create([
            'name' => 'Scallop Pasta',
            'description' => 'Seafood pasta',
        ]);
        $noIngredient->authors()->create(['name' => 'Chef', 'email' => 'foo@bar.com']);
        $noIngredient->ingredients()->create(['name' => 'pasta', 'quantity' => 1]);

        // Recipe missing the keyword
        $noKeyword = Recipe::factory()->create([
            'name' => 'Potato Soup',
            'description' => 'Hearty soup',
        ]);
        $noKeyword->authors()->create(['name' => 'Chef', 'email' => 'foo@bar.com']);
        $noKeyword->ingredients()->create(['name' => 'potatoes', 'quantity' => 4]);

        // Execute search with all three filters
        $query = $this->action->execute([
            'author_email' => 'foo@bar.com',
            'ingredient' => 'potato',
            'keyword' => 'scallop',
        ]);
        $result = $query->get();

        // Should only return the recipe that matches ALL conditions
        $this->assertEquals(1, $result->count(), 'Should return exactly one recipe matching all conditions');
        $this->assertEquals('Scallop and Potato Gratin', $result->first()->name);
    }

    /**
     * @test
     */
    public function it_supports_partial_ingredient_matching_per_requirements()
    {
        // This test validates the requirement: "Ingredient - this could be a partial match; 
        // for example, 'potato' should match '3 large potatoes' in the ingredients list"

        $recipe1 = Recipe::factory()->create(['name' => 'Mashed Potatoes']);
        $recipe1->ingredients()->create(['name' => '3 large potatoes', 'quantity' => 3, 'unit' => 'large']);
        $recipe1->ingredients()->create(['name' => 'butter', 'quantity' => 2, 'unit' => 'tbsp']);

        $recipe2 = Recipe::factory()->create(['name' => 'Sweet Potato Pie']);
        $recipe2->ingredients()->create(['name' => 'sweet potatoes', 'quantity' => 4]);
        $recipe2->ingredients()->create(['name' => 'sugar', 'quantity' => 1, 'unit' => 'cup']);

        $recipe3 = Recipe::factory()->create(['name' => 'Potato and Cheese Gratin']);
        $recipe3->ingredients()->create(['name' => 'russet potatoes, thinly sliced', 'quantity' => 5]);
        $recipe3->ingredients()->create(['name' => 'cheese', 'quantity' => 2, 'unit' => 'cups']);

        $recipe4 = Recipe::factory()->create(['name' => 'Carrot Soup']);
        $recipe4->ingredients()->create(['name' => 'carrots', 'quantity' => 6]);
        $recipe4->ingredients()->create(['name' => 'onion', 'quantity' => 1]);

        // Search for "potato" should match all recipes with "potato" in ingredient names
        $query = $this->action->execute(['ingredient' => 'potato']);
        $result = $query->get();

        $this->assertEquals(3, $result->count(), 'Should match all recipes with potato in ingredients');
        $names = $result->pluck('name')->toArray();
        $this->assertEqualsCanonicalizing(
            ['Mashed Potatoes', 'Sweet Potato Pie', 'Potato and Cheese Gratin'],
            $names,
            'Should match all variations of potato ingredients'
        );
    }

    /**
     * @test
     */
    public function it_normalizes_and_handles_empty_values_in_comma_separated_lists()
    {
        $recipe = Recipe::factory()->create(['name' => 'Test Recipe']);
        $recipe->authors()->create(['name' => 'Chef', 'email' => 'chef@example.com']);

        // Test with extra spaces and empty values
        $query = $this->action->execute([
            'author_email' => 'chef@example.com, , ,other@example.com',
        ]);
        $result = $query->get();

        $this->assertEquals(1, $result->count());
        $this->assertEquals('Test Recipe', $result->first()->name);
    }
}
