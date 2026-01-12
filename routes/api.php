<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\SourceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public API routes for news aggregation
Route::prefix('v1')->group(function () {
    // Articles
    Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');

    // Sources
    Route::get('/sources', [SourceController::class, 'index'])->name('sources.index');
    Route::get('/sources/{source}', [SourceController::class, 'show'])->name('sources.show');
});

// Authenticated routes (if needed in the future)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
