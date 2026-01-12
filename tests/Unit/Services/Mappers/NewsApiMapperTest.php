<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Mappers;

use App\Services\Mappers\NewsApiMapper;
use PHPUnit\Framework\TestCase;

class NewsApiMapperTest extends TestCase
{
    private NewsApiMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new NewsApiMapper();
    }

    public function test_maps_valid_article(): void
    {
        $rawData = [
            'articles' => [
                [
                    'title' => 'Test Article Title',
                    'description' => 'Test description',
                    'content' => 'Test content',
                    'author' => 'John Doe',
                    'url' => 'https://example.com/article',
                    'urlToImage' => 'https://example.com/image.jpg',
                    'publishedAt' => '2026-01-12T10:00:00Z',
                ],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertCount(1, $result);
        $this->assertEquals('Test Article Title', $result[0]['title']);
        $this->assertEquals('Test description', $result[0]['description']);
        $this->assertEquals('Test content', $result[0]['content']);
        $this->assertEquals('John Doe', $result[0]['author']);
        $this->assertEquals('https://example.com/article', $result[0]['url']);
        $this->assertEquals('https://example.com/image.jpg', $result[0]['image_url']);
        $this->assertNotNull($result[0]['external_id']);
    }

    public function test_skips_removed_articles(): void
    {
        $rawData = [
            'articles' => [
                [
                    'title' => '[Removed]',
                    'url' => 'https://example.com/removed',
                ],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertEmpty($result);
    }

    public function test_skips_articles_without_title(): void
    {
        $rawData = [
            'articles' => [
                [
                    'url' => 'https://example.com/article',
                    'description' => 'No title article',
                ],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertEmpty($result);
    }

    public function test_skips_articles_without_url(): void
    {
        $rawData = [
            'articles' => [
                [
                    'title' => 'Article without URL',
                ],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertEmpty($result);
    }

    public function test_handles_empty_response(): void
    {
        $rawData = ['articles' => []];

        $result = $this->mapper->map($rawData);

        $this->assertEmpty($result);
    }

    public function test_handles_missing_articles_key(): void
    {
        $rawData = ['status' => 'ok'];

        $result = $this->mapper->map($rawData);

        $this->assertEmpty($result);
    }

    public function test_handles_null_optional_fields(): void
    {
        $rawData = [
            'articles' => [
                [
                    'title' => 'Minimal Article',
                    'url' => 'https://example.com/minimal',
                ],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertCount(1, $result);
        $this->assertEquals('Minimal Article', $result[0]['title']);
        $this->assertNull($result[0]['description']);
        $this->assertNull($result[0]['content']);
        $this->assertNull($result[0]['author']);
        $this->assertNull($result[0]['image_url']);
    }
}
