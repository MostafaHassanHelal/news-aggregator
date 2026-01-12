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
    private int $sourceId;
    private string $externalId;
    private string $title;
    private ?string $description;
    private ?string $content;
    private ?string $author;
    private string $url;
    private ?string $imageUrl;
    private ?string $category;
    private ?DateTimeInterface $publishedAt;

    public function __construct(
        int $sourceId,
        string $externalId,
        string $title,
        ?string $description,
        ?string $content,
        ?string $author,
        string $url,
        ?string $imageUrl,
        ?string $category,
        ?DateTimeInterface $publishedAt
    ) {
        $this->sourceId = $sourceId;
        $this->externalId = $externalId;
        $this->title = $title;
        $this->description = $description;
        $this->content = $content;
        $this->author = $author;
        $this->url = $url;
        $this->imageUrl = $imageUrl;
        $this->category = $category;
        $this->publishedAt = $publishedAt;
    }

    // Getters for read-only access
    public function getSourceId(): int { return $this->sourceId; }
    public function getExternalId(): string { return $this->externalId; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getContent(): ?string { return $this->content; }
    public function getAuthor(): ?string { return $this->author; }
    public function getUrl(): string { return $this->url; }
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function getCategory(): ?string { return $this->category; }
    public function getPublishedAt(): ?DateTimeInterface { return $this->publishedAt; }

    /**
     * Create DTO from array.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['source_id'],
            (string) $data['external_id'],
            (string) $data['title'],
            $data['description'] ?? null,
            $data['content'] ?? null,
            $data['author'] ?? null,
            (string) $data['url'],
            $data['image_url'] ?? null,
            $data['category'] ?? null,
            isset($data['published_at']) ? new \DateTime($data['published_at']) : null
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
            'published_at' => $this->publishedAt !== null ? $this->publishedAt->format('Y-m-d H:i:s') : null,
        ];
    }
}
