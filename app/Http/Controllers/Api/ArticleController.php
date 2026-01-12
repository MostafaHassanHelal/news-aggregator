<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\ArticleRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleIndexRequest;
use App\Http\Resources\ArticleCollection;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for Articles.
 * 
 * This controller is intentionally thin - all business logic
 * is delegated to the repository and other services.
 */
class ArticleController extends Controller
{
    private ArticleRepositoryInterface $articleRepository;

    public function __construct(ArticleRepositoryInterface $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    /**
     * Display a listing of articles.
     *
     * @param ArticleIndexRequest $request
     * @return ArticleCollection
     */
    public function index(ArticleIndexRequest $request): ArticleCollection
    {
        $articles = $this->articleRepository->findWithFilters(
            $request->getFilters(),
            $request->getPerPage()
        );

        return new ArticleCollection($articles);
    }

    /**
     * Display the specified article.
     *
     * @param Article $article
     * @return ArticleResource
     */
    public function show(Article $article): ArticleResource
    {
        $article->load('source');
        return new ArticleResource($article);
    }
}
