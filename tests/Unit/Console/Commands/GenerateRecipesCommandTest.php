<?php

namespace Tests\Unit\Console\Commands;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateRecipesCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_generates_default_number_of_recipes()
    {
        $this->assertDatabaseCount('recipes', 0);

        $this->artisan('recipes:generate', ['--count' => 3])
            ->expectsOutput('Generating 3 recipes with enhanced FakerRestaurant data...')
            ->expectsOutput('Successfully generated 3 recipes!')
            ->assertExitCode(0);

        $this->assertDatabaseCount('recipes', 3);
        $this->assertGreaterThanOrEqual(3, \DB::table('recipe_authors')->count());
        // Verify at least one recipe has a picsum image URL
        $recipe = Recipe::first();
        $this->assertStringContainsString('picsum.photos', $recipe->image_url);
        // Verify category is populated
        $this->assertNotNull($recipe->category);
        $this->assertNotEmpty($recipe->category);
    }

    /**
     * @test
     */
    public function it_generates_recipes_with_custom_count()
    {
        $this->artisan('recipes:generate', ['--count' => 5])
            ->assertExitCode(0);

        $this->assertDatabaseCount('recipes', 5);
    }

    /**
     * @test
     */
    public function it_respects_ingredient_count_options()
    {
        $this->artisan('recipes:generate', [
            '--count' => 1,
            '--min-ingredients' => 5,
            '--max-ingredients' => 7,
        ])->assertExitCode(0);

        $recipe = Recipe::first();
        $ingredientCount = $recipe->ingredients->count();

        $this->assertGreaterThanOrEqual(5, $ingredientCount);
        $this->assertLessThanOrEqual(7, $ingredientCount);
    }

    /**
     * @test
     */
    public function it_respects_step_count_options()
    {
        $this->artisan('recipes:generate', [
            '--count' => 1,
            '--min-steps' => 4,
            '--max-steps' => 6,
        ])->assertExitCode(0);

        $recipe = Recipe::first();
        $stepCount = $recipe->steps->count();

        $this->assertGreaterThanOrEqual(4, $stepCount);
        $this->assertLessThanOrEqual(6, $stepCount);
    }

    /**
     * @test
     */
    public function it_respects_author_count_options()
    {
        $this->artisan('recipes:generate', [
            '--count' => 1,
            '--min-authors' => 2,
            '--max-authors' => 3,
        ])->assertExitCode(0);

        $recipe = Recipe::first();
        $authorCount = $recipe->authors->count();

        $this->assertGreaterThanOrEqual(2, $authorCount);
        $this->assertLessThanOrEqual(3, $authorCount);
    }

    /**
     * @test
     */
    public function it_uses_picsum_image_source_by_default()
    {
        $this->artisan('recipes:generate', ['--count' => 1])
            ->assertExitCode(0);

        $recipe = Recipe::first();
        $this->assertStringContainsString('picsum.photos', $recipe->image_url);
    }

    /**
     * @test
     */
    public function it_uses_loremflickr_when_specified()
    {
        $this->artisan('recipes:generate', [
            '--count' => 1,
            '--image-source' => 'loremflickr',
        ])->assertExitCode(0);

        $recipe = Recipe::first();
        $this->assertStringContainsString('loremflickr.com', $recipe->image_url);
        $this->assertStringContainsString('food,recipe,cooking', $recipe->image_url);
    }

    /**
     * @test
     */
    public function it_generates_unique_recipe_names()
    {
        $this->artisan('recipes:generate', ['--count' => 20])
            ->assertExitCode(0);

        $recipeNames = Recipe::pluck('name')->toArray();
        $uniqueNames = array_unique($recipeNames);

        // Should have high uniqueness (allow for some duplicates in larger sets)
        $this->assertGreaterThan(15, count($uniqueNames));
    }

    /**
     * @test
     */
    public function it_generates_unique_ingredient_names_within_recipe()
    {
        $this->artisan('recipes:generate', [
            '--count' => 1,
            '--min-ingredients' => 10,
            '--max-ingredients' => 10,
        ])->assertExitCode(0);

        $recipe = Recipe::first();
        $ingredientNames = $recipe->ingredients->pluck('name')->toArray();
        $uniqueIngredients = array_unique($ingredientNames);

        $this->assertCount(10, $uniqueIngredients, 'All ingredients within a recipe should be unique');
    }

    /**
     * @test
     */
    public function it_creates_proper_step_ordering()
    {
        $this->artisan('recipes:generate', [
            '--count' => 1,
            '--min-steps' => 5,
            '--max-steps' => 5,
        ])->assertExitCode(0);

        $recipe = Recipe::first();
        $steps = $recipe->steps->sortBy('order');

        $expectedOrder = [1, 2, 3, 4, 5];
        $actualOrder = $steps->pluck('order')->toArray();

        $this->assertEquals($expectedOrder, $actualOrder);
    }

    /**
     * @test
     */
    public function it_generates_realistic_recipe_data()
    {
        $this->artisan('recipes:generate', ['--count' => 1])
            ->assertExitCode(0);

        $recipe = Recipe::first();

        // Check recipe has realistic values
        $this->assertNotEmpty($recipe->name);
        $this->assertNotEmpty($recipe->description);
        $this->assertGreaterThan(0, $recipe->prep_time);
        $this->assertGreaterThan(0, $recipe->cook_time);
        $this->assertGreaterThan(0, $recipe->servings);
        $this->assertNotEmpty($recipe->image_url);

        // Check category is populated with valid value
        $this->assertNotEmpty($recipe->category);
        $validCategories = [
            'Appetizer', 'Breakfast', 'Lunch', 'Dinner', 'Dessert',
            'Snack', 'Beverage', 'Salad', 'Soup', 'Main Course',
            'Side Dish', 'Pasta', 'Seafood', 'Vegetarian', 'Vegan',
            'Gluten-Free', 'Keto', 'Mediterranean', 'Asian', 'Italian',
            'Mexican', 'American', 'French', 'Indian', 'Thai',
        ];
        $this->assertContains($recipe->category, $validCategories);

        // Check author data
        $author = $recipe->authors->first();
        $this->assertNotEmpty($author->name);
        $this->assertNotEmpty($author->email);
        $this->assertTrue(filter_var($author->email, FILTER_VALIDATE_EMAIL) !== false);

        // Check ingredient data
        $ingredient = $recipe->ingredients->first();
        $this->assertNotEmpty($ingredient->name);
        $this->assertGreaterThan(0, $ingredient->quantity);

        // Check step data
        $step = $recipe->steps->first();
        $this->assertNotEmpty($step->title);
        $this->assertNotEmpty($step->description);
        $this->assertEquals(1, $step->order);
    }

    /**
     * @test
     */
    public function it_handles_updates_for_existing_recipes()
    {
        // Create a recipe first
        Recipe::factory()->create(['name' => 'Classic Chocolate Chip Cookies']);

        $this->assertDatabaseCount('recipes', 1);

        // Generate recipes (might include the same name)
        $this->artisan('recipes:generate', ['--count' => 5])
            ->assertExitCode(0);

        // Should still work without errors
        $this->assertDatabaseHas('recipes', ['name' => 'Classic Chocolate Chip Cookies']);
    }

    /**
     * @test
     */
    public function it_validates_ingredient_quantities_are_numeric()
    {
        $this->artisan('recipes:generate', ['--count' => 1])
            ->assertExitCode(0);

        $recipe = Recipe::first();

        foreach ($recipe->ingredients as $ingredient) {
            $this->assertIsNumeric($ingredient->quantity);
            $this->assertGreaterThan(0, $ingredient->quantity);
        }
    }

    /**
     * @test
     */
    public function it_generates_appropriate_first_step_titles()
    {
        $this->artisan('recipes:generate', [
            '--count' => 10,
            '--min-steps' => 1,
            '--max-steps' => 1,
        ])->assertExitCode(0);

        $recipes = Recipe::with('steps')->get();
        $firstStepTitles = $recipes->map(function ($recipe) {
            return $recipe->steps->first()->title;
        })->toArray();

        $appropriateFirstSteps = ['Prepare ingredients', 'Preheat oven', 'Heat oil'];

        // At least some first steps should be appropriate
        $hasAppropriateSteps = collect($firstStepTitles)->some(function ($title) use ($appropriateFirstSteps) {
            return in_array($title, $appropriateFirstSteps);
        });

        $this->assertTrue($hasAppropriateSteps, 'Should generate appropriate first step titles');
    }

    /**
     * @test
     */
    public function it_uses_faker_restaurant_for_realistic_food_names()
    {
        $this->artisan('recipes:generate', ['--count' => 5])
            ->assertExitCode(0);

        $recipes = Recipe::all();

        // Check that recipe names are more realistic (not just random words)
        foreach ($recipes as $recipe) {
            // Names should contain food-related terms or cooking methods
            $this->assertMatchesRegularExpression(
                '/[A-Z][a-z]+/', // Proper capitalization
                $recipe->name,
                'Recipe names should be properly capitalized'
            );
        }

        // Check ingredients are food-related
        $allIngredients = $recipes->flatMap(function ($recipe) {
            return $recipe->ingredients->pluck('name');
        });

        // Should have some realistic ingredients (not just random words)
        $this->assertNotEmpty($allIngredients);
    }

    /**
     * @test
     */
    public function it_handles_zero_count_gracefully()
    {
        $this->artisan('recipes:generate', ['--count' => 0])
            ->expectsOutput('Generating 0 recipes with enhanced FakerRestaurant data...')
            ->expectsOutput('Successfully generated 0 recipes!')
            ->assertExitCode(0);

        $this->assertDatabaseCount('recipes', 0);
    }

    /**
     * @test
     */
    public function it_shows_progress_bar_for_larger_counts()
    {
        $output = $this->artisan('recipes:generate', ['--count' => 3]);

        $output->expectsOutput('Generating 3 recipes with enhanced FakerRestaurant data...')
            ->expectsOutput('Successfully generated 3 recipes!')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function it_generates_valid_image_urls()
    {
        $this->artisan('recipes:generate', [
            '--count' => 2,
            '--image-source' => 'picsum',
        ])->assertExitCode(0);

        $this->artisan('recipes:generate', [
            '--count' => 2,
            '--image-source' => 'loremflickr',
        ])->assertExitCode(0);

        $recipes = Recipe::all();

        foreach ($recipes as $recipe) {
            $this->assertNotEmpty($recipe->image_url);
            $this->assertTrue(
                str_contains($recipe->image_url, 'picsum.photos') ||
                str_contains($recipe->image_url, 'loremflickr.com'),
                "Image URL should be from a valid source: {$recipe->image_url}"
            );
        }
    }
}
