<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->validate(['status' => ['nullable', Rule::in(['draft', 'published'])]])['status'] ?? null;

        $articles = Article::query()
            ->with('category:id,name')
            ->when($status, fn ($query, $value) => $query->where('is_published', $value === 'published'))
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.articles.index', compact('articles', 'status'));
    }

    public function create(): View
    {
        return view('admin.articles.create', [
            'categories' => ArticleCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateArticle($request);
        $isPublished = (bool) ($validated['is_published'] ?? false);

        Article::create([
            'category_id' => $validated['category_id'] ?? null,
            'created_by' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title']),
            'excerpt' => $validated['excerpt'] ?? null,
            'body' => $validated['body'],
            'is_published' => $isPublished,
            'published_at' => $isPublished ? now() : null,
        ]);

        return redirect()->route('admin.articles.index')->with('status', 'Artikel berhasil dibuat.');
    }

    public function edit(int $articleId): View
    {
        return view('admin.articles.edit', [
            'article' => Article::query()->findOrFail($articleId),
            'categories' => ArticleCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, int $articleId): RedirectResponse
    {
        $article = Article::query()->findOrFail($articleId);
        $validated = $this->validateArticle($request);
        $isPublished = (bool) ($validated['is_published'] ?? false);

        $article->category_id = $validated['category_id'] ?? null;
        $article->title = $validated['title'];
        if ($article->isDirty('title')) {
            $article->slug = $this->uniqueSlug($validated['title'], $article->id);
        }
        $article->excerpt = $validated['excerpt'] ?? null;
        $article->body = $validated['body'];
        $article->is_published = $isPublished;
        $article->published_at = $isPublished ? ($article->published_at ?? now()) : null;
        $article->save();

        return redirect()->route('admin.articles.index')->with('status', 'Artikel berhasil diperbarui.');
    }

    public function destroy(int $articleId): RedirectResponse
    {
        Article::query()->findOrFail($articleId)->delete();

        return redirect()->route('admin.articles.index')->with('status', 'Artikel berhasil dihapus.');
    }

    private function validateArticle(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category_id' => ['nullable', 'integer', 'exists:article_categories,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'min:30'],
            'is_published' => ['nullable', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $counter = 2;

        while (Article::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
