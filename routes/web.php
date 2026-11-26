<?php

use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DiscoveryController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\StoryMediaController;
use App\Http\Controllers\StoryPreviewController;
use App\Http\Controllers\Userzone\DashboardController;
use App\Http\Controllers\Userzone\LocationController;
use App\Http\Controllers\Userzone\ProfileController;
use App\Http\Controllers\Userzone\TrashController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\WelcomeController;
use App\Http\Middleware\EnsureActiveUser;
use Illuminate\Support\Facades\Route;

/*
 * Public Website routes
 */
Route::get('/', WelcomeController::class)->name('welcome');

Route::get('stories/{story}/media/{asset}', StoryMediaController::class)->where('asset', 'cover|background|gallery-[0-5]')->name('stories.media');
Route::get('discover', [DiscoveryController::class, 'random'])->name('discovery.random');
Route::resource('stories', StoryController::class)->only(['index', 'show']);
Route::resource('locations', App\Http\Controllers\LocationController::class)->only(['index', 'show']);
Route::get('users/{user}', [AuthorController::class, 'show'])->name('authors.show');

/*
 * Authentication routes
 */
require __DIR__.'/auth.php';

/*
 * Userzone routes
 */
Route::middleware(['auth', EnsureActiveUser::class])->group(function () {
    Route::post('user/story-preview', StoryPreviewController::class)->middleware('throttle:60,1')->name('stories.preview');
    Route::post('stories/{story}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('comments/{comment}/support', [CommentController::class, 'support'])->name('comments.support');
    Route::delete('comments/{comment}/support', [CommentController::class, 'support'])->name('comments.unsupport');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('stories/{story}/vote', [VoteController::class, 'store'])->name('stories.vote.store');
    Route::delete('stories/{story}/vote', [VoteController::class, 'destroy'])->name('stories.vote.destroy');
    // For the user's dashboard (after login)
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('user/regions/{country}', RegionController::class)->name('geography.regions');

    Route::prefix('user')->name('userzone.')->group(function () {
        Route::get('trash', [TrashController::class, 'index'])->name('trash.index');
        Route::patch('stories/{story}/restore', [TrashController::class, 'restore'])->withTrashed()->name('stories.restore');
        Route::resource('stories', App\Http\Controllers\Userzone\StoryController::class);
        Route::resource('locations', LocationController::class)->only(['create', 'store']);
    });

    Route::prefix('admin')->name('admin.')->middleware('can:admin')->group(function () {
        Route::get('/', App\Http\Controllers\Admin\DashboardController::class)->name('dashboard');
        Route::resource('stories', App\Http\Controllers\Admin\StoryController::class)->only(['update', 'destroy']);
        Route::patch('stories/{story}/restore', [App\Http\Controllers\Admin\StoryController::class, 'restore'])->withTrashed()->name('stories.restore');
        Route::resource('comments', App\Http\Controllers\Admin\CommentController::class)->only(['update', 'destroy']);
        Route::resource('users', UserController::class)->only('update');
        Route::resource('locations', App\Http\Controllers\Admin\LocationController::class)->only(['update', 'destroy']);
        Route::resource('tags', TagController::class)->only(['store', 'update', 'destroy']);
    });

    // For the user's profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
