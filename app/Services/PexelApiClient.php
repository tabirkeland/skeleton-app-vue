<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PexelApiClient
{
    public function __construct(protected string $apiKey, protected string $baseUrl)
    {
    }

    /**
     * Search for photos based on query
     *
     * @param  string  $query The search query (required)
     * @param  array  $options Additional search options:
     *   - per_page: int (1-80, default: 1)
     *   - page: int (default: 1)
     *   - orientation: string (landscape|portrait|square)
     *   - size: string (large|medium|small)
     *   - color: string (red, orange, yellow, green, turquoise, blue, violet, pink, brown, black, gray, white or hex)
     *   - locale: string (en-US, fr-FR, etc.)
     */
    public function searchPhotos(string $query, array $options = []): ?array
    {
        // For testing, return placeholder URL
        if (app()->environment('testing')) {
            return $this->getPlaceholderResponse($query);
        }

        // Build query parameters
        $params = [
            'query' => $query,
            'per_page' => min(80, $options['per_page'] ?? 1), // Max 80 per Pexels API
            'page' => $options['page'] ?? 1,
        ];

        // Add optional parameters if provided
        if (isset($options['orientation'])
            && in_array($options['orientation'], ['landscape', 'portrait', 'square'])
        ) {
            $params['orientation'] = $options['orientation'];
        }

        if (isset($options['size']) && in_array($options['size'], ['large', 'medium', 'small'])) {
            $params['size'] = $options['size'];
        }

        if (isset($options['color'])) {
            $params['color'] = $options['color'];
        }

        if (isset($options['locale'])) {
            $params['locale'] = $options['locale'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->get("{$this->baseUrl}/search", $params);

            if ($response->successful()) {
                // Store rate limit headers for tracking
                $this->storeRateLimitInfo($response->headers());

                // Log rate limit headers if available
                if ($response->hasHeader('X-Ratelimit-Limit')) {
                    Log::debug('Pexels API rate limits', [
                        'limit' => $response->header('X-Ratelimit-Limit'),
                        'remaining' => $response->header('X-Ratelimit-Remaining'),
                        'reset' => $response->header('X-Ratelimit-Reset'),
                    ]);
                }

                return $response->json();
            }

            // Handle specific error codes
            if ($response->status() === 401) {
                Log::error('Pexels API authentication failed - check API key');
            } elseif ($response->status() === 429) {
                Log::warning('Pexels API rate limit exceeded');
            } else {
                Log::warning('Pexels API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $this->getPlaceholderResponse($query);

        } catch (\Exception $e) {
            Log::error('Pexels API exception', [
                'message' => $e->getMessage(),
                'query' => $query,
            ]);

            return $this->getPlaceholderResponse($query);
        }
    }

    /**
     * Get placeholder response for testing
     */
    protected function getPlaceholderResponse(string $query): array
    {
        $encodedQuery = urlencode($query);

        return [
            'photos' => [
                [
                    'src' => [
                        'original' => "https://placehold.co/1920x1080?text={$encodedQuery}",
                        'large2x' => "https://placehold.co/1920x1080?text={$encodedQuery}",
                        'large' => "https://placehold.co/940x650?text={$encodedQuery}",
                        'medium' => "https://placehold.co/640x480?text={$encodedQuery}",
                        'small' => "https://placehold.co/320x240?text={$encodedQuery}",
                    ],
                    'alt' => $query,
                    'photographer' => 'Placeholder',
                ],
            ],
        ];
    }

    /**
     * Search for photos in batch, minimizing API calls.
     * Takes multiple queries and returns a map of query => photos.
     *
     * @param  array  $queries Array of search queries
     * @param  array  $options Search options to apply to all queries
     * @return array Map of normalized query => photos array
     */
    public function batchSearchPhotos(array $queries, array $options = []): array
    {
        // Normalize and deduplicate queries
        $normalizedQueries = collect($queries)
            ->map(fn ($q) => $this->normalizeQuery($q))
            ->unique()
            ->values()
            ->all();

        $results = [];

        // For testing, return placeholder for all queries
        if (app()->environment('testing')) {
            foreach ($normalizedQueries as $query) {
                $results[$query] = $this->getPlaceholderResponse($query);
            }

            return $results;
        }

        // Check rate limits before making requests
        if (!$this->checkRateLimit(count($normalizedQueries))) {
            Log::warning('Pexels API rate limit would be exceeded, using placeholders', [
                'queries_count' => count($normalizedQueries),
                'remaining' => $this->getRemainingRequests(),
            ]);

            foreach ($normalizedQueries as $query) {
                $results[$query] = $this->getPlaceholderResponse($query);
            }

            return $results;
        }

        // Fetch photos for each unique query
        foreach ($normalizedQueries as $query) {
            // Check cache first
            $cacheKey = $this->getCacheKey($query, $options);
            $cached = Cache::get($cacheKey);

            if ($cached !== null) {
                Log::info('CACHE HIT for query', ['query' => $query, 'cache_key' => $cacheKey]);
                $results[$query] = $cached;

                continue;
            }

            Log::info('CACHE MISS - Making API call', ['query' => $query, 'cache_key' => $cacheKey]);

            // Track API calls for testing
            $callCount = Cache::get('test_api_call_count', 0);
            Cache::put('test_api_call_count', $callCount + 1, 3600);

            // Fetch from API
            $response = $this->searchPhotos($query, $options);

            if ($response) {
                // Cache for 24 hours as per best practices
                Cache::put($cacheKey, $response, now()->addHours(24));
                Log::info('CACHED API response', ['query' => $query, 'cache_key' => $cacheKey]);
                $results[$query] = $response;
            } else {
                $results[$query] = $this->getPlaceholderResponse($query);
            }
        }

        return $results;
    }

    /**
     * Normalize a search query for consistent caching.
     */
    protected function normalizeQuery(string $query): string
    {
        return Str::of($query)
            ->trim()
            ->lower()
            ->replaceMatches('/\s+/', ' ')
            ->value();
    }

    /**
     * Generate cache key for a query and options.
     */
    protected function getCacheKey(string $query, array $options): string
    {
        $normalized = $this->normalizeQuery($query);
        $optionsHash = md5(json_encode($options));

        return "pexels_photos_{$normalized}_{$optionsHash}";
    }

    /**
     * Check if we have enough rate limit remaining for the given number of requests.
     */
    protected function checkRateLimit(int $requestsNeeded): bool
    {
        $remaining = $this->getRemainingRequests();

        // Keep a buffer of 10 requests
        return $remaining === null || $remaining > ($requestsNeeded + 10);
    }

    /**
     * Get remaining API requests from cache.
     */
    protected function getRemainingRequests(): ?int
    {
        return Cache::get('pexels_rate_limit_remaining');
    }

    /**
     * Store rate limit information from response headers.
     */
    protected function storeRateLimitInfo(array $headers): void
    {
        if (isset($headers['X-Ratelimit-Remaining'])) {
            Cache::put(
                'pexels_rate_limit_remaining',
                (int) $headers['X-Ratelimit-Remaining'][0],
                now()->addMinutes(5)
            );
        }

        if (isset($headers['X-Ratelimit-Reset'])) {
            Cache::put(
                'pexels_rate_limit_reset',
                (int) $headers['X-Ratelimit-Reset'][0],
                now()->addHours(1)
            );
        }
    }
}
