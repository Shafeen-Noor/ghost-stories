<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\Userzone\DashboardController;
use App\Http\Controllers\Userzone\LocationController;
use App\Http\Controllers\Userzone\ProfileController;
use App\Http\Controllers\Userzone\TrashController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

/*
 * Public Website routes
 */
Route::get('/', WelcomeController::class)->name('welcome');

Route::resource('stories', StoryController::class)->only(['index', 'show']);

/*
 * Authentication routes
 */
require __DIR__.'/auth.php';

/*
 * Userzone routes
 */
Route::middleware('auth')->group(function () {
    Route::post('stories/{story}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('stories/{story}/vote', [VoteController::class, 'store'])->name('stories.vote.store');
    Route::delete('stories/{story}/vote', [VoteController::class, 'destroy'])->name('stories.vote.destroy');
    // For the user's dashboard (after login)
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('user/regions/{country}', RegionController::class)->name('geography.regions');

    Route::prefix('user')->name('userzone.')->group(function () {
        Route::get('trash', [TrashController::class, 'index'])->name('trash.index');
        Route::patch('stories/{story}/restore', [TrashController::class, 'restore'])->withTrashed()->name('stories.restore');
        Route::resource('stories', App\Http\Controllers\Userzone\StoryController::class)->except('show');
        Route::resource('locations', LocationController::class)->only(['create', 'store']);
    });

    // For the user's profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
