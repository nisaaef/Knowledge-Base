<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\WriterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Dashboard\ArticleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SeoController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/artikel/{article:slug}', [HomeController::class, 'show'])->name('articles.show');
Route::get('/profil/{user:username}', [AuthorController::class, 'show'])->name('authors.show');
Route::view('/tentang', 'about')->name('about');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/kontak', [ContactController::class, 'create'])->name('contact');
Route::post('/kontak', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

/*
|--------------------------------------------------------------------------
| Login / logout
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');

    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])->middleware('throttle:5,1');
});

Route::post('/keluar', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// Setelah login, arahkan ke dasbor sesuai peran.
Route::get('/dashboard', function () {
    return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'penulis.dashboard');
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| Dasbor admin
|--------------------------------------------------------------------------
*/
// Notifikasi dasbor: dipakai admin dan penulis (hanya yang aktif).
Route::middleware(['auth', EnsureRole::class.':admin,writer'])->group(function () {
    Route::get('/notifikasi/{id}', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', EnsureRole::class.':admin'])
    ->group(function () {
        Route::get('/', [DashboardController::class, 'admin'])->name('dashboard');

        Route::get('profil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profil', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profil/kata-sandi', [ProfileController::class, 'password'])->name('profile.password');

        Route::resource('artikel', ArticleController::class)
            ->parameters(['artikel' => 'article'])
            ->except('show')
            ->names('articles');

        Route::get('pengaturan', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('pengaturan', [SettingController::class, 'update'])->name('settings.update');

        Route::resource('kategori', CategoryController::class)
            ->parameters(['kategori' => 'category'])
            ->except(['show', 'create'])
            ->names('categories');

        Route::patch('penulis/{writer}/akses', [WriterController::class, 'toggle'])->name('writers.toggle');
        Route::patch('penulis/{writer}/setujui', [WriterController::class, 'approve'])->name('writers.approve');
        Route::resource('penulis', WriterController::class)
            ->parameters(['penulis' => 'writer'])
            ->except('show')
            ->names('writers');

        Route::resource('pesan', MessageController::class)
            ->parameters(['pesan' => 'message'])
            ->only(['index', 'show', 'destroy'])
            ->names('messages');
    });

/*
|--------------------------------------------------------------------------
| Dasbor penulis
|--------------------------------------------------------------------------
*/
Route::prefix('penulis')
    ->name('penulis.')
    ->middleware(['auth', EnsureRole::class.':writer'])
    ->group(function () {
        Route::get('/', [DashboardController::class, 'writer'])->name('dashboard');

        Route::get('profil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profil', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profil/kata-sandi', [ProfileController::class, 'password'])->name('profile.password');

        Route::resource('artikel', ArticleController::class)
            ->parameters(['artikel' => 'article'])
            ->except('show')
            ->names('articles');
    });
