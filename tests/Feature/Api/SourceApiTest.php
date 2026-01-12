<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_sources(): void
    {
        Source::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/sources');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'is_active',
                    ],
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_only_lists_active_sources(): void
    {
        Source::factory()->count(2)->create(['is_active' => true]);
        Source::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/sources');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_show_single_source(): void
    {
        $source = Source::factory()->create([
            'name' => 'Test News',
            'slug' => 'test-news',
        ]);

        $response = $this->getJson("/api/v1/sources/{$source->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $source->id)
            ->assertJsonPath('data.name', 'Test News')
            ->assertJsonPath('data.slug', 'test-news');
    }

    public function test_returns_404_for_nonexistent_source(): void
    {
        $response = $this->getJson('/api/v1/sources/99999');

        $response->assertStatus(404);
    }
}
