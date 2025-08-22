<?php

namespace Tests\Unit\Builders;

use App\Builders\RecipeBuilder;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestRecipes;

class RecipeBuilderTest extends TestCase
{
    use CreatesTestRecipes, RefreshDatabase;

    /**
     * Helper method to create a recipe with proper relationships.
     * Uses the trait methods internally but provides the old interface for compatibility.
     */
    private function createTestRecipe(
        array $recipeData = [],
        string $authorEmail = null,
        array $ingredientNames = [],
        array $stepDescriptions = []
    ): Recipe {
        $recipe = Recipe::factory()->create($recipeData);

        // Create author if provided
        if ($authorEmail) {
            $recipe->authors()->create([
                'name' => 'Test Author',
                'email' => $authorEmail,
            ]);
        }

        // Create ingredients
        foreach ($ingredientNames as $ingredientName) {
            $recipe->ingredients()->create([
                'name' => $ingredientName,
                'quantity' => 1,
                'unit' => null,
            ]);
        }

        // Create steps
        foreach ($stepDescriptions as $index => $stepDescription) {
            $recipe->steps()->create([
                'description' => $stepDescription,
                'order' => $index + 1,
            ]);
        }

        return $recipe;
    }

    /**
     * @test
     */
    public function it_uses_custom_builder_for_queries()
    {
        $builder = Recipe::query();

        $this->assertInstanceOf(RecipeBuilder::class, $builder);
    }

    /**
     * @test
     */
    public function it_filters_recipes_by_author_email()
    {
        $this->createRecipeWithAuthor([], ['name' => 'Chef', 'email' => 'chef@example.com']);
        $this->createRecipeWithAuthor([], ['name' => 'Cook', 'email' => 'cook@example.com']);
        $this->createRecipeWithAuthor([], ['name' => 'Baker', 'email' => 'baker@example.com']);

        $recipes = Recipe::byAuthor('chef@example.com')->get();

        $this->assertCount(1, $recipes);
        $this->assertEquals('chef@example.com', $recipes->first()->author_email);
    }

    /**
     * @test
     */
    public function it_searches_by_keyword_in_name()
    {
        Recipe::factory()->create([
            'name' => 'Chocolate Cake',
            'description' => 'Sweet dessert',
        ]);

        Recipe::factory()->create([
            'name' => 'Vanilla Cake',
            'description' => 'Light dessert',
        ]);

        $recipes = Recipe::searchKeyword('chocolate')->get();

        $this->assertCount(1, $recipes);
        $this->assertEquals('Chocolate Cake', $recipes->first()->name);
    }

    /**
     * @test
     */
    public function it_searches_by_keyword_in_description()
    {
        Recipe::factory()->create([
            'name' => 'Vanilla Cake',
            'description' => 'Chocolate frosting on top',
        ]);

        Recipe::factory()->create([
            'name' => 'Carrot Cake',
            'description' => 'Healthy dessert',
        ]);

        $recipes = Recipe::searchKeyword('chocolate')->get();

        $this->assertCount(1, $recipes);
        $this->assertEquals('Vanilla Cake', $recipes->first()->name);
    }

    /**
     * @test
     */
    public function it_searches_by_keyword_in_ingredients()
    {
        $this->createRecipeWithIngredients(
            ['name' => 'Salad', 'description' => 'Healthy meal'],
            [
                ['name' => 'lettuce', 'quantity' => 1],
                ['name' => 'chocolate dressing', 'quantity' => 2, 'unit' => 'tbsp'],
            ]
        );

        $this->createRecipeWithIngredients(
            ['name' => 'Soup', 'description' => 'Warm meal'],
            [
                ['name' => 'vegetables', 'quantity' => 1, 'unit' => 'cup'],
                ['name' => 'broth', 'quantity' => 4, 'unit' => 'cups'],
            ]
        );

        $recipes = Recipe::searchKeyword('chocolate')->get();

        $this->assertCount(1, $recipes);
        $this->assertEquals('Salad', $recipes->first()->name);
    }

    /**
     * @test
     */
    public function it_searches_by_keyword_in_steps()
    {
        $this->createRecipeWithSteps(
            ['name' => 'Brownies', 'description' => 'Dessert'],
            [
                ['description' => 'Mix ingredients', 'order' => 1],
                ['description' => 'Add chocolate chips', 'order' => 2],
                ['description' => 'Bake', 'order' => 3],
            ]
        );

        $this->createRecipeWithSteps(
            ['name' => 'Cookies', 'description' => 'Snack'],
            [
                ['description' => 'Mix', 'order' => 1],
                ['description' => 'Shape', 'order' => 2],
                ['description' => 'Bake', 'order' => 3],
            ]
        );

        $recipes = Recipe::searchKeyword('chocolate')->get();

        $this->assertCount(1, $recipes);
        $this->assertEquals('Brownies', $recipes->first()->name);
    }

