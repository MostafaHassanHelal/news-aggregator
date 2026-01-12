<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Interface for news provider implementations.
 * 
 * Each news provider (NewsAPI, Guardian, NYTimes, etc.) must implement this interface.
 * This is the Strategy Pattern - allowing different providers to be used interchangeably.
 */
interface NewsProviderInterface
{
    /**
     * Fetch articles from the news provider.
     *
     * @param array $filters Optional filters for the API request
     * @return array Array of normalized article data
     */
    public function fetch(array $filters = []): array;

    /**
     * Get the unique identifier for this provider.
     *
     * @return string Provider identifier (e.g., 'newsapi', 'guardian', 'nytimes')
     */
    public function getProviderName(): string;

    /**
     * Check if the provider is currently enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool;
}
