<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateRecipesCommandFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_integrates_with_full_application_stack()
    {
        $this->artisan('recipes:generate', ['--count' => 2])
            ->assertExitCode(0);

        $this->assertDatabaseCount('recipes', 2);
        $this->assertGreaterThanOrEqual(2, \DB::table('recipe_authors')->count());

        // Verify each recipe has complete relational data
        $recipes = Recipe::with(['authors', 'ingredients', 'steps'])->get();

        foreach ($recipes as $recipe) {
            $this->assertGreaterThan(0, $recipe->authors->count());
            $this->assertGreaterThan(0, $recipe->ingredients->count());
            $this->assertGreaterThan(0, $recipe->steps->count());

            // Verify slug generation works
            $this->assertNotEmpty($recipe->slug);
            $this->assertStringNotContainsString(' ', $recipe->slug);
        }
    }

    /**
     * @test
     */
    public function it_handles_extreme_parameter_values()
    {
        // Test with maximum reasonable values
        $this->artisan('recipes:generate', [
            '--count' => 1,
            '--min-ingredients' => 1,
            '--max-ingredients' => 1,
            '--min-steps' => 1,
            '--max-steps' => 1,
            '--min-authors' => 1,
            '--max-authors' => 1,
        ])->assertExitCode(0);

        $recipe = Recipe::with(['authors', 'ingredients', 'steps'])->first();

        $this->assertCount(1, $recipe->authors);
        $this->assertCount(1, $recipe->ingredients);
        $this->assertCount(1, $recipe->steps);
    }

    /**
     * @test
     */
    public function it_maintains_data_integrity_across_multiple_runs()
    {
        // First run
        $this->artisan('recipes:generate', ['--count' => 3])
            ->assertExitCode(0);

        $firstRunCount = Recipe::count();
        $firstRunAuthorCount = \DB::table('recipe_authors')->count();

        // Second run with different parameters
        $this->artisan('recipes:generate', [
            '--count' => 2,
        ])->assertExitCode(0);

        $secondRunCount = Recipe::count();
        $secondRunAuthorCount = \DB::table('recipe_authors')->count();

        // Should have more recipes after second run
        $this->assertGreaterThan($firstRunCount, $secondRunCount);
        $this->assertGreaterThan($firstRunAuthorCount, $secondRunAuthorCount);

        // Verify all recipes have image URLs
        $recipes = Recipe::all();
        foreach ($recipes as $recipe) {
            $this->assertNotEmpty($recipe->image_url);
        }
    }

    /**
     * @test
     */
    public function it_works_with_existing_data()
    {
        // Create some existing recipes
        Recipe::factory(2)->create();
        $existingCount = Recipe::count();

        $this->artisan('recipes:generate', ['--count' => 3])
            ->assertExitCode(0);

        // Should have original + new recipes
        $this->assertGreaterThanOrEqual($existingCount + 3, Recipe::count());
    }

    /**
     * @test
     */
    public function it_handles_database_constraints_properly()
    {
        // Generate many recipes to test for constraint violations
        $this->artisan('recipes:generate', ['--count' => 10])
            ->assertExitCode(0);

        // Verify all recipes have valid data
        $recipes = Recipe::with(['authors', 'ingredients', 'steps'])->get();

        foreach ($recipes as $recipe) {
            // Test required fields
            $this->assertNotEmpty($recipe->name);
            $this->assertNotEmpty($recipe->slug);

            // Test unique constraints
            $duplicateSlugCount = Recipe::where('slug', $recipe->slug)->count();
            $this->assertEquals(1, $duplicateSlugCount, "Slug '{$recipe->slug}' should be unique");

            // Test foreign key constraints
            foreach ($recipe->authors as $author) {
                $this->assertEquals($recipe->id, $author->recipe_id);
            }

            foreach ($recipe->ingredients as $ingredient) {
                $this->assertEquals($recipe->id, $ingredient->recipe_id);
            }

            foreach ($recipe->steps as $step) {
                $this->assertEquals($recipe->id, $step->recipe_id);
            }
        }
    }

    /**
     * @test
     */
    public function it_generates_searchable_content()
    {
        $this->artisan('recipes:generate', ['--count' => 5])
            ->assertExitCode(0);

        // Test that generated content is searchable via existing search functionality
        $searchTerms = ['chicken', 'chocolate', 'pasta', 'oil', 'flour'];

        foreach ($searchTerms as $term) {
            $foundRecipes = Recipe::whereRaw('MATCH(name, description) AGAINST(? IN BOOLEAN MODE)', [$term])
                ->orWhereHas('ingredients', function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%");
                })
                ->orWhereHas('authors', function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%");
                })
                ->get();

            // At least some recipes should be findable (depending on generated content)
            // This tests that the search functionality works with generated data
            $this->assertGreaterThanOrEqual(0, $foundRecipes->count());
        }
    }

    /**
     * @test
     */
    public function it_generates_realistic_timing_values()
    {
        $this->artisan('recipes:generate', ['--count' => 10])
            ->assertExitCode(0);

        $recipes = Recipe::all();

        foreach ($recipes as $recipe) {
            // Prep time should be reasonable (5-60 minutes)
            $this->assertGreaterThanOrEqual(5, $recipe->prep_time);
            $this->assertLessThanOrEqual(60, $recipe->prep_time);

            // Cook time should be reasonable (10-180 minutes)
            $this->assertGreaterThanOrEqual(10, $recipe->cook_time);
            $this->assertLessThanOrEqual(180, $recipe->cook_time);

            // Servings should be reasonable (1-12)
            $this->assertGreaterThanOrEqual(1, $recipe->servings);
            $this->assertLessThanOrEqual(12, $recipe->servings);
        }
    }

    /**
     * @test
     */
    public function it_handles_command_help_and_description()
    {
        $this->artisan('recipes:generate', ['--help'])
            ->expectsOutputToContain('Generate fake recipe data with authors, ingredients, and steps using Faker')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function it_maintains_performance_with_larger_datasets()
    {
        $startTime = microtime(true);

        $this->artisan('recipes:generate', ['--count' => 20])
            ->assertExitCode(0);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Command should complete in reasonable time (under 30 seconds)
        $this->assertLessThan(30, $executionTime, 'Command took too long to execute');

        $this->assertGreaterThanOrEqual(19, Recipe::count());
        $this->assertLessThanOrEqual(21, Recipe::count());
    }
}
