<?php

declare(strict_types=1);

namespace App\Services\Mappers;

/**
 * Mapper for The Guardian API response format.
 * 
 * The Guardian returns articles in the following format:
 * {
 *   "response": {
 *     "status": "ok",
 *     "total": 100,
 *     "results": [
 *       {
 *         "id": "...",
 *         "type": "article",
 *         "sectionId": "...",
 *         "sectionName": "...",
 *         "webPublicationDate": "...",
 *         "webTitle": "...",
 *         "webUrl": "...",
 *         "apiUrl": "...",
 *         "fields": {
 *           "headline": "...",
 *           "trailText": "...",
 *           "body": "...",
 *           "byline": "...",
 *           "thumbnail": "..."
 *         }
 *       }
 *     ]
 *   }
 * }
 */
class GuardianMapper extends BaseArticleMapper
{
    /**
     * {@inheritdoc}
     */
    protected function extractArticles(array $rawData): array
    {
        return $rawData['response']['results'] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function mapSingle(array $rawArticle): ?array
    {
        // Skip articles with missing required fields
        if (empty($rawArticle['webTitle']) || empty($rawArticle['webUrl'])) {
            return null;
        }

        $fields = $rawArticle['fields'] ?? [];

        return [
            'external_id' => $rawArticle['id'] ?? $this->generateExternalId($rawArticle),
            'title' => $this->truncate($rawArticle['webTitle'], 255),
            'description' => $this->truncate($fields['trailText'] ?? null),
            'content' => $this->truncate($this->stripHtml($fields['body'] ?? null)),
            'author' => $this->truncate($fields['byline'] ?? null, 255),
            'url' => $rawArticle['webUrl'],
            'image_url' => $fields['thumbnail'] ?? null,
            'category' => $rawArticle['sectionName'] ?? $rawArticle['sectionId'] ?? null,
            'published_at' => $this->parseDate($rawArticle['webPublicationDate'] ?? null),
        ];
    }

    /**
     * Strip HTML tags from content.
     *
     * @param string|null $html
     * @return string|null
     */
    private function stripHtml(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        return strip_tags($html);
    }
}
