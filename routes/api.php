<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('categories')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\CategoryController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\CategoryController::class, 'store']);
    Route::get('/{category}', [App\Http\Controllers\Api\CategoryController::class, 'show']);
    Route::put('/{category}', [App\Http\Controllers\Api\CategoryController::class, 'update']);
    Route::delete('/{category}', [App\Http\Controllers\Api\CategoryController::class, 'destroy']);
    Route::get('/{category}/posts', [App\Http\Controllers\Api\CategoryController::class, 'posts']);
    Route::get('/{category}/children', [App\Http\Controllers\Api\CategoryController::class, 'children']);
    Route::get('/{category}/breadcrumb', [App\Http\Controllers\Api\CategoryController::class, 'breadcrumb']);
});


Route::prefix('posts')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\PostController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\PostController::class, 'store']);
    Route::get('/{post}', [App\Http\Controllers\Api\PostController::class, 'show']);
    Route::put('/{post}', [App\Http\Controllers\Api\PostController::class, 'update'])->middleware('can:publish,post');;
    Route::delete('/{post}', [App\Http\Controllers\Api\PostController::class, 'destroy'])->middleware('can:publish,post');;
    Route::post('/{post}/publish', [App\Http\Controllers\Api\PostController::class, 'publish'])->middleware('can:publish,post');;
    Route::post('/{post}/unpublish', [App\Http\Controllers\Api\PostController::class, 'unpublish'])->middleware('can:publish,post');;
    Route::post('/{post}/archive', [App\Http\Controllers\Api\PostController::class, 'archive'])->middleware('can:publish,post');;
    Route::get('/{post}/related', [App\Http\Controllers\Api\PostController::class, 'related'])->middleware('can:publish,post');;
    Route::get('/{post}/next', [App\Http\Controllers\Api\PostController::class, 'next']);
    Route::get('/{post}/previous', [App\Http\Controllers\Api\PostController::class, 'previous']);
    Route::post('/{post}/like', [App\Http\Controllers\Api\PostController::class, 'like']);
    Route::post('/{post}/unlike', [App\Http\Controllers\Api\PostController::class, 'unlike']);
});


Route::prefix('tags')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\TagController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\TagController::class, 'store']);
    Route::get('/{tag}', [App\Http\Controllers\Api\TagController::class, 'show']);
    Route::put('/{tag}', [App\Http\Controllers\Api\TagController::class, 'update']);
    Route::delete('/{tag}', [App\Http\Controllers\Api\TagController::class, 'destroy']);
    Route::get('/{tag}/posts', [App\Http\Controllers\Api\TagController::class, 'posts']);
    Route::get('/{tag}/popular-posts', [App\Http\Controllers\Api\TagController::class, 'popularPosts']);
    Route::get('/{tag}/recent-posts', [App\Http\Controllers\Api\TagController::class, 'recentPosts']);
    Route::get('/{tag}/random-posts', [App\Http\Controllers\Api\TagController::class, 'randomPosts']);
    Route::get('/{tag}/related', [App\Http\Controllers\Api\TagController::class, 'related']);
    Route::get('/{tag}/statistics', [App\Http\Controllers\Api\TagController::class, 'statistics']);
    Route::get('/popular/popular', [App\Http\Controllers\Api\TagController::class, 'popular']);
    Route::get('/with-post-count/with-post-count', [App\Http\Controllers\Api\TagController::class, 'withPostCount']);
    Route::post('/find-or-create', [App\Http\Controllers\Api\TagController::class, 'findOrCreate']);
    Route::post('/find-or-create-multiple', [App\Http\Controllers\Api\TagController::class, 'findOrCreateMultiple']);
});

