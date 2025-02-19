<?php

use App\Http\Controllers\API\ArticleController;
use App\Http\Controllers\API\AuthorsController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\SourceController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\AuthController;
use App\Http\Controllers\API\UserPreferenceController;

Route::controller(RegisterController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [RegisterController::class, 'logout']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/sources', [SourceController::class, 'index']);
Route::get('/authors', [AuthorsController::class, 'index']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/articles', [ArticleController::class, 'index']);
// });

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user/preferences', [UserPreferenceController::class, 'store']);
    Route::get('/user/preferences', [UserPreferenceController::class, 'show']);
});
