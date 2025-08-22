<?php

namespace Tests\Unit\Actions;

use App\Actions\Recipe\CreateRecipeAction;
use App\Exceptions\RecipeCreationException;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

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
            'authors' => [
                ['name' => 'Test Author', 'email' => 'test@example.com', 'about' => 'A test author'],
            ],
            'ingredients' => [
                ['name' => 'ingredient 1', 'quantity' => 1, 'unit' => 'cup'],
                ['name' => 'ingredient 2', 'quantity' => 2, 'unit' => 'tbsp'],
            ],
            'steps' => [
                ['title' => 'Step 1', 'description' => 'Do step 1', 'order' => 1],
                ['title' => 'Step 2', 'description' => 'Do step 2', 'order' => 2],
            ],
        ];

        $recipe = $this->action->execute($data);

        $this->assertInstanceOf(Recipe::class, $recipe);
        $this->assertEquals('Test Recipe', $recipe->name);
        $this->assertEquals('test-recipe', $recipe->slug);
        $this->assertEquals('test@example.com', $recipe->author_email);
        $this->assertCount(1, $recipe->authors);
        $this->assertCount(2, $recipe->ingredients);
        $this->assertCount(2, $recipe->steps);
        $this->assertDatabaseHas('recipes', [
            'name' => 'Test Recipe',
            'slug' => 'test-recipe',
        ]);
        $this->assertDatabaseHas('recipe_authors', [
            'email' => 'test@example.com',
        ]);
    }

    /**
     * @test
     */
    public function it_generates_unique_slug_for_duplicate_names()
    {
        Recipe::factory()->create([
            'name' => 'Duplicate Recipe',
            'slug' => 'duplicate-recipe',
        ]);

        $data = [
            'name' => 'Duplicate Recipe',
            'description' => 'Another duplicate recipe',
            'authors' => [
                ['name' => 'Author', 'email' => 'author@example.com'],
            ],
            'ingredients' => [
                ['name' => 'ingredient', 'quantity' => 1],
            ],
            'steps' => [
                ['description' => 'Step 1'],
            ],
        ];

        $recipe = $this->action->execute($data);

        $this->assertEquals('duplicate-recipe-1', $recipe->slug);
    }

    /**
     * @test
     */
    public function it_handles_multiple_duplicate_slugs()
    {
        Recipe::factory()->create(['name' => 'Recipe', 'slug' => 'recipe']);
        Recipe::factory()->create(['name' => 'Recipe', 'slug' => 'recipe-1']);

        $data = [
            'name' => 'Recipe',
            'description' => 'Another recipe',
            'authors' => [
                ['name' => 'Author', 'email' => 'new@example.com'],
            ],
            'ingredients' => [
                ['name' => 'ingredient', 'quantity' => 1],
            ],
            'steps' => [
                ['description' => 'Step 1'],
            ],
        ];

        $recipe = $this->action->execute($data);

        $this->assertEquals('recipe-2', $recipe->slug);
    }

    /**
     * @test
     */
    public function it_uses_database_transaction()
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $data = [
            'name' => 'Transaction Test',
            'description' => 'Testing transaction',
            'authors' => [
                ['name' => 'Author', 'email' => 'trans@example.com'],
            ],
            'ingredients' => [
                ['name' => 'ingredient', 'quantity' => 1],
            ],
            'steps' => [
                ['description' => 'Step 1'],
            ],
        ];

        $this->action->execute($data);
    }

    /**
     * @test
     */
    public function it_handles_special_characters_in_recipe_name()
    {
        $data = [
            'name' => 'Recipe with Special Characters & Symbols!',
            'description' => 'A recipe with special chars',
            'authors' => [
                ['name' => 'Author', 'email' => 'special@example.com'],
            ],
            'ingredients' => [
                ['name' => 'ingredient', 'quantity' => 1],
            ],
            'steps' => [
                ['description' => 'Step 1'],
            ],
        ];

        $recipe = $this->action->execute($data);

        $this->assertEquals('recipe-with-special-characters-symbols', $recipe->slug);
    }

    /**
     * @test
     */
    public function it_preserves_array_structure_for_ingredients_and_steps()
    {
        $data = [
            'name' => 'Complex Recipe',
            'description' => 'A complex recipe',
            'authors' => [
                ['name' => 'Chef', 'email' => 'chef@example.com'],
            ],
            'ingredients' => [
                ['name' => 'Special ingredient', 'quantity' => 2.5, 'unit' => 'cups'],
                ['name' => 'Another ingredient', 'quantity' => 1, 'unit' => 'tbsp'],
            ],
            'steps' => [
                ['title' => 'First step', 'description' => 'Do this first', 'order' => 1],
                ['title' => 'Second step', 'description' => 'Then do this', 'order' => 2],
            ],
        ];

        $recipe = $this->action->execute($data);

        $this->assertEquals('Special ingredient', $recipe->ingredients->first()->name);
        $this->assertEquals(2.5, $recipe->ingredients->first()->quantity);
        $this->assertEquals('First step', $recipe->steps->first()->title);
    }

    /**
     * @test
     */
    public function it_handles_empty_arrays_for_ingredients_and_steps()
    {
        $data = [
            'name' => 'Empty Recipe',
            'description' => 'A recipe with no ingredients or steps',
            'authors' => [
                ['name' => 'Author', 'email' => 'empty@example.com'],
            ],
            'ingredients' => [],
            'steps' => [],
        ];

        $recipe = $this->action->execute($data);

        $this->assertCount(0, $recipe->ingredients);
        $this->assertCount(0, $recipe->steps);
    }

    /**
     * @test
     */
    public function it_normalizes_email_to_lowercase()
    {
        $data = [
            'name' => 'Recipe',
            'description' => 'Description',
            'authors' => [
                ['name' => 'Author', 'email' => 'TEST@EXAMPLE.COM'],
            ],
            'ingredients' => [
                ['name' => 'ingredient', 'quantity' => 1],
            ],
            'steps' => [
                ['description' => 'Step 1'],
            ],
        ];

        $recipe = $this->action->execute($data);

        $this->assertEquals('test@example.com', $recipe->authors->first()->email);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_database_error_occurs()
    {
        $this->expectException(RecipeCreationException::class);

        $action = new CreateRecipeAction(new Recipe());

        // Create invalid data that will cause a database constraint violation
        $data = [
            'name' => str_repeat('a', 300), // Exceeds max length constraint
            'description' => 'Description',
            'authors' => [
                ['name' => 'Author', 'email' => 'test@example.com'],
            ],
            'ingredients' => [
                ['name' => 'ingredient1'],
            ],
            'steps' => [
                ['description' => 'step1'],
            ],
        ];

        $action->execute($data);
    }

    /**
     * @test
     */
    public function it_generates_slug_from_unicode_characters()
    {
        $data = [
            'name' => 'Crème Brûlée',
            'description' => 'French dessert',
            'authors' => [
                ['name' => 'Chef', 'email' => 'french@example.com'],
            ],
            'ingredients' => [
                ['name' => 'cream', 'quantity' => 1, 'unit' => 'cup'],
            ],
            'steps' => [
                ['description' => 'Make dessert'],
            ],
        ];

        $recipe = $this->action->execute($data);

        $this->assertEquals('creme-brulee', $recipe->slug);
    }
}
