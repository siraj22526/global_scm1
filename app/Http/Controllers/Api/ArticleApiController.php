<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;

class ArticleApiController extends ApiController
{
    /**
     * GET /api/articles
     */
    public function index()
    {
        $articles = Article::where('status', 'published')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $articles->map(function ($art) {
            return [
                'id' => $art->id,
                'title' => $art->title,
                'slug' => $art->slug,
                'body' => $art->body,
                'author' => $art->user->name ?? 'Admin',
                'created_at' => $art->created_at->toIso8601String()
            ];
        });

        return $this->sendResponse($data);
    }

    /**
     * GET /api/articles/{slug}
     */
    public function show(string $slug)
    {
        $article = Article::where('slug', $slug)
            ->where('status', 'published')
            ->with('user')
            ->first();

        if (!$article) {
            return $this->sendError('ARTICLE_NOT_FOUND', 'Artikel tidak ditemukan atau masih berstatus draft.', 404);
        }

        $data = [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'body' => $article->body,
            'author' => $article->user->name ?? 'Admin',
            'created_at' => $article->created_at->toIso8601String()
        ];

        return $this->sendResponse($data);
    }
}
