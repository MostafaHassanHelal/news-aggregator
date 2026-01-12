<?php

declare(strict_types=1);

namespace App\DTOs;

use DateTimeInterface;

/**
 * Data Transfer Object for Article data.
 * 
 * Provides a strongly-typed, immutable container for article data
 * that flows between layers of the application.
 */
class ArticleDTO
{
    public function __construct(
        public readonly int $sourceId,
        public readonly string $externalId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $content,
        public readonly ?string $author,
        public readonly string $url,
        public readonly ?string $imageUrl,
        public readonly ?string $category,
        public readonly ?DateTimeInterface $publishedAt
    ) {}

    /**
     * Create DTO from array.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sourceId: (int) $data['source_id'],
            externalId: (string) $data['external_id'],
            title: (string) $data['title'],
            description: $data['description'] ?? null,
            content: $data['content'] ?? null,
            author: $data['author'] ?? null,
            url: (string) $data['url'],
            imageUrl: $data['image_url'] ?? null,
            category: $data['category'] ?? null,
            publishedAt: isset($data['published_at']) ? new \DateTime($data['published_at']) : null
        );
    }

    /**
     * Convert DTO to array for database operations.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'source_id' => $this->sourceId,
            'external_id' => $this->externalId,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'author' => $this->author,
            'url' => $this->url,
            'image_url' => $this->imageUrl,
            'category' => $this->category,
            'published_at' => $this->publishedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
