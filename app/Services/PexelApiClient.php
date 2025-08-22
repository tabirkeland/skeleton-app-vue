<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
}
