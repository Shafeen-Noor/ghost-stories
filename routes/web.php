<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');
Route::redirect('/dashboard', '/')->middleware('auth')->name('dashboard');

require __DIR__.'/auth.php';
