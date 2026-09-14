<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\FavoriteController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::get('characters', [CharacterController::class, 'index']);
Route::get('characters/{character}', [CharacterController::class, 'show'])->whereNumber('character');

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::post('favorites/{character}', [FavoriteController::class, 'store'])->whereNumber('character');
    Route::delete('favorites/{character}', [FavoriteController::class, 'destroy'])->whereNumber('character');
});
