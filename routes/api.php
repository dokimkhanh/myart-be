<?php

use App\Http\Controllers\AdminDashboard;
use App\Http\Controllers\ArtController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\UserController;
use App\Models\FollowerUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('register', [AuthController::class, 'register']);
});

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'art'], function () {
    Route::get('', [ArtController::class, 'index'])->withoutMiddleware('auth:sanctum');
    Route::get('{id}', [ArtController::class, 'show'])->withoutMiddleware('auth:sanctum');
    Route::post('', [ArtController::class, 'store']);
    Route::put('{id}', [ArtController::class, 'update']);
    Route::delete('{id}', [ArtController::class, 'destroy']);
});

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'follow'], function () {
    Route::post('follow/{id}', [FollowController::class, 'follow']);
    Route::post('unfollow/{id}', [FollowController::class, 'unfollow']);
    Route::get('checkfollow/{id}', [FollowController::class, 'checkFollow']);
});

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'interact'], function () {
    Route::post('like/{id}', [LikeController::class, 'like']);
    Route::post('unlike/{id}', [LikeController::class, 'unlike']);
    Route::get('islike/{id}', [LikeController::class, 'isLike']);
});

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'feed'], function () {
    Route::get('', [ArtController::class, 'feed']);
});

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'comment'], function () {
    Route::post('', [CommentController::class, 'comment']);
    Route::delete('{id}', [CommentController::class, 'destroy']);
    Route::get('get-all', [CommentController::class, 'getAll']);
});

// Route::resource('users', UserController::class);

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'user'], function () {
    Route::get('profile', [UserController::class, 'profile']);
    Route::get('all', [UserController::class, 'index']);
    Route::post('create', [UserController::class, 'store']);
    Route::get('{id}', [UserController::class, 'show'])->withoutMiddleware('auth:sanctum');
    Route::put('update/{id}', [UserController::class, 'update']);
    Route::post('delete/{id}', [UserController::class, 'destroy']);
});

Route::get('topfollowed', [UserController::class, 'topFollowedUsers']);

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'admin'], function () {
    Route::get('dashboard', [AdminDashboard::class, 'index']);
});
