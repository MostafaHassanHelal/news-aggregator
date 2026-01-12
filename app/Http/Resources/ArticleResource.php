<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for Article model.
 */
class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'author' => $this->author,
            'url' => $this->url,
            'image_url' => $this->image_url,
            'category' => $this->category,
            'published_at' => $this->published_at ? $this->published_at->toIso8601String() : null,
            'source' => new SourceResource($this->whenLoaded('source')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
