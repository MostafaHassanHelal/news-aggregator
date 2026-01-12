<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Interface for article mappers.
 * 
 * Each news provider returns data in a different format.
 * Mappers normalize this data into a consistent internal structure.
 * This is the Adapter/Mapper Pattern.
 */
interface ArticleMapperInterface
{
    /**
     * Map raw API response to normalized article data.
     *
     * @param array $rawData Raw data from the news API
     * @return array Array of normalized article arrays
     */
    public function map(array $rawData): array;

    /**
     * Map a single article from raw API format to normalized format.
     *
     * @param array $rawArticle Single article from API response
     * @return array|null Normalized article data or null if invalid
     */
    public function mapSingle(array $rawArticle): ?array;
}
