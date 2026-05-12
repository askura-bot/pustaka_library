<?php

use App\Http\Controllers\ArticleFileController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Livewire\ArticleDetail;
use App\Livewire\GlobalChat;
use App\Livewire\KtiTypeManager;
use App\Livewire\LibraryDashboard;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', LibraryDashboard::class)->name('dashboard');
    Route::livewire('/library/templates', KtiTypeManager::class)->name('library.templates');
    Route::livewire('/library/ask-ai', GlobalChat::class)->name('library.ask-ai');
    Route::livewire('/library/article/{article}', ArticleDetail::class)->name('library.article');
    Route::get('/library/article/{article}/file', [ArticleFileController::class, 'show'])->name('library.article.file');
});

Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

require __DIR__.'/settings.php';
