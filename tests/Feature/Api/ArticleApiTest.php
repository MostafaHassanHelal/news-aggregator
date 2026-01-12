<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    use RefreshDatabase;

    private Source $source;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->source = Source::create([
            'name' => 'Test Source',
            'slug' => 'test-source',
            'api_name' => 'test',
            'is_active' => true,
        ]);
    }

    public function test_can_list_articles(): void
    {
        Article::factory()->count(3)->create([
            'source_id' => $this->source->id,
        ]);

        $response = $this->getJson('/api/v1/articles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'content',
                        'author',
                        'url',
                        'image_url',
                        'category',
                        'published_at',
                        'source',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'per_page',
                    'to',
                    'total',
                ],
                'links',
            ]);
    }

    public function test_can_filter_by_keyword(): void
    {
        Article::factory()->create([
            'source_id' => $this->source->id,
            'title' => 'Laravel Best Practices',
        ]);
        Article::factory()->create([
            'source_id' => $this->source->id,
            'title' => 'Python Tutorial',
        ]);

        $response = $this->getJson('/api/v1/articles?q=Laravel');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Laravel Best Practices');
    }

    public function test_can_filter_by_category(): void
    {
        Article::factory()->create([
            'source_id' => $this->source->id,
            'category' => 'Technology',
        ]);
        Article::factory()->create([
            'source_id' => $this->source->id,
            'category' => 'Sports',
        ]);

        $response = $this->getJson('/api/v1/articles?category=Technology');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category', 'Technology');
    }

    public function test_can_filter_by_author(): void
    {
        Article::factory()->create([
            'source_id' => $this->source->id,
            'author' => 'John Doe',
        ]);
        Article::factory()->create([
            'source_id' => $this->source->id,
            'author' => 'Jane Smith',
        ]);

        $response = $this->getJson('/api/v1/articles?author=John');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.author', 'John Doe');
    }

    public function test_can_filter_by_source(): void
    {
        $anotherSource = Source::create([
            'name' => 'Another Source',
            'slug' => 'another-source',
            'api_name' => 'another',
            'is_active' => true,
        ]);

        Article::factory()->create([
            'source_id' => $this->source->id,
        ]);
        Article::factory()->create([
            'source_id' => $anotherSource->id,
        ]);

        $response = $this->getJson('/api/v1/articles?source=test-source');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_by_date_range(): void
    {
        Article::factory()->create([
            'source_id' => $this->source->id,
            'published_at' => '2026-01-10',
        ]);
        Article::factory()->create([
            'source_id' => $this->source->id,
            'published_at' => '2026-01-15',
        ]);

        $response = $this->getJson('/api/v1/articles?from=2026-01-14&to=2026-01-16');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_paginate_results(): void
    {
        Article::factory()->count(20)->create([
            'source_id' => $this->source->id,
        ]);

        $response = $this->getJson('/api/v1/articles?per_page=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 20);
    }

    public function test_can_show_single_article(): void
    {
        $article = Article::factory()->create([
            'source_id' => $this->source->id,
            'title' => 'Single Article',
        ]);

        $response = $this->getJson("/api/v1/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $article->id)
            ->assertJsonPath('data.title', 'Single Article');
    }

    public function test_returns_404_for_nonexistent_article(): void
    {
        $response = $this->getJson('/api/v1/articles/99999');

        $response->assertStatus(404);
    }

    public function test_validates_per_page_parameter(): void
    {
        $response = $this->getJson('/api/v1/articles?per_page=200');

        $response->assertStatus(422);
    }

    public function test_validates_date_range(): void
    {
        $response = $this->getJson('/api/v1/articles?from=2026-01-15&to=2026-01-10');

        $response->assertStatus(422);
    }

    public function test_orders_by_published_date_descending(): void
    {
        Article::factory()->create([
            'source_id' => $this->source->id,
            'title' => 'Older Article',
            'published_at' => '2026-01-01',
        ]);
        Article::factory()->create([
            'source_id' => $this->source->id,
            'title' => 'Newer Article',
            'published_at' => '2026-01-10',
        ]);

        $response = $this->getJson('/api/v1/articles');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.title', 'Newer Article')
            ->assertJsonPath('data.1.title', 'Older Article');
    }
}
