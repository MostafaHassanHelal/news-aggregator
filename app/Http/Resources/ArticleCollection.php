<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * API Resource Collection for Articles.
 * 
 * Laravel's ResourceCollection automatically handles pagination
 * when a LengthAwarePaginator is passed to it.
 */
class ArticleCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ArticleResource::class;
}
