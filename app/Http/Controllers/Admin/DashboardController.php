<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard.admin', [
            'studentCount' => User::query()->where('role', 'siswa')->count(),
            'bkCount' => User::query()->where('role', 'guru_bk')->count(),
            'activeAccountCount' => User::query()->whereIn('role', ['siswa', 'guru_bk'])->where('is_active', true)->count(),
            'inactiveAccountCount' => User::query()->whereIn('role', ['siswa', 'guru_bk'])->where('is_active', false)->count(),
            'publishedArticleCount' => Article::query()->where('is_published', true)->count(),
            'draftArticleCount' => Article::query()->where('is_published', false)->count(),
        ]);
    }
}
