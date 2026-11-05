<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ScreeningResult;
use App\Models\ScreeningResultShare;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_admin_dashboard_shows_account_and_content_counts_without_screening_metrics(): void
    {
        $admin = $this->admin();
        User::factory()->count(2)->create(['role' => 'siswa']);
        User::factory()->create(['role' => 'guru_bk']);
        Article::create([
            'title' => 'Artikel terbit',
            'slug' => 'artikel-terbit-admin-test',
            'body' => 'Isi artikel edukasi yang cukup panjang untuk pengujian konten publik.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dasbor Admin')
            ->assertSee('Manajemen Pengguna')
            ->assertSee('Manajemen edukasi')
            ->assertSee('Artikel terbit');
    }

    public function test_student_cannot_access_admin_management_pages(): void
    {
        $student = User::factory()->create(['role' => 'siswa','is_active' => true,]);

        $this->actingAs($student)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($student)
            ->get(route('admin.articles.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_bk_account_and_forged_role_is_ignored(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.users.store-teacher'), [
                'name' => 'Guru BK Demo',
                'email' => 'bk-admin-create@example.test',
                'password' => 'PasswordDemo#2026',
                'password_confirmation' => 'PasswordDemo#2026',
                'role' => 'admin',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'bk-admin-create@example.test',
            'role' => 'guru_bk',
            'is_active' => 1,
        ]);
    }

    public function test_admin_can_deactivate_student_and_active_shared_access_is_revoked(): void
    {
        $admin = $this->admin();
        $student = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $teacher = User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $result = ScreeningResult::create([
            'user_id' => $student->id,
            'score' => 50,
            'interpretation' => null,
            'meta' => [],
            'completed_at' => now(),
        ]);
        $share = ScreeningResultShare::create([
            'screening_result_id' => $result->id,
            'student_id' => $student->id,
            'bk_teacher_id' => $teacher->id,
            'consent_version' => 'test-v1',
            'consented_at' => now(),
            'revoked_at' => null,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.status', $student->id), ['is_active' => 0])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['id' => $student->id, 'is_active' => 0]);
        $this->assertNotNull($share->fresh()->revoked_at);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'role' => 'siswa',
            'is_active' => false,
            'password' => bcrypt('password'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_authenticated_user_is_logged_out_on_next_protected_request(): void
    {
        $user = User::factory()->create(['role' => 'siswa', 'is_active' => false]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_admin_can_create_publish_and_public_can_read_article(): void
    {
        $admin = $this->admin();
        $category = ArticleCategory::create([
            'name' => 'Tekanan Akademik',
            'slug' => 'tekanan-akademik-admin-test',
            'description' => 'Materi tekanan akademik.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Mengelola Tekanan Akademik',
                'category_id' => $category->id,
                'excerpt' => 'Panduan ringkas untuk siswa.',
                'body' => 'Tekanan akademik dapat muncul dari berbagai tuntutan belajar. Siswa dapat mencari dukungan dan menyusun langkah belajar yang realistis.',
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.articles.index'));

        $article = Article::query()->where('title', 'Mengelola Tekanan Akademik')->firstOrFail();
        $this->assertTrue($article->is_published);
        $this->assertSame($category->id, $article->category_id);

        $this->get(route('edukasi'))
            ->assertOk()
            ->assertSee('Mengelola Tekanan Akademik');

        $this->get(route('edukasi.show', $article->slug))
            ->assertOk()
            ->assertSee('Tekanan akademik dapat muncul');
    }

    public function test_draft_article_is_not_visible_to_public(): void
    {
        $draft = Article::create([
            'title' => 'Artikel Draft Privat',
            'slug' => 'artikel-draft-privat-admin-test',
            'body' => 'Isi draft yang tidak boleh tampil sebelum artikel diterbitkan.',
            'is_published' => false,
            'published_at' => null,
        ]);

        $this->get(route('edukasi'))->assertOk()->assertDontSee($draft->title);
        $this->get(route('edukasi.show', $draft->slug))->assertNotFound();
    }

    public function test_deleting_category_preserves_articles_without_category(): void
    {
        $admin = $this->admin();
        $category = ArticleCategory::create([
            'name' => 'Kategori Hapus',
            'slug' => 'kategori-hapus-admin-test',
        ]);
        $article = Article::create([
            'category_id' => $category->id,
            'title' => 'Artikel tetap ada',
            'slug' => 'artikel-tetap-ada-admin-test',
            'body' => 'Artikel ini tetap tersimpan setelah kategori terkait dihapus.',
            'is_published' => false,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category->id))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('articles', ['id' => $article->id, 'category_id' => null]);
        $this->assertDatabaseMissing('article_categories', ['id' => $category->id]);
    }
}
