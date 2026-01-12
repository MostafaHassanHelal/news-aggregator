<?php

declare(strict_types=1);

namespace App\Services\NewsProviders;

use App\Contracts\ArticleMapperInterface;
use App\Contracts\NewsProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Base abstract class for news providers.
 * 
 * Provides common functionality for all providers including:
 * - HTTP client handling
 * - Error logging
 * - Response mapping
 */
abstract class BaseNewsProvider implements NewsProviderInterface
{
    protected ArticleMapperInterface $mapper;
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct(ArticleMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(array $filters = []): array
    {
        if (!$this->isEnabled()) {
            Log::info("Provider {$this->getProviderName()} is disabled, skipping fetch");
            return [];
        }

        try {
            $response = $this->makeRequest($filters);

            if (!$response->successful()) {
                Log::error("API request failed for {$this->getProviderName()}", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            return $this->mapper->map($response->json() ?? []);
        } catch (\Exception $e) {
            Log::error("Exception fetching from {$this->getProviderName()}", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Make the HTTP request to the API.
     *
     * @param array $filters
     * @return \Illuminate\Http\Client\Response
     */
    abstract protected function makeRequest(array $filters = []): \Illuminate\Http\Client\Response;

    /**
     * Build query parameters for the API request.
     *
     * @param array $filters
     * @return array
     */
    abstract protected function buildQueryParams(array $filters = []): array;

    /**
     * Make a GET request with timeout and retry handling.
     *
     * @param string $url
     * @param array $params
     * @return \Illuminate\Http\Client\Response
     */
    protected function httpGet(string $url, array $params = []): \Illuminate\Http\Client\Response
    {
        return Http::timeout(30)
            ->retry(3, 100)
            ->get($url, $params);
    }
}
