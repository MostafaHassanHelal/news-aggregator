<?php

declare(strict_types=1);

namespace App\Services\Mappers;

use App\Contracts\ArticleMapperInterface;

/**
 * Base abstract class for article mappers.
 * 
 * Provides common functionality for all mappers.
 */
abstract class BaseArticleMapper implements ArticleMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function map(array $rawData): array
    {
        $articles = $this->extractArticles($rawData);
        $mappedArticles = [];

        foreach ($articles as $rawArticle) {
            $mapped = $this->mapSingle($rawArticle);
            if ($mapped !== null) {
                $mappedArticles[] = $mapped;
            }
        }

        return $mappedArticles;
    }

    /**
     * Extract the articles array from the raw API response.
     *
     * @param array $rawData
     * @return array
     */
    abstract protected function extractArticles(array $rawData): array;

    /**
     * Generate a unique external ID for an article.
     *
     * @param array $rawArticle
     * @return string
     */
    protected function generateExternalId(array $rawArticle): string
    {
        // Default implementation uses URL hash
        $url = $rawArticle['url'] ?? $rawArticle['web_url'] ?? '';
        return md5($url);
    }

    /**
     * Parse a date string to a standard format.
     *
     * @param string|null $dateString
     * @return string|null
     */
    protected function parseDate(?string $dateString): ?string
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            $date = new \DateTime($dateString);
            return $date->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Truncate a string to a maximum length.
     *
     * @param string|null $text
     * @param int $maxLength
     * @return string|null
     */
    protected function truncate(?string $text, int $maxLength = 65535): ?string
    {
        if ($text === null) {
            return null;
        }

        return mb_substr($text, 0, $maxLength);
    }
}
