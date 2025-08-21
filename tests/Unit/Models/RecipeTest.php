<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Recipe;
use App\Builders\RecipeBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecipeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_uses_custom_builder()
    {
        $builder = Recipe::query();

        $this->assertInstanceOf(RecipeBuilder::class, $builder);
    }

    /**
     * @test
     */
    public function it_has_correct_fillable_attributes()
    {
        $recipe = new Recipe();

        $expected = [
            'name',
            'description',
            'ingredients',
            'steps',
            'author_email',
            'slug'
        ];

        $this->assertEquals($expected, $recipe->getFillable());
    }

    /**
     * @test
     */
    public function it_casts_ingredients_to_array()
    {
        $recipe = Recipe::create([
            'name' => 'Test Recipe',
            'description' => 'Test description',
            'ingredients' => ['ingredient1', 'ingredient2'],
            'steps' => ['step1'],
            'author_email' => 'test@example.com',
            'slug' => 'test-recipe'
        ]);

        $retrieved = Recipe::find($recipe->id);

        $this->assertIsArray($retrieved->ingredients);
        $this->assertEquals(['ingredient1', 'ingredient2'], $retrieved->ingredients);
    }

    /**
     * @test
     */
    public function it_casts_steps_to_array()
    {
        $recipe = Recipe::create([
            'name' => 'Test Recipe',
            'description' => 'Test description',
            'ingredients' => ['ingredient1'],
            'steps' => ['step1', 'step2', 'step3'],
            'author_email' => 'test@example.com',
            'slug' => 'test-recipe'
        ]);

        $retrieved = Recipe::find($recipe->id);

        $this->assertIsArray($retrieved->steps);
        $this->assertEquals(['step1', 'step2', 'step3'], $retrieved->steps);
    }

    /**
     * @test
     */
    public function it_handles_empty_arrays_for_json_fields()
    {
        $recipe = Recipe::create([
            'name' => 'Test Recipe',
            'description' => 'Test description',
            'ingredients' => [],
            'steps' => [],
            'author_email' => 'test@example.com',
            'slug' => 'test-recipe'
        ]);

        $this->assertIsArray($recipe->ingredients);
        $this->assertEmpty($recipe->ingredients);
        $this->assertIsArray($recipe->steps);
        $this->assertEmpty($recipe->steps);
    }

    /**
     * @test
     */
    public function it_stores_and_retrieves_all_attributes_correctly()
    {
        $data = [
            'name' => 'Complete Recipe',
            'description' => 'A complete recipe with all attributes',
            'ingredients' => ['flour', 'eggs', 'milk'],
            'steps' => ['Mix ingredients', 'Bake for 30 minutes'],
            'author_email' => 'chef@example.com',
            'slug' => 'complete-recipe'
        ];

        $recipe = Recipe::create($data);
        $retrieved = Recipe::find($recipe->id);

        $this->assertEquals($data['name'], $retrieved->name);
        $this->assertEquals($data['description'], $retrieved->description);
        $this->assertEquals($data['ingredients'], $retrieved->ingredients);
        $this->assertEquals($data['steps'], $retrieved->steps);
        $this->assertEquals($data['author_email'], $retrieved->author_email);
        $this->assertEquals($data['slug'], $retrieved->slug);
    }

    /**
     * @test
     */
    public function it_has_timestamps()
    {
        $recipe = Recipe::factory()->create();

        $this->assertNotNull($recipe->created_at);
        $this->assertNotNull($recipe->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $recipe->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $recipe->updated_at);
    }

    /**
     * @test
     */
    public function it_can_be_found_by_slug()
    {
        $recipe = Recipe::factory()->create(['slug' => 'unique-slug']);

        $found = Recipe::where('slug', 'unique-slug')->first();

        $this->assertNotNull($found);
        $this->assertEquals($recipe->id, $found->id);
    }

    /**
     * @test
     */
    public function it_preserves_json_structure_with_special_characters()
    {
        $recipe = Recipe::create([
            'name' => 'Special Recipe',
            'description' => 'Recipe with special characters',
            'ingredients' => ["1/2 cup milk", "Chef's special sauce", 'Salt & pepper'],
            'steps' => ['Step with "quotes"', "Step with 'apostrophes'"],
            'author_email' => 'test@example.com',
            'slug' => 'special-recipe'
        ]);

        $retrieved = Recipe::find($recipe->id);

        $this->assertEquals("1/2 cup milk", $retrieved->ingredients[0]);
        $this->assertEquals("Chef's special sauce", $retrieved->ingredients[1]);
        $this->assertEquals('Salt & pepper', $retrieved->ingredients[2]);
        $this->assertEquals('Step with "quotes"', $retrieved->steps[0]);
        $this->assertEquals("Step with 'apostrophes'", $retrieved->steps[1]);
    }

    /**
     * @test
     */
    public function it_can_access_ingredient_count()
    {
        $recipe = Recipe::create([
            'name' => 'Test Recipe',
            'description' => 'Test',
            'ingredients' => ['item1', 'item2', 'item3'],
            'steps' => ['step1'],
            'author_email' => 'test@example.com',
            'slug' => 'test-recipe'
        ]);

        $this->assertEquals(3, count($recipe->ingredients));
    }

    /**
     * @test
     */
    public function it_can_access_step_count()
    {
        $recipe = Recipe::create([
            'name' => 'Test Recipe',
            'description' => 'Test',
            'ingredients' => ['item1'],
            'steps' => ['step1', 'step2', 'step3', 'step4'],
            'author_email' => 'test@example.com',
            'slug' => 'test-recipe'
        ]);

        $this->assertEquals(4, count($recipe->steps));
    }

    /**
     * @test
     */
    public function it_can_be_converted_to_array()
    {
        $recipe = Recipe::factory()->create();

        $array = $recipe->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('ingredients', $array);
        $this->assertArrayHasKey('steps', $array);
        $this->assertArrayHasKey('author_email', $array);
        $this->assertArrayHasKey('slug', $array);
    }

    /**
     * @test
     */
    public function it_can_be_converted_to_json()
    {
        $recipe = Recipe::factory()->create();

        $json = $recipe->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals($recipe->id, $decoded['id']);
        $this->assertEquals($recipe->name, $decoded['name']);
    }

    /**
     * @test
     */
    public function it_uses_factory_correctly()
    {
        $recipe = Recipe::factory()->create();

        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id
        ]);
    }

    /**
     * @test
     */
    public function it_handles_mass_assignment()
    {
        $data = [
            'name' => 'Mass Assignment Test',
            'description' => 'Testing mass assignment',
            'ingredients' => ['ingredient'],
            'steps' => ['step'],
            'author_email' => 'test@example.com',
            'slug' => 'mass-assignment-test'
        ];

        $recipe = Recipe::create($data);

        $this->assertDatabaseHas('recipes', [
            'name' => 'Mass Assignment Test',
            'slug' => 'mass-assignment-test'
        ]);
    }
}