<?php

use App\Http\Controllers\StoryController;
use App\Http\Controllers\Userzone\DashboardController;
use App\Http\Controllers\Userzone\LocationController;
use App\Http\Controllers\Userzone\ProfileController;
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
    // For the user's dashboard (after login)
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('user')->name('userzone.')->group(function () {
        Route::resource('stories', App\Http\Controllers\Userzone\StoryController::class)->except('show');
        Route::resource('locations', LocationController::class)->only(['create', 'store']);
    });

    // For the user's profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
