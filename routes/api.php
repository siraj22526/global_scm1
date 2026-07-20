<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CountryApiController;
use App\Http\Controllers\Api\RiskApiController;
use App\Http\Controllers\Api\CurrencyApiController;
use App\Http\Controllers\Api\NewsApiController;
use App\Http\Controllers\Api\PortApiController;
use App\Http\Controllers\Api\CompareApiController;
use App\Http\Controllers\Api\WatchlistApiController;
use App\Http\Controllers\Api\ArticleApiController;
use App\Http\Controllers\Api\AdminApiController;

// Use 'web' middleware to support sessions, CSRF, and cookies on the internal API
Route::middleware(['web'])->group(function () {
    
    // Auth endpoints
    Route::post('/auth/register', [AuthApiController::class, 'register']);
    Route::post('/auth/login', [AuthApiController::class, 'login']);
    Route::post('/auth/logout', [AuthApiController::class, 'logout'])->middleware('auth');

    // Countries & Metrics
    Route::get('/countries', [CountryApiController::class, 'index']);
    Route::get('/countries/{iso}', [CountryApiController::class, 'show']);
    Route::get('/countries/{iso}/indicators', [CountryApiController::class, 'indicators']);
    Route::get('/countries/{iso}/weather', [CountryApiController::class, 'weather']);

    // Risk Scores
    Route::get('/risk', [RiskApiController::class, 'index']);
    Route::get('/risk/{iso}', [RiskApiController::class, 'show']);
    Route::get('/risk/{iso}/history', [RiskApiController::class, 'history']);

    // Currencies
    Route::get('/currency', [CurrencyApiController::class, 'index']);
    Route::get('/currency/{code}/history', [CurrencyApiController::class, 'history']);

    // News
    Route::get('/news', [NewsApiController::class, 'index']);
    Route::get('/news/summary', [NewsApiController::class, 'summary']);

    // Ports
    Route::get('/ports', [PortApiController::class, 'index']);
    Route::get('/ports/{id}', [PortApiController::class, 'show']);

    // Comparison & Articles
    Route::get('/compare', [CompareApiController::class, 'compare']);
    Route::get('/articles', [ArticleApiController::class, 'index']);
    Route::get('/articles/{slug}', [ArticleApiController::class, 'show']);

    // Authenticated User Watchlist
    Route::middleware(['auth'])->group(function () {
        Route::get('/watchlist', [WatchlistApiController::class, 'index']);
        Route::post('/watchlist', [WatchlistApiController::class, 'store']);
        Route::delete('/watchlist/{iso}', [WatchlistApiController::class, 'destroy']);
    });

    // Admin Panel Actions
    Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
        Route::get('/users', [AdminApiController::class, 'users']);
        Route::patch('/users/{id}', [AdminApiController::class, 'updateUser']);

        Route::post('/ports', [AdminApiController::class, 'storePort']);
        Route::put('/ports/{id}', [AdminApiController::class, 'updatePort']);
        Route::delete('/ports/{id}', [AdminApiController::class, 'destroyPort']);
        Route::post('/ports/import', [AdminApiController::class, 'importPorts']);
        Route::post('/ports/sync', [AdminApiController::class, 'syncPortsFromApi']);

        Route::post('/articles', [AdminApiController::class, 'storeArticle']);
        Route::put('/articles/{id}', [AdminApiController::class, 'updateArticle']);
        Route::delete('/articles/{id}', [AdminApiController::class, 'destroyArticle']);

        Route::get('/lexicon', [AdminApiController::class, 'lexicon']);
        Route::post('/lexicon', [AdminApiController::class, 'storeLexicon']);
        Route::delete('/lexicon/{id}', [AdminApiController::class, 'destroyLexicon']);

        Route::put('/risk-weights', [AdminApiController::class, 'updateWeights']);
    });
});
