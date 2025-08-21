<?php

namespace Tests\Unit\Builders;

use Tests\TestCase;
use App\Models\Recipe;
use App\Builders\RecipeBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecipeBuilderTest extends TestCase
{
    use RefreshDatabase;

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
        Recipe::factory()->create(['author_email' => 'chef@example.com']);
        Recipe::factory()->create(['author_email' => 'cook@example.com']);
        Recipe::factory()->create(['author_email' => 'baker@example.com']);

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
            'ingredients' => ['flour', 'eggs'],
            'steps' => ['Mix', 'Bake']
        ]);

        Recipe::factory()->create([
            'name' => 'Vanilla Cake',
            'description' => 'Light dessert',
            'ingredients' => ['flour', 'vanilla'],
            'steps' => ['Mix', 'Bake']
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
            'ingredients' => ['flour', 'vanilla'],
            'steps' => ['Mix', 'Bake']
        ]);

        Recipe::factory()->create([
            'name' => 'Carrot Cake',
            'description' => 'Healthy dessert',
            'ingredients' => ['carrots', 'flour'],
            'steps' => ['Mix', 'Bake']
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
        Recipe::factory()->create([
            'name' => 'Salad',
            'description' => 'Healthy meal',
            'ingredients' => ['lettuce', 'chocolate dressing'],
            'steps' => ['Toss', 'Serve']
        ]);

        Recipe::factory()->create([
            'name' => 'Soup',
            'description' => 'Warm meal',
            'ingredients' => ['vegetables', 'broth'],
            'steps' => ['Boil', 'Serve']
        ]);

        $recipes = Recipe::searchKeyword('chocolate')->get();

        $this->assertCount(1, $recipes);
        $this->assertEquals('Salad', $recipes->first()->name);
    }

    /**
     * @test
     */
    public function it_searches_by_keyword_in_steps()
    {
        Recipe::factory()->create([
            'name' => 'Brownies',
            'description' => 'Dessert',
            'ingredients' => ['flour', 'eggs'],
            'steps' => ['Mix ingredients', 'Add chocolate chips', 'Bake']
        ]);

        Recipe::factory()->create([
            'name' => 'Cookies',
            'description' => 'Snack',
            'ingredients' => ['flour', 'sugar'],
            'steps' => ['Mix', 'Shape', 'Bake']
        ]);

        $recipes = Recipe::searchKeyword('chocolate')->get();

        $this->assertCount(1, $recipes);
        $this->assertEquals('Brownies', $recipes->first()->name);
    }

    /**
     * @test
     */
    public function it_finds_recipes_with_specific_ingredient()
    {
        Recipe::factory()->create([
            'ingredients' => ['3 large potatoes', 'butter', 'milk']
        ]);

        Recipe::factory()->create([
            'ingredients' => ['sweet potato', 'oil']
        ]);

        Recipe::factory()->create([
            'ingredients' => ['carrots', 'onions']
        ]);

        $recipes = Recipe::withIngredient('potato')->get();

        $this->assertCount(2, $recipes);
    }

    /**
     * @test
     */
    public function it_performs_case_insensitive_ingredient_search()
    {
        Recipe::factory()->create([
            'ingredients' => ['POTATOES', 'butter']
        ]);

        Recipe::factory()->create([
            'ingredients' => ['Potato chips', 'salt']
        ]);

        $recipes = Recipe::withIngredient('potato')->get();

        $this->assertCount(2, $recipes);
    }

    /**
     * @test
     */
    public function it_chains_multiple_query_methods()
    {
        Recipe::factory()->create([
            'author_email' => 'chef@example.com',
            'name' => 'Potato Gratin',
            'ingredients' => ['potatoes', 'cream', 'cheese']
        ]);

        Recipe::factory()->create([
            'author_email' => 'chef@example.com',
            'name' => 'Tomato Soup',
            'ingredients' => ['tomatoes', 'basil']
        ]);

        Recipe::factory()->create([
            'author_email' => 'other@example.com',
            'name' => 'Potato Salad',
            'ingredients' => ['potatoes', 'mayo']
        ]);

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
        Recipe::factory()->create([
            'author_email' => 'foo@bar.com',
            'name' => 'Scalloped Potatoes',
            'ingredients' => ['potatoes', 'cream'],
            'description' => 'Creamy scalloped potatoes'
        ]);

        Recipe::factory()->create([
            'author_email' => 'foo@bar.com',
            'name' => 'Fish and Chips',
            'ingredients' => ['fish', 'potatoes'],
            'description' => 'Classic British dish'
        ]);

        Recipe::factory()->create([
            'author_email' => 'other@bar.com',
            'name' => 'Scalloped Oysters',
            'ingredients' => ['oysters', 'cream'],
            'description' => 'Seafood dish'
        ]);

        $recipes = Recipe::search([
            'author_email' => 'foo@bar.com',
            'keyword' => 'scallop',
            'ingredient' => 'potato'
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
        Recipe::factory()->create([
            'ingredients' => ["Smith's potatoes", 'butter']
        ]);

        $recipes = Recipe::withIngredient("Smith's")->get();

        $this->assertCount(1, $recipes);
    }

    /**
     * @test
     */
    public function it_combines_search_with_popular_ordering()
    {
        Recipe::factory()->create([
            'author_email' => 'chef@example.com',
            'created_at' => now()->subDays(5)
        ]);

        Recipe::factory()->create([
            'author_email' => 'chef@example.com',
            'created_at' => now()
        ]);

        $recipes = Recipe::search(['author_email' => 'chef@example.com'])
            ->popular()
            ->get();

        $this->assertCount(2, $recipes);
        $this->assertTrue($recipes->first()->created_at->isAfter($recipes->last()->created_at));
    }
}