<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\ArticleDTO;

/**
 * Interface for article repository.
 * 
 * Encapsulates database persistence logic for articles.
 * This is the Repository Pattern.
 */
interface ArticleRepositoryInterface
{
    /**
     * Upsert an article (insert or update if exists).
     *
     * @param ArticleDTO $articleDTO
     * @return void
     */
    public function upsert(ArticleDTO $articleDTO): void;

    /**
     * Upsert multiple articles.
     *
     * @param array<ArticleDTO> $articleDTOs
     * @return int Number of articles processed
     */
    public function upsertMany(array $articleDTOs): int;

    /**
     * Find articles with optional filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function findWithFilters(array $filters = [], int $perPage = 15);
}
