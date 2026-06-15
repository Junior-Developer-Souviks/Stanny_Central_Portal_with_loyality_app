<?php

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Customer_Loyality_Api\{AuthController,TransactionController};


Route::get('/prefixes', [AuthController::class, 'getPrefixes']);
Route::get('/country-code', [AuthController::class, 'country_code']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

Route::middleware('auth:sanctum', 'token.session')->group(function () {
         Route::post('/update-profile', [AuthController::class, 'updateProfile']);
         Route::post('/rewards/history', [TransactionController::class, 'rewardsHistory']);
        Route::post('/save-fcm-token', [AuthController::class, 'saveFcmToken']);
});