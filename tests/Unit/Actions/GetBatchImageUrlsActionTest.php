<?php

namespace Tests\Unit\Actions;

use App\Actions\Recipe\GetBatchImageUrlsAction;
use App\Models\Recipe;
use App\Services\PexelApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GetBatchImageUrlsActionTest extends TestCase
{
    use RefreshDatabase;

    protected GetBatchImageUrlsAction $action;

    protected PexelApiClient $mockClient;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear cache before each test
        Cache::flush();

        // Create mock PexelApiClient
        $this->mockClient = $this->createMock(PexelApiClient::class);

        // Bind mock to Laravel's container
        $this->app->instance(PexelApiClient::class, $this->mockClient);

        // Create action (will use mocked client from container)
        $this->action = app(GetBatchImageUrlsAction::class);
    }

    /** @test */
    public function it_returns_existing_image_urls_without_api_calls()
    {
        // Create recipes with existing image URLs
        $recipe1 = Recipe::factory()->create(['image_url' => 'https://example.com/image1.jpg']);
        $recipe2 = Recipe::factory()->create(['image_url' => 'https://example.com/image2.jpg']);

        // Mock should never be called
        $this->mockClient->expects($this->never())
            ->method('batchSearchPhotos');

        // Execute action
        $result = $this->action->execute([$recipe1, $recipe2]);

        // Assert results
        $this->assertCount(2, $result);
        $this->assertEquals('https://example.com/image1.jpg', $result[$recipe1->id]);
        $this->assertEquals('https://example.com/image2.jpg', $result[$recipe2->id]);
    }

    /** @test */
    public function it_fetches_images_for_recipes_without_urls()
    {
        // Create recipes without image URLs
        $recipe1 = Recipe::factory()->create(['image_url' => '', 'name' => 'Chocolate Cake', 'category' => 'Dessert']);
        $recipe2 = Recipe::factory()->create(['image_url' => '', 'name' => 'Pasta Carbonara', 'category' => 'Pasta']);

        // Mock API response with properly normalized keys
        $this->mockClient->expects($this->once())
            ->method('batchSearchPhotos')
            ->willReturn([
                'chocolate cake dessert sweet' => [
                    'photos' => [
                        ['src' => ['medium' => 'https://pexels.com/chocolate.jpg']],
                    ],
                ],
                'pasta carbonara pasta dish' => [
                    'photos' => [
                        ['src' => ['medium' => 'https://pexels.com/pasta.jpg']],
                    ],
                ],
            ]);

        // Execute action
        $result = $this->action->execute([$recipe1, $recipe2]);

        // Assert results
        $this->assertCount(2, $result);
        $this->assertStringContainsString('pexels.com', $result[$recipe1->id]);
        $this->assertStringContainsString('pexels.com', $result[$recipe2->id]);
    }

    /** @test */
    public function it_uses_cache_for_subsequent_requests()
    {
        // Create recipes with consistent category
        $recipes = Recipe::factory()->count(3)->create([
            'image_url' => '',
            'category' => 'Main Course',
            'name' => 'Test Recipe',
        ]);

        // Mock API response - should only be called once
        $this->mockClient->expects($this->once())
            ->method('batchSearchPhotos')
            ->willReturnCallback(function ($queries) {
                $result = [];
                foreach ($queries as $query) {
                    $result[$query] = [
                        'photos' => [
                            ['src' => ['medium' => 'https://pexels.com/test.jpg']],
                        ],
                    ];
                }

                return $result;
            });

        // First execution - hits API
        $result1 = $this->action->execute($recipes);

        // Second execution - should use cache
        $result2 = $this->action->execute($recipes);

        // Results should be identical
        $this->assertEquals($result1, $result2);
    }

    /** @test */
    public function it_deduplicates_search_queries()
    {
        // Create recipes with similar names that would generate same query
        $recipe1 = Recipe::factory()->create(['image_url' => '', 'name' => 'Chocolate Cake', 'category' => 'Dessert']);
        $recipe2 = Recipe::factory()->create(['image_url' => '', 'name' => 'CHOCOLATE CAKE', 'category' => 'Dessert']);
        $recipe3 = Recipe::factory()->create(['image_url' => '', 'name' => 'chocolate  cake', 'category' => 'Dessert']);

        // Mock should receive deduplicated queries
        $this->mockClient->expects($this->once())
            ->method('batchSearchPhotos')
            ->with(
                $this->callback(function ($queries) {
                    // Should only have one unique normalized query
                    return count($queries) === 1;
                }),
                $this->anything()
            )
            ->willReturn([
                'chocolate cake dessert sweet' => [
                    'photos' => [
                        ['src' => ['medium' => 'https://pexels.com/chocolate.jpg']],
                    ],
                ],
            ]);

        // Execute action
        $result = $this->action->execute([$recipe1, $recipe2, $recipe3]);

        // All recipes should get the same image
        $this->assertEquals($result[$recipe1->id], $result[$recipe2->id]);
        $this->assertEquals($result[$recipe2->id], $result[$recipe3->id]);
    }

    /** @test */
    public function it_handles_mixed_recipes_with_and_without_images()
    {
        // Create mixed recipes
        $recipeWithImage = Recipe::factory()->create(['image_url' => 'https://example.com/existing.jpg']);
        $recipeWithoutImage = Recipe::factory()->create(['image_url' => '', 'name' => 'New Recipe', 'category' => 'Main Course']);

        // Mock should only fetch for recipe without image
        $this->mockClient->expects($this->once())
            ->method('batchSearchPhotos')
            ->with(
                $this->callback(function ($queries) {
                    return count($queries) === 1;
                }),
                $this->anything()
            )
            ->willReturnCallback(function ($queries) {
                $result = [];
                foreach ($queries as $query) {
                    $result[$query] = [
                        'photos' => [
                            ['src' => ['medium' => 'https://pexels.com/new.jpg']],
                        ],
                    ];
                }

                return $result;
            });

        // Execute action
        $result = $this->action->execute([$recipeWithImage, $recipeWithoutImage]);

        // Assert results
        $this->assertEquals('https://example.com/existing.jpg', $result[$recipeWithImage->id]);
        $this->assertStringContainsString('pexels.com', $result[$recipeWithoutImage->id]);
    }

    /** @test */
    public function it_returns_placeholder_when_no_photos_found()
    {
        // Create recipe
        $recipe = Recipe::factory()->create(['image_url' => '', 'name' => 'Unknown Dish', 'category' => 'Main Course']);

        // Mock empty API response
        $this->mockClient->expects($this->once())
            ->method('batchSearchPhotos')
            ->willReturnCallback(function ($queries) {
                $result = [];
                foreach ($queries as $query) {
                    $result[$query] = ['photos' => []];
                }

                return $result;
            });

        // Execute action
        $result = $this->action->execute([$recipe]);

        // Should return placeholder
        $this->assertStringContainsString('placehold.co', $result[$recipe->id]);
        $this->assertStringContainsString(str_replace(' ', '+', $recipe->name), $result[$recipe->id]);
    }
}
