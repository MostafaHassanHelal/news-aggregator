<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SourceCollection;
use App\Http\Resources\SourceResource;
use App\Models\Source;

/**
 * API Controller for Sources.
 */
class SourceController extends Controller
{
    /**
     * Display a listing of sources.
     *
     * @return SourceCollection
     */
    public function index(): SourceCollection
    {
        $sources = Source::active()->get();
        return new SourceCollection($sources);
    }

    /**
     * Display the specified source.
     *
     * @param Source $source
     * @return SourceResource
     */
    public function show(Source $source): SourceResource
    {
        return new SourceResource($source);
    }
}
