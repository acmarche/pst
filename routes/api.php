<?php

declare(strict_types=1);

use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/users/usernames', [UserController::class, 'index'])
    ->middleware('auth:sanctum');
