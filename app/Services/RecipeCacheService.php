<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RecipeCacheService
{
    private const CACHE_PREFIX = 'recipes:';
    private const DEFAULT_TTL = 3600; // 1 hour

    /**
     * Generate a cache key for recipe search queries.
     *
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return string
     */
    public function generateSearchKey(array $filters, int $page, int $perPage): string
    {
        // Sort filters to ensure consistent cache keys
        ksort($filters);
        
        // Create a deterministic key from filters
        $filterString = json_encode(array_filter($filters));
        $hash = md5($filterString . $page . $perPage);
        
        return self::CACHE_PREFIX . 'search:' . $hash;
    }

    /**
     * Generate a cache key for a single recipe.
     *
     * @param string $slug
     * @return string
     */
    public function generateRecipeKey(string $slug): string
    {
        return self::CACHE_PREFIX . 'recipe:' . $slug;
    }

    /**
     * Remember a value in cache or execute the callback if not found.
     *
     * @param string $key
     * @param \Closure $callback
     * @param int|null $ttl
     * @return mixed
     */
    public function remember(string $key, \Closure $callback, ?int $ttl = null)
    {
        $ttl = $ttl ?? self::DEFAULT_TTL;
        
        try {
            return Cache::remember($key, $ttl, $callback);
        } catch (\Exception $e) {
            // Log cache failures but don't break the application
            Log::warning('Cache operation failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            // Fall back to executing the callback without caching
            return $callback();
        }
    }

    /**
     * Forget cache entries matching a pattern.
     *
     * @param string $pattern
     * @return void
     */
    public function forgetPattern(string $pattern): void
    {
        try {
            // For Redis, we can use pattern matching
            if (config('cache.default') === 'redis') {
                $keys = Cache::getRedis()->keys(Cache::getPrefix() . $pattern);
                foreach ($keys as $key) {
                    Cache::forget(str_replace(Cache::getPrefix(), '', $key));
                }
            }
        } catch (\Exception $e) {
            Log::warning('Cache invalidation failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clear all recipe-related cache entries.
     *
     * @return void
     */
    public function clearRecipeCache(): void
    {
        $this->forgetPattern(self::CACHE_PREFIX . '*');
    }

    /**
     * Warm up cache with frequently accessed data.
     *
     * @return void
     */
    public function warmCache(): void
    {
        try {
            // Cache recent recipes
            $recentKey = self::CACHE_PREFIX . 'recent:7days';
            Cache::remember($recentKey, self::DEFAULT_TTL, function () {
                return \App\Models\Recipe::query()
                    ->recent(7)
                    ->limit(20)
                    ->get();
            });

            // Cache popular recipes
            $popularKey = self::CACHE_PREFIX . 'popular:all';
            Cache::remember($popularKey, self::DEFAULT_TTL, function () {
                return \App\Models\Recipe::query()
                    ->popular()
                    ->limit(20)
                    ->get();
            });
            
            Log::info('Recipe cache warmed successfully');
        } catch (\Exception $e) {
            Log::error('Cache warming failed', ['error' => $e->getMessage()]);
        }
    }
}