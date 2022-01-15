<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* PUBLIC ROUTES */

Route::post('/login', [AuthController::class, 'login']);

Route::get('/albums', [AlbumController::class, 'index']);
Route::get('/album/{album}', [AlbumController::class, 'show']);

Route::post('/comment', [CommentController::class, 'store']);
Route::post('/comment/{comment}/report', [CommentController::class, 'report']);

/* PRIVATE ROUTES */

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/album', [AlbumController::class, 'store']);
    Route::patch('/album/{album}', [AlbumController::class, 'update']);
    Route::delete('/album/{album}', [AlbumController::class, 'destroy']);

    Route::patch('/comment/{comment}', [CommentController::class, 'update']);
    Route::delete('/comment/{comment}', [CommentController::class, 'delete']);
    Route::get('/comments/reports', [CommentController::class, 'reports']);

    Route::post('/images', [ImageController::class, 'store']);
    Route::patch('/image/{image}', [ImageController::class, 'update']);
    Route::delete('/image/{image}', [ImageController::class, 'destroy']);
});
