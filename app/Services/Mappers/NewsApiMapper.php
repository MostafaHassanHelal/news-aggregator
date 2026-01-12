<?php

declare(strict_types=1);

namespace App\Services\Mappers;

/**
 * Mapper for NewsAPI.org response format.
 * 
 * NewsAPI returns articles in the following format:
 * {
 *   "status": "ok",
 *   "totalResults": 100,
 *   "articles": [
 *     {
 *       "source": {"id": "...", "name": "..."},
 *       "author": "...",
 *       "title": "...",
 *       "description": "...",
 *       "url": "...",
 *       "urlToImage": "...",
 *       "publishedAt": "...",
 *       "content": "..."
 *     }
 *   ]
 * }
 */
class NewsApiMapper extends BaseArticleMapper
{
    /**
     * {@inheritdoc}
     */
    protected function extractArticles(array $rawData): array
    {
        return $rawData['articles'] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function mapSingle(array $rawArticle): ?array
    {
        // Skip articles with missing required fields
        if (empty($rawArticle['title']) || empty($rawArticle['url'])) {
            return null;
        }

        // Skip articles with "[Removed]" content (NewsAPI placeholder for removed articles)
        if ($rawArticle['title'] === '[Removed]') {
            return null;
        }

        return [
            'external_id' => $this->generateExternalId($rawArticle),
            'title' => $this->truncate($rawArticle['title'], 255),
            'description' => $this->truncate($rawArticle['description'] ?? null),
            'content' => $this->truncate($rawArticle['content'] ?? null),
            'author' => $this->truncate($rawArticle['author'] ?? null, 255),
            'url' => $rawArticle['url'],
            'image_url' => $rawArticle['urlToImage'] ?? null,
            'category' => $rawArticle['category'] ?? null,
            'published_at' => $this->parseDate($rawArticle['publishedAt'] ?? null),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function generateExternalId(array $rawArticle): string
    {
        // Use URL as unique identifier since NewsAPI doesn't provide article IDs
        return md5($rawArticle['url'] ?? uniqid());
    }
}
