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
        Recipe::factory()->create([
            'name' => 'Chocolate Chip Cookies',
            'description' => 'Classic chocolate chip cookies with vanilla',
            'ingredients' => ['flour', 'chocolate chips', 'vanilla', 'butter', 'sugar'],
            'steps' => ['Mix dry ingredients', 'Add wet ingredients', 'Bake for 12 minutes'],
            'author_email' => 'baker@example.com',
        ]);

        Recipe::factory()->create([
            'name' => 'Vanilla Cake',
            'description' => 'Moist vanilla cake with buttercream frosting',
            'ingredients' => ['flour', 'vanilla', 'butter', 'sugar', 'eggs', 'milk'],
            'steps' => ['Mix ingredients', 'Pour into pan', 'Bake for 30 minutes', 'Add frosting'],
            'author_email' => 'chef@example.com',
        ]);

        Recipe::factory()->create([
            'name' => 'Beef Stew',
            'description' => 'Hearty beef stew with vegetables',
            'ingredients' => ['beef', 'carrots', 'potatoes', 'onions', 'broth'],
            'steps' => ['Brown beef', 'Add vegetables', 'Simmer for 2 hours'],
            'author_email' => 'chef@example.com',
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
                    ingredients
                    steps
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
                'ingredients' => ['ingredient1', 'ingredient2', 'ingredient3'],
                'steps' => ['step1', 'step2'],
                'author_email' => 'test@example.com',
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
                        'ingredients',
                        'steps',
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
        $this->assertEquals(3, $data['ingredient_count']);
        $this->assertEquals(2, $data['step_count']);
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
                        ingredients
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
            $this->assertContains('vanilla', $recipe['ingredients']);
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
                        ingredients
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
        $this->assertContains('flour', $data['data'][0]['ingredients']);
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
                    ingredients
                    steps
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
                        'ingredients',
                        'steps',
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
