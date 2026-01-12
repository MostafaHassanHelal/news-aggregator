<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\ArticleRepositoryInterface;
use App\DTOs\ArticleDTO;
use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Article Repository implementation.
 * 
 * Encapsulates all database operations for articles.
 * Uses upsert to prevent duplicates based on source_id + external_id.
 */
class ArticleRepository implements ArticleRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function upsert(ArticleDTO $articleDTO): void
    {
        $data = $articleDTO->toArray();

        // Debug: log image_url byte length to diagnose DB truncation issues
        try {
            \Illuminate\Support\Facades\Log::info('article.image_url.length', [
                'source_id' => $articleDTO->getSourceId(),
                'external_id' => $articleDTO->getExternalId(),
                'length_bytes' => isset($data['image_url']) && $data['image_url'] !== null ? strlen($data['image_url']) : 0,
                'sample' => isset($data['image_url']) && $data['image_url'] !== null ? substr($data['image_url'], 0, 300) : null,
            ]);
        } catch (\Throwable $e) {
            // ignore logging errors in production flow
        }

        Article::updateOrCreate(
            [
                'source_id' => $articleDTO->getSourceId(),
                'external_id' => $articleDTO->getExternalId(),
            ],
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    public function upsertMany(array $articleDTOs): int
    {
        $processed = 0;

        // Use database transaction for bulk operations
        DB::transaction(function () use ($articleDTOs, &$processed) {
            foreach ($articleDTOs as $articleDTO) {
                if ($articleDTO instanceof ArticleDTO) {
                    $this->upsert($articleDTO);
                    $processed++;
                }
            }
        });

        return $processed;
    }

    /**
     * {@inheritdoc}
     */
    public function findWithFilters(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Article::query()->with('source');

        // Keyword search (searches in title, description, content)
        if (!empty($filters['q'])) {
            $searchTerm = $filters['q'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('content', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by source(s)
        if (!empty($filters['source'])) {
            $sources = is_array($filters['source']) ? $filters['source'] : [$filters['source']];
            $query->whereHas('source', function ($q) use ($sources) {
                $q->whereIn('slug', $sources)->orWhereIn('id', $sources);
            });
        }

        // Filter by category
        if (!empty($filters['category'])) {
            $query->where('category', 'like', "%{$filters['category']}%");
        }

        // Filter by author
        if (!empty($filters['author'])) {
            $query->where('author', 'like', "%{$filters['author']}%");
        }

        // Filter by date range
        if (!empty($filters['from'])) {
            $query->where('published_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->where('published_at', '<=', $filters['to']);
        }

        // Default ordering: newest first
        $query->orderBy('published_at', 'desc');

        return $query->paginate($perPage);
    }
}
