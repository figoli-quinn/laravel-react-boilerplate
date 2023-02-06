<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TootController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\RegistrationController;

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

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [RegistrationController::class, 'register']);
Route::post('/register/verify/{id}/{hash}', [RegistrationController::class, 'verify']);

Route::post('/password', [PasswordController::class, 'store']);
Route::post('/password/reset', [PasswordController::class, 'reset']);
Route::get('/user/me', [UserController::class, 'me']);
Route::post('/register/resend-verification-email', [RegistrationController::class, 'resend']);

Route::get('/toots', [TootController::class, 'index']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/toots/{toot}/like', [TootController::class, 'like']);
    Route::post('/toots', [TootController::class, 'store']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::post('/users/{user}/follow', [UserController::class, 'follow']);
});