    /**
     * @test
     */
    public function it_finds_recipes_with_specific_ingredient()
    {
        $this->createRecipeWithIngredients([], [
            ['name' => '3 large potatoes', 'quantity' => 3],
            ['name' => 'butter', 'quantity' => 0.5, 'unit' => 'cup'],
            ['name' => 'milk', 'quantity' => 1, 'unit' => 'cup'],
        ]);

        $this->createRecipeWithIngredients([], [
            ['name' => 'sweet potato', 'quantity' => 2],
            ['name' => 'oil', 'quantity' => 2, 'unit' => 'tbsp'],
        ]);

        $this->createRecipeWithIngredients([], [
            ['name' => 'carrots', 'quantity' => 3],
            ['name' => 'onions', 'quantity' => 1],
        ]);

        $recipes = Recipe::withIngredient('potato')->get();

        $this->assertCount(2, $recipes);
    }

    /**
     * @test
     */
    public function it_performs_case_insensitive_ingredient_search()
    {
        $this->createTestRecipe([], null, ['POTATOES', 'butter']);
        $this->createTestRecipe([], null, ['Potato chips', 'salt']);

        $recipes = Recipe::withIngredient('potato')->get();

        $this->assertCount(2, $recipes);
    }

    /**
     * @test
     */
    public function it_chains_multiple_query_methods()
    {
        $this->createTestRecipe(
            ['name' => 'Potato Gratin'],
            'chef@example.com',
            ['potatoes', 'cream', 'cheese']
        );

        $this->createTestRecipe(
            ['name' => 'Tomato Soup'],
            'chef@example.com',
            ['tomatoes', 'basil']
        );

        $this->createTestRecipe(
            ['name' => 'Potato Salad'],
            'other@example.com',
            ['potatoes', 'mayo']
        );

        $recipes = Recipe::query()
            ->byAuthor('chef@example.com')
            ->withIngredient('potato')
            ->get();

        $this->assertCount(1, $recipes);
        $this->assertEquals('Potato Gratin', $recipes->first()->name);
    }

    /**
     * @test
     */
    public function it_uses_combined_search_method()
    {
        $this->createTestRecipe(
            ['name' => 'Scalloped Potatoes', 'description' => 'Creamy scalloped potatoes'],
            'foo@bar.com',
            ['potatoes', 'cream']
        );

        $this->createTestRecipe(
            ['name' => 'Fish and Chips', 'description' => 'Classic British dish'],
            'foo@bar.com',
            ['fish', 'potatoes']
        );

        $this->createTestRecipe(
            ['name' => 'Scalloped Oysters', 'description' => 'Seafood dish'],
            'other@bar.com',
            ['oysters', 'cream']
        );

        $recipes = Recipe::search([
            'author_email' => 'foo@bar.com',
            'keyword' => 'scallop',
            'ingredient' => 'potato',
        ])->get();

        $this->assertCount(1, $recipes);
        $this->assertEquals('Scalloped Potatoes', $recipes->first()->name);
    }

    /**
     * @test
     */
    public function it_orders_recipes_by_popularity()
    {
        $older = Recipe::factory()->create(['created_at' => now()->subDays(5)]);
        $newer = Recipe::factory()->create(['created_at' => now()]);

        $recipes = Recipe::popular()->get();

        $this->assertEquals($newer->id, $recipes->first()->id);
        $this->assertEquals($older->id, $recipes->last()->id);
    }

    /**
     * @test
     */
    public function it_gets_recent_recipes()
    {
        Recipe::factory()->create(['created_at' => now()->subDays(5)]);
        Recipe::factory()->create(['created_at' => now()->subDays(10)]);
        Recipe::factory()->create(['created_at' => now()->subDays(15)]);

        $recentRecipes = Recipe::recent(7)->get();

        $this->assertCount(1, $recentRecipes);
    }

    /**
     * @test
     */
    public function it_returns_empty_collection_when_no_matches_found()
    {
        Recipe::factory()->count(3)->create();

        $recipes = Recipe::byAuthor('nonexistent@email.com')->get();

        $this->assertCount(0, $recipes);
    }

    /**
     * @test
     */
    public function it_handles_special_characters_in_search()
    {
        $this->createTestRecipe([], null, ["Smith's potatoes", 'butter']);

        $recipes = Recipe::withIngredient("Smith's")->get();

        $this->assertCount(1, $recipes);
    }

    /**
     * @test
     */
    public function it_combines_search_with_popular_ordering()
    {
        $recipe1 = $this->createTestRecipe(
            ['created_at' => now()->subDays(5)],
            'chef@example.com'
        );

        $recipe2 = $this->createTestRecipe(
            ['created_at' => now()],
            'chef@example.com'
        );

        $recipes = Recipe::search(['author_email' => 'chef@example.com'])
            ->popular()
            ->get();

        $this->assertCount(2, $recipes);
        $this->assertTrue($recipes->first()->created_at->isAfter($recipes->last()->created_at));
    }
}
