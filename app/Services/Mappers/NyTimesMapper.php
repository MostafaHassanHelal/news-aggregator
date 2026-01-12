<?php

declare(strict_types=1);

namespace App\Services\Mappers;

/**
 * Mapper for New York Times API response format.
 * 
 * NYTimes Article Search API returns articles in the following format:
 * {
 *   "status": "OK",
 *   "response": {
 *     "docs": [
 *       {
 *         "_id": "...",
 *         "web_url": "...",
 *         "snippet": "...",
 *         "lead_paragraph": "...",
 *         "abstract": "...",
 *         "headline": {"main": "...", "kicker": "..."},
 *         "byline": {"original": "By ...", "person": [...]},
 *         "multimedia": [{"url": "..."}],
 *         "pub_date": "...",
 *         "section_name": "...",
 *         "news_desk": "..."
 *       }
 *     ]
 *   }
 * }
 */
class NyTimesMapper extends BaseArticleMapper
{
    private const IMAGE_BASE_URL = 'https://static01.nyt.com/';

    /**
     * {@inheritdoc}
     */
    protected function extractArticles(array $rawData): array
    {
        return $rawData['response']['docs'] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function mapSingle(array $rawArticle): ?array
    {
        $headline = $rawArticle['headline']['main'] ?? null;
        $url = $rawArticle['web_url'] ?? null;

        // Skip articles with missing required fields
        if (empty($headline) || empty($url)) {
            return null;
        }

        return [
            'external_id' => $rawArticle['_id'] ?? $this->generateExternalId($rawArticle),
            'title' => $this->truncate($headline, 255),
            'description' => $this->truncate($rawArticle['abstract'] ?? $rawArticle['snippet'] ?? null),
            'content' => $this->truncate($rawArticle['lead_paragraph'] ?? null),
            'author' => $this->truncate($this->extractAuthor($rawArticle), 255),
            'url' => $url,
            'image_url' => $this->extractImageUrl($rawArticle),
            'category' => $rawArticle['section_name'] ?? $rawArticle['news_desk'] ?? null,
            'published_at' => $this->parseDate($rawArticle['pub_date'] ?? null),
        ];
    }

    /**
     * Extract author from byline data.
     *
     * @param array $rawArticle
     * @return string|null
     */
    private function extractAuthor(array $rawArticle): ?string
    {
        $byline = $rawArticle['byline'] ?? [];

        if (!empty($byline['original'])) {
            // Remove "By " prefix if present
            return preg_replace('/^By\s+/i', '', $byline['original']);
        }

        if (!empty($byline['person']) && is_array($byline['person'])) {
            $names = array_map(function ($person) {
                return trim(($person['firstname'] ?? '') . ' ' . ($person['lastname'] ?? ''));
            }, $byline['person']);

            return implode(', ', array_filter($names));
        }

        return null;
    }

    /**
     * Extract the best available image URL.
     *
     * @param array $rawArticle
     * @return string|null
     */
    private function extractImageUrl(array $rawArticle): ?string
    {
        $multimedia = $rawArticle['multimedia'] ?? [];

        if (empty($multimedia)) {
            return null;
        }

        // Find the best image (prefer larger images)
        $preferredTypes = ['xlarge', 'large', 'mediumThreeByTwo440', 'mediumThreeByTwo210'];

        foreach ($preferredTypes as $type) {
            foreach ($multimedia as $media) {
                if (isset($media['subtype']) && $media['subtype'] === $type) {
                    return self::IMAGE_BASE_URL . $media['url'];
                }
            }
        }

        // Fallback to first available image
        $firstImage = $multimedia[0] ?? null;
        if ($firstImage && isset($firstImage['url'])) {
            return self::IMAGE_BASE_URL . $firstImage['url'];
        }

        return null;
    }
}
