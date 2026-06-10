<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::get('/user','show')->name('user.show')->middleware('auth:sanctum');
    Route::post('/register','register')->name('register');
    Route::post('/login','login')->name('login');
    Route::post('/logout','logout')->name('logout');
});

Route::controller(PostController::class)->prefix('/posts')->group(function () {
    Route::get('/','index')->name('posts.index');
    Route::post('/','store')->name('posts.store')->middleware('auth:sanctum');
    Route::get('/top-viewed','topViewed')->name('posts.top-viewed');
    Route::get('/{post}', 'show')->name('posts.show');
    Route::get('/{post}/analytics/daily','dailyAnalytics')->name('posts.analytics.daily');
    Route::get('/{post}/analytics/summary','AnalyticsSummary')->name('posts.analytics.summary');
});

