<?php

namespace App\Providers;

use App\Services\PrintCheck\OpenAIAnalysisService;
use App\Services\PrintCheck\PdfAnalysisService;
use App\Services\PrintCheck\PdfMetadataService;
use Illuminate\Support\ServiceProvider;
use Smalot\PdfParser\Parser;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Parser::class, fn () => new Parser());

        $this->app->singleton(PdfMetadataService::class, fn ($app) =>
            new PdfMetadataService($app->make(Parser::class))
        );

        $this->app->singleton(OpenAIAnalysisService::class, fn () =>
            new OpenAIAnalysisService()
        );

        $this->app->singleton(PdfAnalysisService::class, fn ($app) =>
            new PdfAnalysisService(
                $app->make(PdfMetadataService::class),
                $app->make(OpenAIAnalysisService::class),
            )
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
