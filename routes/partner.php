<?php

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Partner_Loyality_Api\{RedemptionController,PartnerAuthController,TransactionController};

Route::post('/login', [PartnerAuthController::class, 'login']);
Route::post('/verify-otp', [PartnerAuthController::class, 'verifyOtp']);

Route::middleware('auth:sanctum', 'token.session')->group(function () {
      Route::get('/customer/wallet/{qr_code}',[RedemptionController::class, 'wallet']);
      Route::post('/redeem-points',[RedemptionController::class, 'redeem']);
      Route::post('/transaction-history',[TransactionController::class, 'transactionHistory']);
      
});
