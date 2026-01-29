<?php

use App\Http\Controllers\CommonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [App\Http\Controllers\UserController::class, 'login']);
Route::post('/register', [App\Http\Controllers\UserController::class, 'register']);

Route::get('banks', [CommonController::class, 'banks']);

Route::post('/identity-verification', [App\Http\Controllers\UserController::class, 'identityVerification'])->middleware('auth:sanctum');
Route::post('/user/update-profile', [App\Http\Controllers\UserController::class, 'updateProfile'])->middleware('auth:sanctum');
Route::post('/user/add-bank', [App\Http\Controllers\UserController::class, 'addBank'])->middleware('auth:sanctum');
