<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use App\Models\Recipe;
use App\Actions\Search\SearchRecipesAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;

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
    public function it_returns_paginated_results()
    {
        Recipe::factory()->count(15)->create();

        $result = $this->action->execute([]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(15, $result->perPage()); // Default is 15
        $this->assertEquals(15, $result->total());
    }

    /**
     * @test
     */
    public function it_filters_by_author_email()
    {
        Recipe::factory()->count(3)->create(['author_email' => 'chef@example.com']);
        Recipe::factory()->count(2)->create(['author_email' => 'other@example.com']);

        $result = $this->action->execute(['author_email' => 'chef@example.com']);

        $this->assertEquals(3, $result->total());
        foreach ($result->items() as $recipe) {
            $this->assertEquals('chef@example.com', $recipe->author_email);
        }
    }

    /**
     * @test
     */
    public function it_filters_by_keyword()
    {
        Recipe::factory()->create([
            'name' => 'Chocolate Cake',
            'description' => 'Delicious dessert'
        ]);
        Recipe::factory()->create([
            'name' => 'Vanilla Cake',
            'description' => 'Light dessert'
        ]);
        Recipe::factory()->create([
            'name' => 'Chocolate Chip Cookies',
            'description' => 'Sweet treats'
        ]);

        $result = $this->action->execute(['keyword' => 'chocolate']);

        $this->assertEquals(2, $result->total());
    }

    /**
     * @test
     */
    public function it_filters_by_ingredient()
    {
        Recipe::factory()->create(['ingredients' => ['potatoes', 'butter']]);
        Recipe::factory()->create(['ingredients' => ['tomatoes', 'basil']]);
        Recipe::factory()->create(['ingredients' => ['sweet potato', 'oil']]);

        $result = $this->action->execute(['ingredient' => 'potato']);

        $this->assertEquals(2, $result->total());
    }

    /**
     * @test
     */
    public function it_combines_multiple_filters()
    {
        Recipe::factory()->create([
            'author_email' => 'chef@example.com',
            'name' => 'Potato Gratin',
            'ingredients' => ['potatoes', 'cream']
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

        $result = $this->action->execute([
            'author_email' => 'chef@example.com',
            'ingredient' => 'potato'
        ]);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Potato Gratin', $result->items()[0]->name);
    }

    /**
     * @test
     */
    public function it_respects_custom_per_page_parameter()
    {
        Recipe::factory()->count(25)->create();

        $result = $this->action->execute(['perPage' => 20]);

        $this->assertEquals(20, $result->perPage());
        $this->assertEquals(20, count($result->items()));
    }

    /**
     * @test
     */
    public function it_normalizes_email_filter_to_lowercase()
    {
        Recipe::factory()->count(2)->create(['author_email' => 'chef@example.com']);
        Recipe::factory()->count(3)->create(['author_email' => 'other@example.com']);

        $result = $this->action->execute(['author_email' => 'CHEF@EXAMPLE.COM']);

        $this->assertEquals(2, $result->total());
    }

    /**
     * @test
     */
    public function it_orders_results_by_popularity()
    {
        $older = Recipe::factory()->create([
            'name' => 'Older Recipe',
            'created_at' => now()->subDays(5)
        ]);
        $newer = Recipe::factory()->create([
            'name' => 'Newer Recipe',
            'created_at' => now()
        ]);

        $result = $this->action->execute([]);

        $items = $result->items();
        $this->assertEquals($newer->id, $items[0]->id);
        $this->assertEquals($older->id, $items[1]->id);
    }

    /**
     * @test
     */
    public function it_returns_empty_results_when_no_matches()
    {
        Recipe::factory()->count(5)->create();

        $result = $this->action->execute(['author_email' => 'nonexistent@example.com']);

        $this->assertEquals(0, $result->total());
        $this->assertEmpty($result->items());
    }

    /**
     * @test
     */
    public function it_handles_empty_filters_array()
    {
        Recipe::factory()->count(5)->create();

        $result = $this->action->execute([]);

        $this->assertEquals(5, $result->total());
    }

    /**
     * @test
     */
    public function it_ignores_null_filter_values()
    {
        Recipe::factory()->count(3)->create();

        $result = $this->action->execute([
            'author_email' => null,
            'keyword' => null,
            'ingredient' => null
        ]);

        $this->assertEquals(3, $result->total());
    }

    /**
     * @test
     */
    public function it_handles_special_characters_in_keyword_search()
    {
        Recipe::factory()->create(['name' => "Chef's Special"]);
        Recipe::factory()->create(['name' => 'Regular Recipe']);

        $result = $this->action->execute(['keyword' => "Chef's"]);

        $this->assertEquals(1, $result->total());
        $this->assertEquals("Chef's Special", $result->items()[0]->name);
    }

    /**
     * @test
     */
    public function it_maintains_pagination_with_filters()
    {
        Recipe::factory()->count(25)->create(['author_email' => 'chef@example.com']);

        $page1 = $this->action->execute(['author_email' => 'chef@example.com', 'perPage' => 10]);
        
        $this->assertEquals(1, $page1->currentPage());
        $this->assertEquals(3, $page1->lastPage());
        $this->assertTrue($page1->hasMorePages());
        $this->assertEquals(10, count($page1->items()));
    }

    /**
     * @test
     */
    public function it_applies_all_three_filters_with_and_logic()
    {
        Recipe::factory()->create([
            'author_email' => 'foo@bar.com',
            'name' => 'Scalloped Potatoes',
            'description' => 'Creamy scalloped potatoes',
            'ingredients' => ['potatoes', 'cream', 'cheese']
        ]);

        Recipe::factory()->create([
            'author_email' => 'foo@bar.com',
            'name' => 'Potato Salad',
            'description' => 'Summer side dish',
            'ingredients' => ['potatoes', 'mayo']
        ]);

        Recipe::factory()->create([
            'author_email' => 'other@bar.com',
            'name' => 'Scalloped Oysters',
            'description' => 'Seafood dish',
            'ingredients' => ['oysters', 'cream']
        ]);

        $result = $this->action->execute([
            'author_email' => 'foo@bar.com',
            'keyword' => 'scallop',
            'ingredient' => 'potato'
        ]);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Scalloped Potatoes', $result->items()[0]->name);
    }
}