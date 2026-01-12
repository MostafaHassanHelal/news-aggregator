<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs;

use App\DTOs\ArticleDTO;
use PHPUnit\Framework\TestCase;

class ArticleDTOTest extends TestCase
{
    public function test_creates_dto_from_array(): void
    {
        $data = [
            'source_id' => 1,
            'external_id' => 'ext-123',
            'title' => 'Test Article',
            'description' => 'Test description',
            'content' => 'Test content',
            'author' => 'John Doe',
            'url' => 'https://example.com/article',
            'image_url' => 'https://example.com/image.jpg',
            'category' => 'Technology',
            'published_at' => '2026-01-12 10:00:00',
        ];

        $dto = ArticleDTO::fromArray($data);

        $this->assertEquals(1, $dto->getSourceId());
        $this->assertEquals('ext-123', $dto->getExternalId());
        $this->assertEquals('Test Article', $dto->getTitle());
        $this->assertEquals('Test description', $dto->getDescription());
        $this->assertEquals('Test content', $dto->getContent());
        $this->assertEquals('John Doe', $dto->getAuthor());
        $this->assertEquals('https://example.com/article', $dto->getUrl());
        $this->assertEquals('https://example.com/image.jpg', $dto->getImageUrl());
        $this->assertEquals('Technology', $dto->getCategory());
        $this->assertNotNull($dto->getPublishedAt());
    }

    public function test_handles_null_optional_fields(): void
    {
        $data = [
            'source_id' => 1,
            'external_id' => 'ext-123',
            'title' => 'Test Article',
            'url' => 'https://example.com/article',
        ];

        $dto = ArticleDTO::fromArray($data);

        $this->assertNull($dto->getDescription());
        $this->assertNull($dto->getContent());
        $this->assertNull($dto->getAuthor());
        $this->assertNull($dto->getImageUrl());
        $this->assertNull($dto->getCategory());
        $this->assertNull($dto->getPublishedAt());
    }

    public function test_converts_to_array(): void
    {
        $data = [
            'source_id' => 1,
            'external_id' => 'ext-123',
            'title' => 'Test Article',
            'description' => 'Test description',
            'content' => 'Test content',
            'author' => 'John Doe',
            'url' => 'https://example.com/article',
            'image_url' => 'https://example.com/image.jpg',
            'category' => 'Technology',
            'published_at' => '2026-01-12 10:00:00',
        ];

        $dto = ArticleDTO::fromArray($data);
        $array = $dto->toArray();

        $this->assertEquals(1, $array['source_id']);
        $this->assertEquals('ext-123', $array['external_id']);
        $this->assertEquals('Test Article', $array['title']);
        $this->assertEquals('Test description', $array['description']);
        $this->assertEquals('Test content', $array['content']);
        $this->assertEquals('John Doe', $array['author']);
        $this->assertEquals('https://example.com/article', $array['url']);
        $this->assertEquals('https://example.com/image.jpg', $array['image_url']);
        $this->assertEquals('Technology', $array['category']);
        $this->assertEquals('2026-01-12 10:00:00', $array['published_at']);
    }

    public function test_to_array_with_null_published_at(): void
    {
        $dto = ArticleDTO::fromArray([
            'source_id' => 1,
            'external_id' => 'ext-123',
            'title' => 'Test Article',
            'url' => 'https://example.com/article',
        ]);

        $array = $dto->toArray();

        $this->assertNull($array['published_at']);
    }
}
