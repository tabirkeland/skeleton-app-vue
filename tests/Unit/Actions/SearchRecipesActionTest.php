<?php

namespace Tests\Unit\Actions;

use App\Actions\Search\SearchRecipesAction;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $recipe1 = Recipe::factory()->create();
        $recipe1->authors()->delete();
        $recipe1->authors()->create(['name' => 'Chef 1', 'email' => 'chef@example.com']);

        $recipe2 = Recipe::factory()->create();
        $recipe2->authors()->delete();
        $recipe2->authors()->create(['name' => 'Chef 2', 'email' => 'chef@example.com']);

        $recipe3 = Recipe::factory()->create();
        $recipe3->authors()->delete();
        $recipe3->authors()->create(['name' => 'Other', 'email' => 'other@example.com']);

        $result = $this->action->execute(['author_email' => 'chef@example.com']);

        $this->assertEquals(2, $result->total());
        foreach ($result->items() as $recipe) {
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

        $result = $this->action->execute(['keyword' => 'Cake']);

        $this->assertEquals(2, $result->total());
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

        $result = $this->action->execute(['ingredient' => 'chocolate']);

        $this->assertEquals(2, $result->total());
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

        $result = $this->action->execute([
            'author_email' => 'chef@example.com',
            'keyword' => 'Chocolate',
            'ingredient' => 'chocolate',
        ]);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Chocolate Cake', $result->items()[0]->name);
    }

    /**
     * @test
     */
    public function it_respects_custom_per_page_parameter()
    {
        Recipe::factory()->count(20)->create();

        $result = $this->action->execute(['perPage' => 5]);

        $this->assertEquals(5, $result->perPage());
        $this->assertEquals(5, $result->count());
        $this->assertEquals(20, $result->total());
    }

    /**
     * @test
     */
    public function it_normalizes_email_filter_to_lowercase()
    {
        $recipe = Recipe::factory()->create();
        $recipe->authors()->create(['name' => 'Chef', 'email' => 'chef@example.com']);

        $result = $this->action->execute(['author_email' => 'CHEF@EXAMPLE.COM']);

        $this->assertEquals(1, $result->total());
    }

    /**
     * @test
     */
    public function it_orders_results_by_popularity()
    {
        $older = Recipe::factory()->create(['created_at' => now()->subDays(2)]);
        $newer = Recipe::factory()->create(['created_at' => now()->subDay()]);
        $newest = Recipe::factory()->create(['created_at' => now()]);

        $result = $this->action->execute([]);

        $items = $result->items();
        $this->assertEquals($newest->id, $items[0]->id);
        $this->assertEquals($newer->id, $items[1]->id);
        $this->assertEquals($older->id, $items[2]->id);
    }

    /**
     * @test
     */
    public function it_returns_empty_results_when_no_matches()
    {
        Recipe::factory()->count(5)->create();

        $result = $this->action->execute(['keyword' => 'NonExistentKeyword']);

        $this->assertEquals(0, $result->total());
        $this->assertEmpty($result->items());
    }

    /**
     * @test
     */
    public function it_handles_empty_filters_array()
    {
        Recipe::factory()->count(3)->create();

        $result = $this->action->execute([]);

        $this->assertEquals(3, $result->total());
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
            'ingredient' => null,
        ]);

        $this->assertEquals(3, $result->total());
    }

    /**
     * @test
     */
    public function it_handles_special_characters_in_keyword_search()
    {
        Recipe::factory()->create(['name' => 'Recipe with Special Characters & Symbols!']);
        Recipe::factory()->create(['name' => 'Normal Recipe']);

        $result = $this->action->execute(['keyword' => 'Special Characters & Symbols']);

        $this->assertEquals(1, $result->total());
    }

    /**
     * @test
     */
    public function it_maintains_pagination_with_filters()
    {
        $recipes = [];
        for ($i = 1; $i <= 10; $i++) {
            $recipe = Recipe::factory()->create(['name' => "Chocolate Recipe $i"]);
            $recipe->authors()->create(['name' => 'Chef', 'email' => 'chef@example.com']);
        }

        $result = $this->action->execute([
            'author_email' => 'chef@example.com',
            'keyword' => 'Chocolate',
            'perPage' => 3,
            'page' => 2,
        ]);

        $this->assertEquals(3, $result->perPage());
        $this->assertEquals(2, $result->currentPage());
        $this->assertEquals(10, $result->total());
        $this->assertEquals(3, $result->count());
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

        $result = $this->action->execute([
            'author_email' => 'chef@example.com',
            'keyword' => 'Chocolate',
            'ingredient' => 'chocolate',
        ]);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Chocolate Cake', $result->items()[0]->name);
    }
}
