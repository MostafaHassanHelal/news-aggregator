<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Mappers;

use App\Services\Mappers\NyTimesMapper;
use PHPUnit\Framework\TestCase;

class NyTimesMapperTest extends TestCase
{
    private NyTimesMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new NyTimesMapper();
    }

    public function test_maps_valid_article(): void
    {
        $rawData = [
            'response' => [
                'docs' => [
                    [
                        '_id' => 'nyt://article/12345',
                        'headline' => ['main' => 'Test NYT Article'],
                        'abstract' => 'Test abstract',
                        'lead_paragraph' => 'Test lead paragraph',
                        'web_url' => 'https://nytimes.com/article',
                        'pub_date' => '2026-01-12T10:00:00Z',
                        'section_name' => 'Technology',
                        'byline' => [
                            'original' => 'By John Reporter',
                        ],
                        'multimedia' => [
                            [
                                'url' => 'images/test.jpg',
                                'subtype' => 'xlarge',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertCount(1, $result);
        $this->assertEquals('nyt://article/12345', $result[0]['external_id']);
        $this->assertEquals('Test NYT Article', $result[0]['title']);
        $this->assertEquals('Test abstract', $result[0]['description']);
        $this->assertEquals('Test lead paragraph', $result[0]['content']);
        $this->assertEquals('John Reporter', $result[0]['author']); // "By " prefix removed
        $this->assertEquals('https://nytimes.com/article', $result[0]['url']);
        $this->assertEquals('https://static01.nyt.com/images/test.jpg', $result[0]['image_url']);
        $this->assertEquals('Technology', $result[0]['category']);
    }

    public function test_removes_by_prefix_from_author(): void
    {
        $rawData = [
            'response' => [
                'docs' => [
                    [
                        'headline' => ['main' => 'Article'],
                        'web_url' => 'https://nytimes.com/article',
                        'byline' => [
                            'original' => 'BY Jane Smith',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertEquals('Jane Smith', $result[0]['author']);
    }

    public function test_extracts_author_from_person_array(): void
    {
        $rawData = [
            'response' => [
                'docs' => [
                    [
                        'headline' => ['main' => 'Article'],
                        'web_url' => 'https://nytimes.com/article',
                        'byline' => [
                            'person' => [
                                ['firstname' => 'John', 'lastname' => 'Doe'],
                                ['firstname' => 'Jane', 'lastname' => 'Smith'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertEquals('John Doe, Jane Smith', $result[0]['author']);
    }

    public function test_uses_snippet_when_abstract_missing(): void
    {
        $rawData = [
            'response' => [
                'docs' => [
                    [
                        'headline' => ['main' => 'Article'],
                        'web_url' => 'https://nytimes.com/article',
                        'snippet' => 'Test snippet',
                    ],
                ],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertEquals('Test snippet', $result[0]['description']);
    }

    public function test_uses_news_desk_when_section_name_missing(): void
    {
        $rawData = [
            'response' => [
                'docs' => [
                    [
                        'headline' => ['main' => 'Article'],
                        'web_url' => 'https://nytimes.com/article',
                        'news_desk' => 'Business',
                    ],
                ],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertEquals('Business', $result[0]['category']);
    }

    public function test_skips_articles_without_headline(): void
    {
        $rawData = [
            'response' => [
                'docs' => [
                    [
                        'web_url' => 'https://nytimes.com/article',
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
                'docs' => [],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertEmpty($result);
    }

    public function test_handles_empty_multimedia(): void
    {
        $rawData = [
            'response' => [
                'docs' => [
                    [
                        'headline' => ['main' => 'Article'],
                        'web_url' => 'https://nytimes.com/article',
                        'multimedia' => [],
                    ],
                ],
            ],
        ];

        $result = $this->mapper->map($rawData);

        $this->assertNull($result[0]['image_url']);
    }
}
