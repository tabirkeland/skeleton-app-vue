<?php

namespace Tests\Unit\Models;

use App\Builders\RecipeBuilder;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
            'slug',
            'category',
            'image_url',
            'prep_time',
            'cook_time',
            'servings',
        ];

        $this->assertEquals($expected, $recipe->getFillable());
    }

    /**
     * @test
     */
    public function it_has_relationships()
    {
        $recipe = Recipe::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $recipe->authors());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $recipe->ingredients());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $recipe->steps());
    }

    /**
     * @test
     */
    public function it_eager_loads_relationships()
    {
        $recipe = Recipe::factory()->create();
        $recipe->authors()->create(['name' => 'Test Author', 'email' => 'test@example.com']);
        $recipe->ingredients()->create(['name' => 'Test Ingredient', 'quantity' => 1]);
        $recipe->steps()->create(['description' => 'Test Step', 'order' => 1]);

        $loaded = Recipe::find($recipe->id);

        $this->assertTrue($loaded->relationLoaded('authors'));
        $this->assertTrue($loaded->relationLoaded('ingredients'));
        $this->assertTrue($loaded->relationLoaded('steps'));
    }

    /**
     * @test
     */
    public function it_stores_and_retrieves_all_attributes_correctly()
    {
        $data = [
            'name' => 'Test Recipe',
            'description' => 'A test recipe description',
            'slug' => 'test-recipe',
            'image_url' => 'https://example.com/image.jpg',
            'prep_time' => 15,
            'cook_time' => 30,
            'servings' => 4,
        ];

        $recipe = Recipe::create($data);
        $retrieved = Recipe::find($recipe->id);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $retrieved->{$key});
        }
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
    public function it_preserves_related_data_with_special_characters()
    {
        $recipe = Recipe::factory()->create();

        // Clear auto-generated relationships
        $recipe->ingredients()->delete();
        $recipe->steps()->delete();

        $recipe->ingredients()->create([
            'name' => 'Special "ingredient" with quotes',
            'quantity' => 1,
            'unit' => 'cup',
        ]);

        $recipe->steps()->create([
            'description' => 'Step with special characters: &, <, >, "',
            'order' => 1,
        ]);

        $retrieved = Recipe::with(['ingredients', 'steps'])->find($recipe->id);

        $this->assertEquals('Special "ingredient" with quotes', $retrieved->ingredients->first()->name);
        $this->assertEquals('Step with special characters: &, <, >, "', $retrieved->steps->first()->description);
    }

    /**
     * @test
     */
    public function it_can_access_ingredient_count()
    {
        $recipe = Recipe::factory()->create();
        $recipe->ingredients()->delete();
        $recipe->ingredients()->createMany([
            ['name' => 'Ingredient 1', 'quantity' => 1],
            ['name' => 'Ingredient 2', 'quantity' => 2],
            ['name' => 'Ingredient 3', 'quantity' => 3],
        ]);

        $this->assertEquals(3, $recipe->ingredient_count);
    }

    /**
     * @test
     */
    public function it_can_access_step_count()
    {
        $recipe = Recipe::factory()->create();
        $recipe->steps()->delete();
        $recipe->steps()->createMany([
            ['description' => 'Step 1', 'order' => 1],
            ['description' => 'Step 2', 'order' => 2],
        ]);

        $this->assertEquals(2, $recipe->step_count);
    }

    /**
     * @test
     */
    public function it_can_be_converted_to_array()
    {
        $recipe = Recipe::factory()->create([
            'name' => 'Test Recipe',
            'description' => 'Test description',
        ]);

        // Clear auto-generated relationships
        $recipe->authors()->delete();
        $recipe->ingredients()->delete();
        $recipe->steps()->delete();

        $recipe->authors()->create(['name' => 'Author', 'email' => 'author@example.com']);
        $recipe->ingredients()->create(['name' => 'Ingredient', 'quantity' => 1]);
        $recipe->steps()->create(['description' => 'Step', 'order' => 1]);

        // Refresh the model to include the relationships
        $recipe = $recipe->fresh(['authors', 'ingredients', 'steps']);
        $array = $recipe->toArray();

        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('authors', $array);
        $this->assertArrayHasKey('ingredients', $array);
        $this->assertArrayHasKey('steps', $array);
        $this->assertIsArray($array['authors']);
        $this->assertIsArray($array['ingredients']);
        $this->assertIsArray($array['steps']);
    }

    /**
     * @test
     */
    public function it_can_be_converted_to_json()
    {
        $recipe = Recipe::factory()->create();

        $json = $recipe->toJson();
        $decoded = json_decode($json, true);

        $this->assertIsString($json);
        $this->assertArrayHasKey('id', $decoded);
        $this->assertArrayHasKey('name', $decoded);
    }

    /**
     * @test
     */
    public function it_uses_factory_correctly()
    {
        $recipe = Recipe::factory()->create();

        $this->assertInstanceOf(Recipe::class, $recipe);
        $this->assertDatabaseHas('recipes', ['id' => $recipe->id]);
    }

    /**
     * @test
     */
    public function it_handles_mass_assignment()
    {
        $recipe = Recipe::create([
            'name' => 'Mass Assignment Test',
            'description' => 'Testing mass assignment',
            'slug' => 'mass-assignment-test',
        ]);

        $this->assertEquals('Mass Assignment Test', $recipe->name);
        $this->assertEquals('Testing mass assignment', $recipe->description);
        $this->assertEquals('mass-assignment-test', $recipe->slug);
    }

    /**
     * @test
     */
    public function it_gets_primary_author()
    {
        $recipe = Recipe::factory()->create();
        $recipe->authors()->delete();
        $author1 = $recipe->authors()->create(['name' => 'First Author', 'email' => 'first@example.com']);
        $author2 = $recipe->authors()->create(['name' => 'Second Author', 'email' => 'second@example.com']);

        $this->assertEquals($author1->id, $recipe->primary_author->id);
        $this->assertEquals('first@example.com', $recipe->author_email);
    }
}
