<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Aws\Rekognition\RekognitionClient;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->bind(RekognitionClient::class, function ($app) {
            return new RekognitionClient([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION'),
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
