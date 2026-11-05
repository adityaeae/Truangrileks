<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => ArticleCategory::query()->withCount('articles')->orderBy('name')->paginate(15),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:article_categories,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        ArticleCategory::create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['name']),
        ]);

        return redirect()->route('admin.categories.index')->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, int $categoryId): RedirectResponse
    {
        $category = ArticleCategory::query()->findOrFail($categoryId);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('article_categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $category->name = $validated['name'];
        $category->description = $validated['description'] ?? null;
        $category->slug = $this->uniqueSlug($validated['name'], $category->id);
        $category->save();

        return redirect()->route('admin.categories.index')->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(int $categoryId): RedirectResponse
    {
        $category = ArticleCategory::query()->findOrFail($categoryId);
        $category->delete(); // Articles are preserved; category_id becomes null through the FK.

        return redirect()->route('admin.categories.index')->with('status', 'Kategori dihapus. Artikel terkait tetap dipertahankan.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $counter = 2;

        while (ArticleCategory::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
