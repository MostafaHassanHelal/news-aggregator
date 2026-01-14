<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\DTOs\ArticleDTO;
use App\Models\Article;
use App\Models\Source;
use App\Repositories\ArticleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ArticleRepository $repository;
    private Source $source;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ArticleRepository();
        $this->source = Source::factory()->create();
    }

    public function test_upsert_creates_new_article(): void
    {
        $dto = new ArticleDTO(
            sourceId: $this->source->id,
            externalId: 'unique-article-123',
            title: 'Test Article',
            description: 'Test description',
            content: 'Test content',
            author: 'Test Author',
            url: 'https://example.com/article',
            imageUrl: 'https://example.com/image.jpg',
            category: 'Technology',
            publishedAt: new \DateTime('2026-01-14')
        );

        $this->repository->upsert($dto);

        $this->assertDatabaseCount('articles', 1);
        $this->assertDatabaseHas('articles', [
            'source_id' => $this->source->id,
            'external_id' => 'unique-article-123',
            'title' => 'Test Article',
        ]);
    }

    public function test_upsert_updates_existing_article_instead_of_duplicating(): void
    {
        // Create first version
        $dto1 = new ArticleDTO(
            sourceId: $this->source->id,
            externalId: 'duplicate-test-456',
            title: 'Original Title',
            description: 'Original description',
            content: null,
            author: null,
            url: 'https://example.com/article',
            imageUrl: null,
            category: null,
            publishedAt: null
        );

        $this->repository->upsert($dto1);
        $this->assertDatabaseCount('articles', 1);

        // Try to insert same article with updated content
        $dto2 = new ArticleDTO(
            sourceId: $this->source->id,
            externalId: 'duplicate-test-456', // Same external_id
            title: 'Updated Title',
            description: 'Updated description',
            content: 'New content',
            author: 'New Author',
            url: 'https://example.com/article',
            imageUrl: null,
            category: 'News',
            publishedAt: null
        );

        $this->repository->upsert($dto2);

        // Should still be only 1 record, not 2
        $this->assertDatabaseCount('articles', 1);

        // Should have updated values
        $this->assertDatabaseHas('articles', [
            'source_id' => $this->source->id,
            'external_id' => 'duplicate-test-456',
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);
    }

    public function test_upsert_many_prevents_duplicates(): void
    {
        $dtos = [
            new ArticleDTO(
                sourceId: $this->source->id,
                externalId: 'batch-article-1',
                title: 'Article 1',
                description: null,
                content: null,
                author: null,
                url: 'https://example.com/1',
                imageUrl: null,
                category: null,
                publishedAt: null
            ),
            new ArticleDTO(
                sourceId: $this->source->id,
                externalId: 'batch-article-2',
                title: 'Article 2',
                description: null,
                content: null,
                author: null,
                url: 'https://example.com/2',
                imageUrl: null,
                category: null,
                publishedAt: null
            ),
            new ArticleDTO(
                sourceId: $this->source->id,
                externalId: 'batch-article-1', // Duplicate!
                title: 'Article 1 Updated',
                description: null,
                content: null,
                author: null,
                url: 'https://example.com/1',
                imageUrl: null,
                category: null,
                publishedAt: null
            ),
        ];

        $processed = $this->repository->upsertMany($dtos);

        // All 3 were processed
        $this->assertEquals(3, $processed);

        // But only 2 unique articles in DB
        $this->assertDatabaseCount('articles', 2);

        // The duplicate was updated, not inserted
        $this->assertDatabaseHas('articles', [
            'external_id' => 'batch-article-1',
            'title' => 'Article 1 Updated', // Updated title
        ]);
    }

    public function test_same_external_id_different_sources_are_separate_articles(): void
    {
        $source2 = Source::factory()->create();

        $dto1 = new ArticleDTO(
            sourceId: $this->source->id,
            externalId: 'shared-external-id',
            title: 'Article from Source 1',
            description: null,
            content: null,
            author: null,
            url: 'https://source1.com/article',
            imageUrl: null,
            category: null,
            publishedAt: null
        );

        $dto2 = new ArticleDTO(
            sourceId: $source2->id,
            externalId: 'shared-external-id', // Same external_id, different source
            title: 'Article from Source 2',
            description: null,
            content: null,
            author: null,
            url: 'https://source2.com/article',
            imageUrl: null,
            category: null,
            publishedAt: null
        );

        $this->repository->upsert($dto1);
        $this->repository->upsert($dto2);

        // Should have 2 separate articles (different sources)
        $this->assertDatabaseCount('articles', 2);
    }
}
