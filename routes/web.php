<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScreeningController;
use App\Http\Controllers\EdukasiController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController as AdminUserManagementController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\ArticleCategoryController as AdminArticleCategoryController;
use App\Http\Controllers\Student\ScreeningShareController;
use App\Http\Controllers\BK\SharedScreeningController;
use App\Http\Controllers\Student\ScreeningHistoryController;
use App\Http\Controllers\BkServiceScheduleController;
use App\Http\Controllers\Student\BkAppointmentController;
use App\Http\Controllers\BK\BkAppointmentManagementController;
use App\Http\Controllers\NotificationController;

Route::get('/', function () {
    return view('beranda');
})->name('beranda');

Route::get('/tentang', function () {
    return view('tentang');
})->name('tentang');

Route::get('/edukasi', [EdukasiController::class, 'index'])->name('edukasi');
Route::get('/edukasi/{slug}', [EdukasiController::class, 'show'])->name('edukasi.show');

Route::get('/informasi-bk', function () {
    return view('informasi-bk');
})->name('informasi-bk');

Route::prefix('skrining')->name('screening.')->group(function () {
    Route::get('/info', [ScreeningController::class, 'info'])->name('info');
    Route::post('/setuju', [ScreeningController::class, 'consent'])->name('consent');
    Route::post('/persetujuan', [ScreeningController::class, 'storeConsent'])->name('consent.store');
    Route::get('/kuesioner', [ScreeningController::class, 'questionnaire'])->name('questionnaire');
    Route::post('/kirim', [ScreeningController::class, 'submit'])->name('submit');
    Route::get('/hasil/{id}', [ScreeningController::class, 'result'])->name('result');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');
    Route::post('/notifikasi/{notificationId}/baca', [NotificationController::class, 'markRead'])
        ->whereUuid('notificationId')->name('notifications.read');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profil', function (Request $request) {
        $user = $request->user();
        return view('dashboard.profile', ['user' => $user]);
    })->name('profile');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/jadwal-layanan', [BkServiceScheduleController::class, 'index'])->name('service-schedules.index');
        Route::get('/jadwal-layanan/baru', [BkServiceScheduleController::class, 'create'])->name('service-schedules.create');
        Route::post('/jadwal-layanan', [BkServiceScheduleController::class, 'store'])->name('service-schedules.store');
        Route::get('/jadwal-layanan/{scheduleId}/edit', [BkServiceScheduleController::class, 'edit'])->whereNumber('scheduleId')->name('service-schedules.edit');
        Route::put('/jadwal-layanan/{scheduleId}', [BkServiceScheduleController::class, 'update'])->whereNumber('scheduleId')->name('service-schedules.update');
        Route::delete('/jadwal-layanan/{scheduleId}', [BkServiceScheduleController::class, 'destroy'])->whereNumber('scheduleId')->name('service-schedules.destroy');

        Route::get('/pengguna', [AdminUserManagementController::class, 'index'])->name('users.index');
        Route::get('/pengguna/guru-bk/baru', [AdminUserManagementController::class, 'createTeacher'])->name('users.create-teacher');
        Route::post('/pengguna/guru-bk', [AdminUserManagementController::class, 'storeTeacher'])->name('users.store-teacher');
        Route::get('/pengguna/{userId}/edit', [AdminUserManagementController::class, 'edit'])->whereNumber('userId')->name('users.edit');
        Route::put('/pengguna/{userId}', [AdminUserManagementController::class, 'update'])->whereNumber('userId')->name('users.update');
        Route::patch('/pengguna/{userId}/status', [AdminUserManagementController::class, 'toggleStatus'])->whereNumber('userId')->name('users.status');

        Route::get('/artikel', [AdminArticleController::class, 'index'])->name('articles.index');
        Route::get('/artikel/baru', [AdminArticleController::class, 'create'])->name('articles.create');
        Route::post('/artikel', [AdminArticleController::class, 'store'])->name('articles.store');
        Route::get('/artikel/{articleId}/edit', [AdminArticleController::class, 'edit'])->whereNumber('articleId')->name('articles.edit');
        Route::put('/artikel/{articleId}', [AdminArticleController::class, 'update'])->whereNumber('articleId')->name('articles.update');
        Route::delete('/artikel/{articleId}', [AdminArticleController::class, 'destroy'])->whereNumber('articleId')->name('articles.destroy');

        Route::get('/kategori', [AdminArticleCategoryController::class, 'index'])->name('categories.index');
        Route::post('/kategori', [AdminArticleCategoryController::class, 'store'])->name('categories.store');
        Route::put('/kategori/{categoryId}', [AdminArticleCategoryController::class, 'update'])->whereNumber('categoryId')->name('categories.update');
        Route::delete('/kategori/{categoryId}', [AdminArticleCategoryController::class, 'destroy'])->whereNumber('categoryId')->name('categories.destroy');
    });

    Route::prefix('jadwal-temu-bk')->name('student.bk-appointments.')->middleware('role:siswa')->group(function () {
        Route::get('/', [BkAppointmentController::class, 'index'])->name('index');
        Route::post('/', [BkAppointmentController::class, 'store'])->name('store');
        Route::delete('/{appointmentId}', [BkAppointmentController::class, 'cancel'])->whereNumber('appointmentId')->name('cancel');
    });

    Route::prefix('bk')->name('bk.')->middleware('role:guru_bk')->group(function () {
        Route::get('/jadwal-layanan', [BkServiceScheduleController::class, 'index'])->name('service-schedules.index');
        Route::get('/jadwal-layanan/baru', [BkServiceScheduleController::class, 'create'])->name('service-schedules.create');
        Route::post('/jadwal-layanan', [BkServiceScheduleController::class, 'store'])->name('service-schedules.store');
        Route::get('/jadwal-layanan/{scheduleId}/edit', [BkServiceScheduleController::class, 'edit'])->whereNumber('scheduleId')->name('service-schedules.edit');
        Route::put('/jadwal-layanan/{scheduleId}', [BkServiceScheduleController::class, 'update'])->whereNumber('scheduleId')->name('service-schedules.update');
        Route::delete('/jadwal-layanan/{scheduleId}', [BkServiceScheduleController::class, 'destroy'])->whereNumber('scheduleId')->name('service-schedules.destroy');
        Route::patch('/janji-temu/{appointmentId}/status', [BkAppointmentManagementController::class, 'updateStatus'])->whereNumber('appointmentId')->name('service-schedules.appointments.status');
    });

    Route::middleware('role:siswa')->group(function () {
        Route::get('/riwayat-skrining/{resultId}/bagikan', [ScreeningShareController::class, 'create'])
            ->whereNumber('resultId')->name('student.screening-shares.create');
        Route::post('/riwayat-skrining/{resultId}/bagikan', [ScreeningShareController::class, 'store'])
            ->whereNumber('resultId')->name('student.screening-shares.store');
        Route::delete('/riwayat-skrining/{resultId}/bagikan/{shareId}', [ScreeningShareController::class, 'revoke'])
            ->whereNumber('resultId')->whereNumber('shareId')->name('student.screening-shares.revoke');
    });

    Route::prefix('bk/hasil-dibagikan')->name('bk.shared-results.')
        ->middleware('role:guru_bk')->group(function () {
            Route::get('/', [SharedScreeningController::class, 'index'])->name('index');
            Route::get('/{shareId}', [SharedScreeningController::class, 'show'])
                ->whereNumber('shareId')->name('show');
        });

    Route::middleware('role:siswa,guru_bk')->group(function () {
        Route::get('/screening', function () {
            return view('dashboard.screening');
        })->name('screening');
    });

    Route::prefix('riwayat-skrining')->name('screening-history.')->middleware('role:siswa')->group(function () {
        Route::get('/', [ScreeningHistoryController::class, 'index'])->name('index');
        Route::delete('/{id}', [ScreeningHistoryController::class, 'destroy'])->whereNumber('id')->name('destroy');
    });
});
