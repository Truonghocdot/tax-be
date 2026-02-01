<?php

use App\Http\Controllers\CommonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;   

Route::get('/user', function (Request $request) {
    return new \App\Http\Resources\UserResource($request->user());
})->middleware('auth:sanctum');

Route::post('/login', [App\Http\Controllers\UserController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\UserController::class, 'logout']);
Route::post('/register', [App\Http\Controllers\UserController::class, 'register']);

Route::get('banks', [CommonController::class, 'banks']);

Route::post('/identity-verification', [App\Http\Controllers\UserController::class, 'identityVerification'])->middleware('auth:sanctum');
Route::post('/user/update-profile', [App\Http\Controllers\UserController::class, 'updateProfile'])->middleware('auth:sanctum');
Route::post('/user/add-bank', [App\Http\Controllers\UserController::class, 'addBank'])->middleware('auth:sanctum');
Route::get('/user/list-bank', [App\Http\Controllers\UserController::class, 'listBank'])->middleware('auth:sanctum');
Route::get('/qr-bank', [App\Http\Controllers\UserController::class, 'qrBank'])->middleware('auth:sanctum');
