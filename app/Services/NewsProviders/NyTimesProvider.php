<?php

declare(strict_types=1);

namespace App\Services\NewsProviders;

use App\Services\Mappers\NyTimesMapper;

/**
 * New York Times API provider implementation.
 * 
 * @see https://developer.nytimes.com/docs/articlesearch-product/1/overview
 */
class NyTimesProvider extends BaseNewsProvider
{
    protected string $baseUrl = 'https://api.nytimes.com/svc/search/v2';

    public function __construct(NyTimesMapper $mapper)
    {
        parent::__construct($mapper);
        $this->apiKey = config('services.nytimes.key', '');
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderName(): string
    {
        return 'nytimes';
    }

    /**
     * {@inheritdoc}
     */
    protected function makeRequest(array $filters = []): \Illuminate\Http\Client\Response
    {
        $params = $this->buildQueryParams($filters);
        return $this->httpGet("{$this->baseUrl}/articlesearch.json", $params);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildQueryParams(array $filters = []): array
    {
        $params = [
            'api-key' => $this->apiKey,
        ];

        // Add search query if provided (NYTimes requires at least one filter)
        if (!empty($filters['q'])) {
            $params['q'] = $filters['q'];
        } else {
            // Default to fetching recent news
            $params['q'] = '*';
        }

        // Build filter query (fq) for advanced filtering
        $filterQueries = [];

        // Add section/category filter
        if (!empty($filters['category'])) {
            $filterQueries[] = 'section_name:("' . $filters['category'] . '")';
        }

        // Add news desk filter
        if (!empty($filters['news_desk'])) {
            $filterQueries[] = 'news_desk:("' . $filters['news_desk'] . '")';
        }

        if (!empty($filterQueries)) {
            $params['fq'] = implode(' AND ', $filterQueries);
        }

        // Add date range filters
        if (!empty($filters['from'])) {
            $params['begin_date'] = $this->formatDate($filters['from']);
        }

        if (!empty($filters['to'])) {
            $params['end_date'] = $this->formatDate($filters['to']);
        }

        // Sort by newest
        $params['sort'] = 'newest';

        // Pagination
        $params['page'] = $filters['page'] ?? 0;

        return $params;
    }

    /**
     * Format date to NYTimes expected format (YYYYMMDD).
     *
     * @param string $date
     * @return string
     */
    private function formatDate(string $date): string
    {
        try {
            $dateTime = new \DateTime($date);
            return $dateTime->format('Ymd');
        } catch (\Exception $e) {
            return str_replace('-', '', $date);
        }
    }
}
