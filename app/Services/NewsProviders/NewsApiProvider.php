<?php

declare(strict_types=1);

namespace App\Services\NewsProviders;

use App\Services\Mappers\NewsApiMapper;

/**
 * NewsAPI.org provider implementation.
 * 
 * @see https://newsapi.org/docs
 */
class NewsApiProvider extends BaseNewsProvider
{
    protected string $baseUrl = 'https://newsapi.org/v2';

    public function __construct(NewsApiMapper $mapper)
    {
        parent::__construct($mapper);
        $this->apiKey = (string) config('services.newsapi.key');
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderName(): string
    {
        return 'newsapi';
    }

    /**
     * {@inheritdoc}
     */
    protected function makeRequest(array $filters = []): \Illuminate\Http\Client\Response
    {
        $endpoint = $this->determineEndpoint($filters);
        $params = $this->buildQueryParams($filters);

        return $this->httpGet("{$this->baseUrl}/{$endpoint}", $params);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildQueryParams(array $filters = []): array
    {
        $params = [
            'apiKey' => $this->apiKey,
            'pageSize' => $filters['limit'] ?? 100,
            'language' => $filters['language'] ?? 'en',
        ];

        // Add search query if provided
        if (!empty($filters['q'])) {
            $params['q'] = $filters['q'];
        }

        // Add category filter (only for top-headlines endpoint)
        if (!empty($filters['category'])) {
            $params['category'] = $filters['category'];
        }

        // Add date range filters
        if (!empty($filters['from'])) {
            $params['from'] = $filters['from'];
        }

        if (!empty($filters['to'])) {
            $params['to'] = $filters['to'];
        }

        // Add source filter
        if (!empty($filters['sources'])) {
            $params['sources'] = is_array($filters['sources']) 
                ? implode(',', $filters['sources']) 
                : $filters['sources'];
        }

        return $params;
    }

    /**
     * Determine which endpoint to use based on filters.
     *
     * @param array $filters
     * @return string
     */
    private function determineEndpoint(array $filters): string
    {
        // Use 'everything' endpoint for search queries, otherwise 'top-headlines'
        if (!empty($filters['q']) || !empty($filters['sources'])) {
            return 'everything';
        }

        return 'top-headlines';
    }
}
