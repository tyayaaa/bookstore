<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\GuestUserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/book', BookController::class);
Route::get('/authors/top', [AuthorController::class, 'index']);
Route::post('/rating', [RatingController::class, 'store']);
Route::get('/authors', [AuthorController::class, 'all']);
Route::post('/auto-register', [GuestUserController::class, 'register']);
