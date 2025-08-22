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
                ['title' => 'Mix ingredients', 'description' => 'Combine dry ingredients', 'order' => 1],
                ['title' => 'Add chocolate chips', 'description' => 'Fold in the chips', 'order' => 2],
                ['title' => 'Bake', 'description' => 'Bake at 350F', 'order' => 3],
            ]
        );

        $this->createRecipeWithSteps(
            ['name' => 'Cookies', 'description' => 'Snack'],
            [
                ['title' => 'Mix', 'description' => 'Combine ingredients', 'order' => 1],
                ['title' => 'Shape', 'description' => 'Form into balls', 'order' => 2],
                ['title' => 'Bake', 'description' => 'Bake until golden', 'order' => 3],
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

    /**
     * @test
     */
    public function it_searches_with_multiple_author_emails()
    {
        $this->createRecipeWithAuthor(
            ['name' => 'Recipe 1'],
            ['name' => 'Chef One', 'email' => 'chef1@example.com']
        );
        $this->createRecipeWithAuthor(
            ['name' => 'Recipe 2'],
            ['name' => 'Chef Two', 'email' => 'chef2@example.com']
        );
        $this->createRecipeWithAuthor(
            ['name' => 'Recipe 3'],
            ['name' => 'Chef Three', 'email' => 'chef3@example.com']
        );

        $recipes = Recipe::withAnyAuthor(['chef1@example.com', 'chef3@example.com'])->get();

        $this->assertCount(2, $recipes);
        $this->assertEqualsCanonicalizing(
            ['Recipe 1', 'Recipe 3'],
            $recipes->pluck('name')->toArray()
        );
    }

    /**
     * @test
     */
    public function it_searches_with_author_emails_array_via_search_method()
    {
        $this->createRecipeWithAuthor(
            ['name' => 'Recipe A'],
            ['name' => 'Author A', 'email' => 'a@example.com']
        );
        $this->createRecipeWithAuthor(
            ['name' => 'Recipe B'],
            ['name' => 'Author B', 'email' => 'b@example.com']
        );
        $this->createRecipeWithAuthor(
            ['name' => 'Recipe C'],
            ['name' => 'Author C', 'email' => 'c@example.com']
        );

        $recipes = Recipe::search([
            'author_emails' => ['a@example.com', 'c@example.com'],
        ])->get();

        $this->assertCount(2, $recipes);
        $this->assertEqualsCanonicalizing(
            ['Recipe A', 'Recipe C'],
            $recipes->pluck('name')->toArray()
        );
    }

    /**
     * @test
     */
    public function it_searches_with_single_author_email_in_array()
    {
        $this->createRecipeWithAuthor(
            ['name' => 'Single Recipe'],
            ['name' => 'Single Author', 'email' => 'single@example.com']
        );
        $this->createRecipeWithAuthor(
            ['name' => 'Other Recipe'],
            ['name' => 'Other Author', 'email' => 'other@example.com']
        );

        $recipes = Recipe::search([
            'author_emails' => ['single@example.com'],
        ])->get();

        $this->assertCount(1, $recipes);
        $this->assertEquals('Single Recipe', $recipes->first()->name);
    }

    /**
     * @test
     */
    public function it_searches_with_multiple_ingredients()
    {
        $this->createRecipeWithIngredients(
            ['name' => 'Potato Soup'],
            [
                ['name' => 'potatoes', 'quantity' => 3],
                ['name' => 'onions', 'quantity' => 1],
            ]
        );
        $this->createRecipeWithIngredients(
            ['name' => 'Tomato Salad'],
            [
                ['name' => 'tomatoes', 'quantity' => 2],
                ['name' => 'lettuce', 'quantity' => 1],
            ]
        );
        $this->createRecipeWithIngredients(
            ['name' => 'Mixed Vegetables'],
            [
                ['name' => 'potatoes', 'quantity' => 1],
                ['name' => 'tomatoes', 'quantity' => 1],
            ]
        );

        $recipes = Recipe::withAnyIngredient(['potato', 'tomato'])->get();

        $this->assertCount(3, $recipes);
    }

    /**
     * @test
     */
    public function it_searches_with_ingredients_array_via_search_method()
    {
        $this->createRecipeWithIngredients(
            ['name' => 'Cheese Pizza'],
            [
                ['name' => 'cheese', 'quantity' => 2, 'unit' => 'cups'],
                ['name' => 'tomato sauce', 'quantity' => 1, 'unit' => 'cup'],
            ]
        );
        $this->createRecipeWithIngredients(
            ['name' => 'Chicken Salad'],
            [
                ['name' => 'chicken', 'quantity' => 1],
                ['name' => 'lettuce', 'quantity' => 2],
            ]
        );
        $this->createRecipeWithIngredients(
            ['name' => 'Cheese Chicken'],
            [
                ['name' => 'cheese', 'quantity' => 1],
                ['name' => 'chicken breast', 'quantity' => 2],
            ]
        );

        $recipes = Recipe::search([
            'ingredients' => ['cheese', 'chicken'],
        ])->get();

        $this->assertCount(3, $recipes);
    }

    /**
     * @test
     */
    public function it_searches_with_single_ingredient_in_array()
    {
        $this->createRecipeWithIngredients(
            ['name' => 'Garlic Bread'],
            [['name' => 'garlic', 'quantity' => 3]]
        );
        $this->createRecipeWithIngredients(
            ['name' => 'Plain Bread'],
            [['name' => 'flour', 'quantity' => 2]]
        );

        $recipes = Recipe::search([
            'ingredients' => ['garlic'],
        ])->get();

        $this->assertCount(1, $recipes);
        $this->assertEquals('Garlic Bread', $recipes->first()->name);
    }

    /**
     * @test
     */
    public function it_searches_by_author_name()
    {
        $this->createRecipeWithAuthor(
            ['name' => 'Recipe by John'],
            ['name' => 'John Doe', 'email' => 'john@example.com']
        );
        $this->createRecipeWithAuthor(
            ['name' => 'Recipe by Jane'],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com']
        );

        $recipes = Recipe::byAuthorName('John')->get();

        $this->assertCount(1, $recipes);
        $this->assertEquals('Recipe by John', $recipes->first()->name);
    }

    /**
     * @test
     */
    public function it_combines_all_search_filters()
    {
        // Create recipe that matches all filters
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

        // Create recipe that matches some but not all filters
        $recipe2 = Recipe::factory()->create([
            'name' => 'Brownies',
            'description' => 'Chocolate dessert',
        ]);
        $recipe2->authors()->create([
            'name' => 'Chef John',  // Doesn't match 'Baker'
            'email' => 'chef@example.com',
        ]);
        $recipe2->ingredients()->create([
            'name' => 'dark chocolate',
            'quantity' => 2,
            'unit' => 'cups',
        ]);

        // Search with multiple filters - only recipe1 should match all
        $recipes = Recipe::search([
            'keyword' => 'chocolate',
            'author_name' => 'Baker',
            'ingredients' => ['chocolate'],
        ])->get();

        $this->assertCount(1, $recipes);
        $this->assertEquals('Chocolate Chip Cookies', $recipes->first()->name);
    }

    /**
     * @test
     */
    public function it_verifies_keyword_searches_all_required_fields()
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
        $recipe3 = $this->createRecipeWithIngredients(
            ['name' => 'Salad', 'description' => 'Healthy'],
            [['name' => 'chocolate dressing', 'quantity' => 1]]
        );

        // Test steps.title
        $recipe4 = $this->createRecipeWithSteps(
            ['name' => 'Cookies', 'description' => 'Snack'],
            [['title' => 'Add chocolate', 'description' => 'Mix well', 'order' => 1]]
        );

        $recipes = Recipe::searchKeyword('chocolate')->get();

        $this->assertCount(4, $recipes);
        $recipeNames = $recipes->pluck('name')->toArray();
        $this->assertEqualsCanonicalizing(
            ['Chocolate Cake', 'Vanilla Cake', 'Salad', 'Cookies'],
            $recipeNames
        );
    }
}
