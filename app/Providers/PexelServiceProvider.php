<?php

namespace App\Providers;

use App\Services\PexelApiClient;
use Illuminate\Support\ServiceProvider;

class PexelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PexelApiClient::class, function () {
            return new PexelApiClient(
                apiKey: config('services.pexels.api_key', ''),
                baseUrl: config('services.pexels.base_url', 'https://api.pexels.com/v1')
            );
        });
    }
}
