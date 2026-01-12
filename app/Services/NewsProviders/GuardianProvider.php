<?php

declare(strict_types=1);

namespace App\Services\NewsProviders;

use App\Services\Mappers\GuardianMapper;

/**
 * The Guardian API provider implementation.
 * 
 * @see https://open-platform.theguardian.com/documentation/
 */
class GuardianProvider extends BaseNewsProvider
{
    protected string $baseUrl = 'https://content.guardianapis.com';

    public function __construct(GuardianMapper $mapper)
    {
        parent::__construct($mapper);
        $this->apiKey = config('services.guardian.key', '');
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderName(): string
    {
        return 'guardian';
    }

    /**
     * {@inheritdoc}
     */
    protected function makeRequest(array $filters = []): \Illuminate\Http\Client\Response
    {
        $params = $this->buildQueryParams($filters);
        return $this->httpGet("{$this->baseUrl}/search", $params);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildQueryParams(array $filters = []): array
    {
        $params = [
            'api-key' => $this->apiKey,
            'page-size' => $filters['limit'] ?? 50,
            'show-fields' => 'headline,trailText,body,byline,thumbnail',
            'order-by' => 'newest',
        ];

        // Add search query if provided
        if (!empty($filters['q'])) {
            $params['q'] = $filters['q'];
        }

        // Add section/category filter
        if (!empty($filters['category'])) {
            $params['section'] = strtolower($filters['category']);
        }

        // Add date range filters
        if (!empty($filters['from'])) {
            $params['from-date'] = $filters['from'];
        }

        if (!empty($filters['to'])) {
            $params['to-date'] = $filters['to'];
        }

        return $params;
    }
}
