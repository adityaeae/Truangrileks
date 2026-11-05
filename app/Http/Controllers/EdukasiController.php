<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EdukasiController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:120'],
        ]);

        $articles = Article::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with('category:id,name,slug')
            ->when($filters['q'] ?? null, function ($query, $term): void {
                $query->where(function ($inner) use ($term): void {
                    $inner->where('title', 'like', '%'.$term.'%')
                        ->orWhere('excerpt', 'like', '%'.$term.'%');
                });
            })
            ->when($filters['category'] ?? null, function ($query, $slug): void {
                $query->whereHas('category', fn ($category) => $category->where('slug', $slug));
            })
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('edukasi', [
            'articles' => $articles,
            'categories' => ArticleCategory::query()->orderBy('name')->get(['name', 'slug']),
            'filters' => $filters,
        ]);
    }

    public function show(string $slug): View
    {
        $article = Article::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with('category:id,name,slug')
            ->firstOrFail();

        return view('edukasi.show', compact('article'));
    }
}
