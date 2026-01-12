<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\NewsProviderInterface;
use App\DTOs\ArticleDTO;
use App\Models\Source;
use Illuminate\Support\Facades\Log;

/**
 * News Aggregator Service.
 * 
 * Coordinates fetching articles from all registered providers
 * and storing them in the database.
 * 
 * This service receives providers via dependency injection,
 * allowing new providers to be added without modifying this class (Open/Closed Principle).
 */
class NewsAggregatorService
{
    /**
     * @var array<NewsProviderInterface>
     */
    private array $providers;

    private ArticleRepositoryInterface $articleRepository;

    /**
     * @param ArticleRepositoryInterface $articleRepository
     * @param NewsProviderInterface ...$providers
     */
    public function __construct(
        ArticleRepositoryInterface $articleRepository,
        NewsProviderInterface ...$providers
    ) {
        $this->articleRepository = $articleRepository;
        $this->providers = $providers;
    }

    /**
     * Aggregate articles from all providers.
     *
     * @param array $filters Optional filters to pass to providers
     * @return array Summary of aggregation results
     */
    public function aggregateAll(array $filters = []): array
    {
        $results = [];

        foreach ($this->providers as $provider) {
            $providerName = $provider->getProviderName();
            
            try {
                $result = $this->aggregateFromProvider($provider, $filters);
                $results[$providerName] = $result;
                
                Log::info("Aggregation complete for {$providerName}", $result);
            } catch (\Exception $e) {
                $results[$providerName] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'fetched' => 0,
                    'stored' => 0,
                ];
                
                Log::error("Aggregation failed for {$providerName}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Aggregate articles from a specific provider.
     *
     * @param NewsProviderInterface $provider
     * @param array $filters
     * @return array
     */
    public function aggregateFromProvider(NewsProviderInterface $provider, array $filters = []): array
    {
        $providerName = $provider->getProviderName();
        
        // Get source from database
        $source = Source::where('api_name', $providerName)->first();
        
        if (!$source) {
            throw new \RuntimeException("Source not found for provider: {$providerName}");
        }

        if (!$source->is_active) {
            return [
                'success' => true,
                'message' => 'Source is inactive',
                'fetched' => 0,
                'stored' => 0,
            ];
        }

        // Fetch articles from provider
        $articles = $provider->fetch($filters);
        $fetchedCount = count($articles);

        // Convert to DTOs and store
        $articleDTOs = $this->convertToArticleDTOs($articles, $source->id);
        $storedCount = $this->articleRepository->upsertMany($articleDTOs);

        return [
            'success' => true,
            'fetched' => $fetchedCount,
            'stored' => $storedCount,
        ];
    }

    /**
     * Aggregate from a single provider by name.
     *
     * @param string $providerName
     * @param array $filters
     * @return array
     */
    public function aggregateByProviderName(string $providerName, array $filters = []): array
    {
        foreach ($this->providers as $provider) {
            if ($provider->getProviderName() === $providerName) {
                return $this->aggregateFromProvider($provider, $filters);
            }
        }

        throw new \InvalidArgumentException("Provider not found: {$providerName}");
    }

    /**
     * Convert raw article arrays to ArticleDTO instances.
     *
     * @param array $articles
     * @param int $sourceId
     * @return array<ArticleDTO>
     */
    private function convertToArticleDTOs(array $articles, int $sourceId): array
    {
        $dtos = [];

        foreach ($articles as $article) {
            try {
                $article['source_id'] = $sourceId;
                $dtos[] = ArticleDTO::fromArray($article);
            } catch (\Exception $e) {
                Log::warning("Failed to convert article to DTO", [
                    'error' => $e->getMessage(),
                    'article' => $article,
                ]);
            }
        }

        return $dtos;
    }

    /**
     * Get list of registered providers.
     *
     * @return array<string>
     */
    public function getProviderNames(): array
    {
        return array_map(
            fn(NewsProviderInterface $provider) => $provider->getProviderName(),
            $this->providers
        );
    }
}
