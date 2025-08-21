<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use App\Models\Recipe;
use App\Actions\Recipe\CreateRecipeAction;
use App\Exceptions\RecipeCreationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class CreateRecipeActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateRecipeAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateRecipeAction(new Recipe());
    }

    /**
     * @test
     */
    public function it_creates_a_recipe_with_valid_data()
    {
        $data = [
            'name' => 'Test Recipe',
            'description' => 'A test recipe description',
            'ingredients' => ['ingredient 1', 'ingredient 2'],
            'steps' => ['Step 1', 'Step 2'],
            'author_email' => 'test@example.com'
        ];

        $recipe = $this->action->execute($data);

        $this->assertInstanceOf(Recipe::class, $recipe);
        $this->assertEquals('Test Recipe', $recipe->name);
        $this->assertEquals('test-recipe', $recipe->slug);
        $this->assertEquals('test@example.com', $recipe->author_email);
        $this->assertDatabaseHas('recipes', [
            'name' => 'Test Recipe',
            'slug' => 'test-recipe'
        ]);
    }

    /**
     * @test
     */
    public function it_generates_unique_slug_for_duplicate_names()
    {
        Recipe::factory()->create([
            'name' => 'Duplicate Recipe',
            'slug' => 'duplicate-recipe'
        ]);

        $data = [
            'name' => 'Duplicate Recipe',
            'description' => 'Another recipe with same name',
            'ingredients' => ['ingredient'],
            'steps' => ['step'],
            'author_email' => 'test@example.com'
        ];

        $recipe = $this->action->execute($data);

        $this->assertEquals('duplicate-recipe-1', $recipe->slug);
        $this->assertDatabaseHas('recipes', [
            'slug' => 'duplicate-recipe-1'
        ]);
    }

    /**
     * @test
     */
    public function it_handles_multiple_duplicate_slugs()
    {
        Recipe::factory()->create(['slug' => 'test-recipe']);
        Recipe::factory()->create(['slug' => 'test-recipe-1']);

        $data = [
            'name' => 'Test Recipe',
            'description' => 'Description',
            'ingredients' => ['ingredient'],
            'steps' => ['step'],
            'author_email' => 'test@example.com'
        ];

        $recipe = $this->action->execute($data);

        $this->assertEquals('test-recipe-2', $recipe->slug);
    }

    /**
     * @test
     */
    public function it_uses_database_transaction()
    {
        $data = [
            'name' => 'Transaction Test',
            'description' => 'Testing transaction',
            'ingredients' => ['ingredient'],
            'steps' => ['step'],
            'author_email' => 'test@example.com'
        ];

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $this->action->execute($data);
    }

    /**
     * @test
     */
    public function it_handles_special_characters_in_recipe_name()
    {
        $data = [
            'name' => "Chef's Special Recipe & More!",
            'description' => 'Description',
            'ingredients' => ['ingredient'],
            'steps' => ['step'],
            'author_email' => 'test@example.com'
        ];

        $recipe = $this->action->execute($data);

        $this->assertEquals('chefs-special-recipe-more', $recipe->slug);
    }

    /**
     * @test
     */
    public function it_preserves_array_structure_for_ingredients_and_steps()
    {
        $ingredients = ['2 cups flour', '1 egg', '1/2 cup milk'];
        $steps = ['Mix dry ingredients', 'Add wet ingredients', 'Bake for 30 minutes'];

        $data = [
            'name' => 'Array Test Recipe',
            'description' => 'Testing arrays',
            'ingredients' => $ingredients,
            'steps' => $steps,
            'author_email' => 'test@example.com'
        ];

        $recipe = $this->action->execute($data);

        $this->assertEquals($ingredients, $recipe->ingredients);
        $this->assertEquals($steps, $recipe->steps);
    }

    /**
     * @test
     */
    public function it_handles_empty_arrays_for_ingredients_and_steps()
    {
        $data = [
            'name' => 'Empty Arrays Recipe',
            'description' => 'Testing empty arrays',
            'ingredients' => [],
            'steps' => [],
            'author_email' => 'test@example.com'
        ];

        $recipe = $this->action->execute($data);

        $this->assertIsArray($recipe->ingredients);
        $this->assertEmpty($recipe->ingredients);
        $this->assertIsArray($recipe->steps);
        $this->assertEmpty($recipe->steps);
    }

    /**
     * @test
     */
    public function it_normalizes_email_to_lowercase()
    {
        $data = [
            'name' => 'Email Test',
            'description' => 'Testing email normalization',
            'ingredients' => ['ingredient'],
            'steps' => ['step'],
            'author_email' => 'TEST@EXAMPLE.COM'
        ];

        $recipe = $this->action->execute($data);

        $this->assertEquals('test@example.com', $recipe->author_email);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_database_error_occurs()
    {
        $this->expectException(RecipeCreationException::class);

        DB::shouldReceive('transaction')
            ->once()
            ->andThrow(new \Exception('Database error'));

        $data = [
            'name' => 'Test Recipe',
            'description' => 'Description',
            'ingredients' => ['ingredient'],
            'steps' => ['step'],
            'author_email' => 'test@example.com'
        ];

        $this->action->execute($data);
    }

    /**
     * @test
     */
    public function it_generates_slug_from_unicode_characters()
    {
        $data = [
            'name' => 'Crème Brûlée',
            'description' => 'French dessert',
            'ingredients' => ['cream', 'sugar'],
            'steps' => ['Mix', 'Torch'],
            'author_email' => 'test@example.com'
        ];

        $recipe = $this->action->execute($data);

        $this->assertEquals('creme-brulee', $recipe->slug);
    }
}