<?php

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GraphQLTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test recipes for search functionality
        $recipe1 = Recipe::factory()->create([
            'name' => 'Chocolate Chip Cookies',
            'description' => 'Classic chocolate chip cookies with vanilla',
        ]);

        // Clear auto-created relationships and create specific test data
        $recipe1->authors()->delete();
        $recipe1->ingredients()->delete();
        $recipe1->steps()->delete();

        $recipe1->authors()->create([
            'name' => 'Baker',
            'email' => 'baker@example.com',
        ]);

        $recipe1->ingredients()->createMany([
            ['name' => 'flour', 'quantity' => 2, 'unit' => 'cups'],
            ['name' => 'chocolate chips', 'quantity' => 1, 'unit' => 'cup'],
            ['name' => 'vanilla', 'quantity' => 1, 'unit' => 'tsp'],
            ['name' => 'butter', 'quantity' => 0.5, 'unit' => 'cup'],
            ['name' => 'sugar', 'quantity' => 0.75, 'unit' => 'cup'],
        ]);

        $recipe1->steps()->createMany([
            ['title' => 'Prep', 'description' => 'Mix dry ingredients', 'order' => 1],
            ['title' => 'Combine', 'description' => 'Add wet ingredients', 'order' => 2],
            ['title' => 'Bake', 'description' => 'Bake for 12 minutes', 'order' => 3],
        ]);

        $recipe2 = Recipe::factory()->create([
            'name' => 'Vanilla Cake',
            'description' => 'Moist vanilla cake with buttercream frosting',
        ]);

        $recipe2->authors()->delete();
        $recipe2->ingredients()->delete();
        $recipe2->steps()->delete();

        $recipe2->authors()->create([
            'name' => 'Chef',
            'email' => 'chef@example.com',
        ]);

        $recipe2->ingredients()->createMany([
            ['name' => 'flour', 'quantity' => 2.5, 'unit' => 'cups'],
            ['name' => 'vanilla', 'quantity' => 2, 'unit' => 'tsp'],
            ['name' => 'butter', 'quantity' => 1, 'unit' => 'cup'],
            ['name' => 'sugar', 'quantity' => 1.5, 'unit' => 'cups'],
            ['name' => 'eggs', 'quantity' => 3, 'unit' => ''],
            ['name' => 'milk', 'quantity' => 1, 'unit' => 'cup'],
        ]);

        $recipe2->steps()->createMany([
            ['title' => 'Mix', 'description' => 'Mix ingredients', 'order' => 1],
            ['title' => 'Pour', 'description' => 'Pour into pan', 'order' => 2],
            ['title' => 'Bake', 'description' => 'Bake for 30 minutes', 'order' => 3],
            ['title' => 'Frost', 'description' => 'Add frosting', 'order' => 4],
        ]);

        $recipe3 = Recipe::factory()->create([
            'name' => 'Beef Stew',
            'description' => 'Hearty beef stew with vegetables',
        ]);

        $recipe3->authors()->delete();
        $recipe3->ingredients()->delete();
        $recipe3->steps()->delete();

        $recipe3->authors()->create([
            'name' => 'Chef',
            'email' => 'chef@example.com',
        ]);

        $recipe3->ingredients()->createMany([
            ['name' => 'beef', 'quantity' => 2, 'unit' => 'lbs'],
            ['name' => 'carrots', 'quantity' => 3, 'unit' => ''],
            ['name' => 'potatoes', 'quantity' => 4, 'unit' => ''],
            ['name' => 'onions', 'quantity' => 1, 'unit' => ''],
            ['name' => 'broth', 'quantity' => 4, 'unit' => 'cups'],
        ]);

        $recipe3->steps()->createMany([
            ['title' => 'Brown', 'description' => 'Brown beef', 'order' => 1],
            ['title' => 'Add', 'description' => 'Add vegetables', 'order' => 2],
            ['title' => 'Simmer', 'description' => 'Simmer for 2 hours', 'order' => 3],
        ]);
    }

    public function test_health_check_returns_success()
    {
        $query = '
            query {
                health
            }
        ';

        $response = $this->postJson('/graphql', ['query' => $query]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'health' => 'GraphQL server is healthy!',
                ],
            ]);
    }

    public function test_can_create_recipe()
    {
        $mutation = '
            mutation CreateRecipe($input: CreateRecipeInput!) {
                createRecipe(input: $input) {
                    id
                    name
                    description
                    ingredients {
                        id
                        name
                        quantity
                        unit
                    }
                    steps {
                        id
                        title
                        description
                        order
                    }
                    author_email
                    slug
                    ingredient_count
                    step_count
                }
            }
        ';

        $variables = [
            'input' => [
                'name' => 'Test Recipe',
                'description' => 'A test recipe for unit testing',
                'authors' => [
                    [
                        'name' => 'Test Author',
                        'email' => 'test@example.com',
                    ],
                ],
                'ingredients' => [
                    [
                        'name' => 'ingredient1',
                        'quantity' => 1,
                        'unit' => 'cup',
                    ],
                    [
                        'name' => 'ingredient2',
                        'quantity' => 2,
                        'unit' => 'tbsp',
                    ],
                    [
                        'name' => 'ingredient3',
                        'quantity' => 0.5,
                        'unit' => 'tsp',
                    ],
                ],
                'steps' => [
                    [
                        'description' => 'step1',
                        'order' => 1,
                    ],
                    [
                        'description' => 'step2',
                        'order' => 2,
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/graphql', [
            'query' => $mutation,
            'variables' => $variables,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'createRecipe' => [
                        'id',
                        'name',
                        'description',
                        'ingredients' => [
                            '*' => [
                                'id',
                                'name',
                                'quantity',
                                'unit',
                            ],
                        ],
                        'steps' => [
                            '*' => [
                                'id',
                                'title',
                                'description',
                                'order',
                            ],
                        ],
                        'author_email',
                        'slug',
                        'ingredient_count',
                        'step_count',
                    ],
                ],
            ]);

        $data = $response->json('data.createRecipe');
        $this->assertEquals('Test Recipe', $data['name']);
        $this->assertEquals('test-recipe', $data['slug']);
        $this->assertEquals('test@example.com', $data['author_email']);
        $this->assertEquals(3, $data['ingredient_count']);
        $this->assertEquals(2, $data['step_count']);
        $this->assertCount(3, $data['ingredients']);
        $this->assertCount(2, $data['steps']);
    }

    public function test_can_search_recipes_without_filters()
    {
        $query = '
            query SearchRecipes($first: Int, $page: Int) {
                recipes(first: $first, page: $page) {
                    data {
                        id
                        name
                        slug
                        author_email
                        ingredient_count
                        step_count
                    }
                    paginatorInfo {
                        count
                        total
                        currentPage
                        lastPage
                        hasMorePages
                        perPage
                    }
                }
            }
        ';

        $variables = [
            'first' => 15,
            'page' => 1,
        ];

        $response = $this->postJson('/graphql', [
            'query' => $query,
            'variables' => $variables,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'recipes' => [
                        'data' => [
                            '*' => [
                                'id',
                                'name',
                                'slug',
                                'author_email',
                                'ingredient_count',
                                'step_count',
                            ],
                        ],
                        'paginatorInfo' => [
                            'count',
                            'total',
                            'currentPage',
                            'lastPage',
                            'hasMorePages',
                            'perPage',
                        ],
                    ],
                ],
            ]);

        $data = $response->json('data.recipes');
        $this->assertEquals(3, $data['paginatorInfo']['total']);
        $this->assertCount(3, $data['data']);
    }

    public function test_can_search_recipes_by_keyword()
    {
        $query = '
            query SearchRecipes($keyword: String, $first: Int, $page: Int) {
                recipes(keyword: $keyword, first: $first, page: $page) {
                    data {
                        id
                        name
                        description
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ';

        $variables = [
            'keyword' => 'chocolate',
            'first' => 15,
            'page' => 1,
        ];

        $response = $this->postJson('/graphql', [
            'query' => $query,
            'variables' => $variables,
        ]);

        $response->assertStatus(200);

        $data = $response->json('data.recipes');
        $this->assertEquals(1, $data['paginatorInfo']['total']);
        $this->assertStringContainsString('Chocolate', $data['data'][0]['name']);
    }

    public function test_can_search_recipes_by_ingredient()
    {
        $query = '
            query SearchRecipes($ingredient: String, $first: Int, $page: Int) {
                recipes(ingredient: $ingredient, first: $first, page: $page) {
                    data {
                        id
                        name
                        ingredients {
                            name
                        }
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ';

        $variables = [
            'ingredient' => 'vanilla',
            'first' => 15,
            'page' => 1,
        ];

        $response = $this->postJson('/graphql', [
            'query' => $query,
            'variables' => $variables,
        ]);

        $response->assertStatus(200);

        $data = $response->json('data.recipes');
        $this->assertEquals(2, $data['paginatorInfo']['total']);

        foreach ($data['data'] as $recipe) {
            $ingredientNames = array_column($recipe['ingredients'], 'name');
            $this->assertContains('vanilla', $ingredientNames);
        }
    }

    public function test_can_search_recipes_by_author_email()
    {
        $query = '
            query SearchRecipes($author_email: String, $first: Int, $page: Int) {
                recipes(author_email: $author_email, first: $first, page: $page) {
                    data {
                        id
                        name
                        author_email
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ';

        $variables = [
            'author_email' => 'chef@example.com',
            'first' => 15,
            'page' => 1,
        ];

        $response = $this->postJson('/graphql', [
            'query' => $query,
            'variables' => $variables,
        ]);

        $response->assertStatus(200);

        $data = $response->json('data.recipes');
        $this->assertEquals(2, $data['paginatorInfo']['total']);

        foreach ($data['data'] as $recipe) {
            $this->assertEquals('chef@example.com', $recipe['author_email']);
        }
    }

    public function test_can_combine_multiple_search_filters()
    {
        $query = '
            query SearchRecipes($author_email: String, $ingredient: String, $first: Int, $page: Int) {
                recipes(author_email: $author_email, ingredient: $ingredient, first: $first, page: $page) {
                    data {
                        id
                        name
                        author_email
                        ingredients {
                            name
                        }
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ';

        $variables = [
            'author_email' => 'chef@example.com',
            'ingredient' => 'flour',
            'first' => 15,
            'page' => 1,
        ];

        $response = $this->postJson('/graphql', [
            'query' => $query,
            'variables' => $variables,
        ]);

        $response->assertStatus(200);

        $data = $response->json('data.recipes');
        $this->assertEquals(1, $data['paginatorInfo']['total']);
        $this->assertEquals('chef@example.com', $data['data'][0]['author_email']);
        $ingredientNames = array_column($data['data'][0]['ingredients'], 'name');
        $this->assertContains('flour', $ingredientNames);
    }

    public function test_can_get_single_recipe_by_slug()
    {
        $recipe = Recipe::first();

        $query = '
            query GetRecipe($slug: String!) {
                recipe(slug: $slug) {
                    id
                    name
                    description
                    ingredients {
                        id
                        name
                        quantity
                        unit
                    }
                    steps {
                        id
                        title
                        description
                        order
                    }
                    author_email
                    slug
                    ingredient_count
                    step_count
                    created_at
                    updated_at
                }
            }
        ';

        $variables = [
            'slug' => $recipe->slug,
        ];

        $response = $this->postJson('/graphql', [
            'query' => $query,
            'variables' => $variables,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'recipe' => [
                        'id',
                        'name',
                        'description',
                        'ingredients' => [
                            '*' => [
                                'id',
                                'name',
                                'quantity',
                                'unit',
                            ],
                        ],
                        'steps' => [
                            '*' => [
                                'id',
                                'title',
                                'description',
                                'order',
                            ],
                        ],
                        'author_email',
                        'slug',
                        'ingredient_count',
                        'step_count',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        $data = $response->json('data.recipe');
        $this->assertEquals($recipe->name, $data['name']);
        $this->assertEquals($recipe->slug, $data['slug']);
    }

    public function test_pagination_works_correctly()
    {
        // Create additional recipes to test pagination
        Recipe::factory()->count(10)->create();

        $query = '
            query SearchRecipes($first: Int, $page: Int) {
                recipes(first: $first, page: $page) {
                    data {
                        id
                        name
                    }
                    paginatorInfo {
                        count
                        total
                        currentPage
                        lastPage
                        hasMorePages
                        perPage
                    }
                }
            }
        ';

        // Test first page
        $variables = ['first' => 5, 'page' => 1];
        $response = $this->postJson('/graphql', ['query' => $query, 'variables' => $variables]);

        $response->assertStatus(200);
        $data = $response->json('data.recipes');

        $this->assertEquals(5, $data['paginatorInfo']['count']);
        $this->assertEquals(13, $data['paginatorInfo']['total']); // 3 initial + 10 new
        $this->assertEquals(1, $data['paginatorInfo']['currentPage']);
        $this->assertTrue($data['paginatorInfo']['hasMorePages']);
        $this->assertCount(5, $data['data']);

        // Test second page
        $variables = ['first' => 5, 'page' => 2];
        $response = $this->postJson('/graphql', ['query' => $query, 'variables' => $variables]);

        $response->assertStatus(200);
        $data = $response->json('data.recipes');

        $this->assertEquals(5, $data['paginatorInfo']['count']);
        $this->assertEquals(2, $data['paginatorInfo']['currentPage']);
        $this->assertTrue($data['paginatorInfo']['hasMorePages']);
    }

    public function test_returns_empty_result_for_no_matches()
    {
        $query = '
            query SearchRecipes($keyword: String, $first: Int, $page: Int) {
                recipes(keyword: $keyword, first: $first, page: $page) {
                    data {
                        id
                        name
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ';

        $variables = [
            'keyword' => 'nonexistentrecipe',
            'first' => 15,
            'page' => 1,
        ];

        $response = $this->postJson('/graphql', [
            'query' => $query,
            'variables' => $variables,
        ]);

        $response->assertStatus(200);

        $data = $response->json('data.recipes');
        $this->assertEquals(0, $data['paginatorInfo']['total']);
        $this->assertEmpty($data['data']);
    }

    public function test_create_recipe_validates_required_fields()
    {
        $mutation = '
            mutation CreateRecipe($input: CreateRecipeInput!) {
                createRecipe(input: $input) {
                    id
                    name
                }
            }
        ';

        $variables = [
            'input' => [
                'name' => '',
                'description' => '',
                'ingredients' => [],
                'steps' => [],
                'author_email' => 'invalid-email',
            ],
        ];

        $response = $this->postJson('/graphql', [
            'query' => $mutation,
            'variables' => $variables,
        ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('errors', $response->json());
    }

    public function test_returns_null_for_nonexistent_recipe_slug()
    {
        $query = '
            query GetRecipe($slug: String!) {
                recipe(slug: $slug) {
                    id
                    name
                }
            }
        ';

        $variables = [
            'slug' => 'nonexistent-recipe-slug',
        ];

        $response = $this->postJson('/graphql', [
            'query' => $query,
            'variables' => $variables,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'recipe' => null,
                ],
            ]);
    }
}
