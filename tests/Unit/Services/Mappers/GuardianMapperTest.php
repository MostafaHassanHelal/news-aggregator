<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Mappers;

use App\Services\Mappers\GuardianMapper;
use PHPUnit\Framework\TestCase;

class GuardianMapperTest extends TestCase
{
    private GuardianMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new GuardianMapper();
    }

    public function test_maps_valid_article(): void
    {
        $rawData = [
            'response' => [
                'results' => [
                    [
                        'id' => 'world/2026/jan/12/test-article',
                        'webTitle' => 'Test Guardian Article',
                        'webUrl' => 'https://theguardian.com/article',
                        'webPublicationDate' => '2026-01-12T10:00:00Z',
                        'sectionName' => 'World',
                        'fields' => [
                            'trailText' => 'Test trail text',
                            'body' => '<p>Test body content</p>',
                            'byline' => 'Jane Smith',
                            'thumbnail' => 'https://example.com/thumbnail.jpg',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertCount(1, $result);
        $this->assertEquals('world/2026/jan/12/test-article', $result[0]['external_id']);
        $this->assertEquals('Test Guardian Article', $result[0]['title']);
        $this->assertEquals('Test trail text', $result[0]['description']);
        $this->assertEquals('Test body content', $result[0]['content']); // HTML stripped
        $this->assertEquals('Jane Smith', $result[0]['author']);
        $this->assertEquals('https://theguardian.com/article', $result[0]['url']);
        $this->assertEquals('https://example.com/thumbnail.jpg', $result[0]['image_url']);
        $this->assertEquals('World', $result[0]['category']);
    }

    public function test_strips_html_from_body(): void
    {
        $rawData = [
            'response' => [
                'results' => [
                    [
                        'webTitle' => 'Article with HTML',
                        'webUrl' => 'https://theguardian.com/html-article',
                        'fields' => [
                            'body' => '<p>Paragraph one</p><p>Paragraph two</p><strong>Bold text</strong>',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertCount(1, $result);
        $this->assertEquals('Paragraph oneParagraph twoBold text', $result[0]['content']);
    }

    public function test_uses_section_id_when_section_name_missing(): void
    {
        $rawData = [
            'response' => [
                'results' => [
                    [
                        'webTitle' => 'Article',
                        'webUrl' => 'https://theguardian.com/article',
                        'sectionId' => 'technology',
                    ],
                ],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertCount(1, $result);
        $this->assertEquals('technology', $result[0]['category']);
    }

    public function test_skips_articles_without_title(): void
    {
        $rawData = [
            'response' => [
                'results' => [
                    [
                        'webUrl' => 'https://theguardian.com/article',
                    ],
                ],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertEmpty($result);
    }

    public function test_handles_empty_response(): void
    {
        $rawData = [
            'response' => [
                'results' => [],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertEmpty($result);
    }

    public function test_handles_missing_response_key(): void
    {
        $rawData = ['status' => 'ok'];

        $result = $this->mapper->map($rawData);

        $this->assertEmpty($result);
    }
}
