<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\NewsProviderInterface;
use App\Repositories\ArticleRepository;
use App\Services\NewsAggregatorService;
use App\Services\NewsProviders\GuardianProvider;
use App\Services\NewsProviders\NewsApiProvider;
use App\Services\NewsProviders\NyTimesProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public array $bindings = [
        ArticleRepositoryInterface::class => ArticleRepository::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Register the NewsAggregatorService with all providers
        $this->app->singleton(NewsAggregatorService::class, function ($app) {
            return new NewsAggregatorService(
                $app->make(ArticleRepositoryInterface::class),
                $app->make(NewsApiProvider::class),
                $app->make(GuardianProvider::class),
                $app->make(NyTimesProvider::class)
            );
        });

        // Tag all news providers for potential iteration
        $this->app->tag([
            NewsApiProvider::class,
            GuardianProvider::class,
            NyTimesProvider::class,
        ], NewsProviderInterface::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
