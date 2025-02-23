<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\FacebookController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    // Facebook API Routes
    Route::prefix('facebook')->group(function () {
        Route::get('/pages', [FacebookController::class, 'fetchFacebookPages'])->name('fetch.facebook.pages');
        Route::get('/analytics/{pageId}', [FacebookController::class, 'fetchPageAnalytics'])->name('fetch.facebook.analytics');
    });
});

// Facebook Authentication Routes
Route::get('/login/facebook', [FacebookController::class, 'redirectToProvider'])->name('facebook.login');
Route::get('/login/facebook/callback', [FacebookController::class, 'handleProviderCallback'])->name('facebook.callback');
Route::get('/logout', [FacebookController::class, 'logout'])->name('logout');
