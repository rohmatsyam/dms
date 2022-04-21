<?php

use Illuminate\Http\Request;
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

Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource("/role", App\Http\Controllers\Api\RoleController::class)->middleware('isAdmin');
    Route::apiResource("/user", App\Http\Controllers\Api\UserController::class);
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
});
