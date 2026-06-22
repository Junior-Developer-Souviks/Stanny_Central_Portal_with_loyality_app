<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\FabricController;
use App\Http\Controllers\Api\BusinessTypeController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CashBookController;
use App\Http\Controllers\Api\AddPaymentController;
use App\Http\Controllers\Api\ExpenseController;
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
Route::get('/country-list', [AuthController::class, 'CountryList']);
Route::get('/country/details/{id}', [AuthController::class, 'CountryDetailsByID']);
Route::get('/business-type', [BusinessTypeController::class, 'index']);
Route::post('/check-device', [AuthController::class, 'checkDevice']);
Route::post('/user-login', [AuthController::class, 'userLogin']); // Step 1:
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/set-mpin', [AuthController::class, 'setMpin']); // Step 5:
Route::post('/mpin-login', [AuthController::class, 'mpinLogin']); // Step 6:

Route::post('/forgot-mpin', [AuthController::class, 'forgotMpin']); // Step 2:
Route::post('/verify-otp-mpin', [AuthController::class, 'verifyOtpMpin']); //Step 3:
Route::post('/reset-mpin', [AuthController::class, 'resetMpin']); //Step 4:
Route::post('/forget-password', [AuthController::class, 'forgotPassword']);

// Route::middleware('auth:sanctum', 'token.expiry')->group(function () {
Route::middleware('auth:sanctum', 'token.session')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // profile
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/dashboard', [AuthController::class, 'dashboard']);
    Route::get('/customer/list', [AuthController::class, 'customer_list']);
    Route::get('/customer/details/{id}', [AuthController::class, 'customer_details']);
    Route::get('/customer/filter', [AuthController::class, 'customer_filter']);
    Route::get('/customer/default/data', [AuthController::class, 'customer_default_data']);
    Route::post('/customer/store', [AuthController::class, 'customer_store']);
    Route::post('/customer/update/{id}', [AuthController::class, 'customer_update']);
    Route::get('/customer/order/list', [OrderController::class, 'customer_order_list']);
    Route::get('/customer/order/detail', [OrderController::class, 'customer_order_detail']);
    Route::get('/cashbook',[CashBookController::class,'cashbook']);
    Route::post('/add-payment-receipt', [AddPaymentController::class, 'addPaymentReceipt']);
    Route::get('/payment-collections', [AddPaymentController::class, 'paymentCollection']);

    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('user.index');
        Route::post('/store', [UserController::class, 'store'])->name('user.store');
        Route::get('/list', [UserController::class, 'list'])->name('user.list');
        Route::get('/search', [UserController::class, 'search'])->name('user.search');
        Route::get('/show/{id}', [UserController::class, 'show'])->name('user.show');
    });

    Route::prefix('category')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('category.index');
        Route::get('/category-collection-wise/{categoryid}', [CategoryController::class, 'getCategoriesByCollection'])->name('category.collection-wise');
    });
    
    Route::get('/collection', [CollectionController::class, 'index']);
    
    Route::prefix('product')->group(function () {
        Route::get('/all-products', [ProductController::class, 'getAllProducts']);
        Route::get('/products-category-collection-wise', [ProductController::class, 'getProductsByCategoryAndCollection']);
        Route::get('/products-collection-wise', [ProductController::class, 'getProductsByCollection']);
        Route::get('/fabric/{id}', [FabricController::class, 'index']);
        Route::get('/check/price', [ProductController::class, 'checkPrice']);
        Route::get('/measurement-product-wise', [ProductController::class, 'getMeasurementProductwise']);
        
    });
    
    Route::prefix('catalogue')->group(function () {
        Route::get('/list', [ProductController::class, 'catalogueList']);
        Route::get('/pages', [ProductController::class, 'pages']);
        Route::get('/page/item', [ProductController::class, 'pageItem']);
        Route::get('/products-collection-wise', [ProductController::class, 'getProductsByCollection']);
    });
    
    Route::prefix('order')->group(function () { 
        Route::get('/next-order-id', [OrderController::class, 'fetchNextOrderId']);
        Route::post('/skip-order', [OrderController::class, 'skipOrder']);
        Route::post('/store', [OrderController::class, 'createOrder']);
        Route::post('/video/store', [OrderController::class, 'createVideo']);
        Route::get('/list', [OrderController::class, 'index']);
        Route::get('/detail', [OrderController::class, 'detail']);
        Route::get('/ledger-view', [OrderController::class, 'ledgerView']);
        Route::get('/bill/{orderId}', [OrderController::class, 'downloadBill']);
        // Route::post('/payment-receipt-save', [OrderController::class, 'paymentReceiptSave']);
    });
    
    Route::prefix('/expenses')->group(function () { 
        Route::get('/list', [ExpenseController::class, 'expenseList']);
        Route::get('/types', [ExpenseController::class, 'types']);
        Route::post('/add-expense', [ExpenseController::class, 'addExpense']);
    });
    // More routes related to products can be added here
    // Route::get('/products', [ProductController::class, 'index']);
    // Route::put('/products/{id}', [ProductController::class, 'update']);
    // Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});