<?php

// use Illuminate\Http\Request;
use App\Http\Controllers\Api\Customer_Loyality_Api\{AuthController,TransactionController,CustomerPhotoController,BannerController};
use Illuminate\Support\Facades\Route;


Route::get('/prefixes', [AuthController::class, 'getPrefixes']);
Route::get('/country-code', [AuthController::class, 'country_code']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

Route::middleware('auth:sanctum', 'token.session')->group(function () {
        Route::get('/banners', [BannerController::class, 'index']);
        Route::post('/update-profile', [AuthController::class, 'updateProfile']);
        Route::post('/rewards/history', [TransactionController::class, 'rewardsHistory']);
        Route::post('/customer/photo/upload', [CustomerPhotoController::class, 'upload']);
        Route::get('/customer/photo/show', [CustomerPhotoController::class, 'show']);
        Route::post('/save-fcm-token', [AuthController::class, 'saveFcmToken']);
});