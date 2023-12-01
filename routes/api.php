<?php

use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Auth\ResetPasswordController;
use App\Http\Controllers\API\Auth\VerificationController;
use App\Http\Controllers\API\TodoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/verify-email', [VerificationController::class, 'verifyEmail']);
Route::post('/resend-verification-code', [VerificationController::class, 'resendVerificationCode']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::resource('todos', TodoController::class);

    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    });
});
