<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Callback\PaymentController;
use App\Http\Controllers\Api\CartsController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\SubscribeController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WishlistController;
use App\Models\Api\Transaction;
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
/* ====================================================== AUTH ==================================================== */

Route::prefix('auth')->group(function () {
    Route::get('list', [AuthController::class, 'list']);
    Route::get('list/{id}', [AuthController::class, 'list']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('check-pass', [AuthController::class, 'checkPass']);
    Route::post('verify', [AuthController::class, 'verify']);
    Route::post('update-pass', [AuthController::class, 'update_pass']);
    Route::post('forgot', [AuthController::class, 'forgot']);
    Route::post('check-reset', [AuthController::class, 'checkTokenResetPass']);
    Route::post('reset-confirm', [AuthController::class, 'resetPassAuth']);
    Route::post('upload-photo', [AuthController::class, 'upload_photo']);
    Route::post('update-status', [AuthController::class, 'updateStatus']);
    Route::post('update-role', [AuthController::class, 'updateRole']);
});
/* ====================================================== END AUTH ==================================================== */

/* ====================================================== CATEGORY ==================================================== */
Route::prefix('category')->group(function () {
    Route::get('list', [CategoryController::class, 'list']);
    Route::get('list/{id}', [CategoryController::class, 'list']);
    Route::post('store', [CategoryController::class, 'store']);
    Route::post('update', [CategoryController::class, 'update']);
    Route::post('update-icon', [CategoryController::class, 'updatePhoto']);
    Route::post('update-status', [CategoryController::class, 'updateStatus']);
    Route::post('delete', [CategoryController::class, 'delete']);
});
/* ====================================================== END CATEGORY ==================================================== */

/* ====================================================== STORE ==================================================== */
Route::prefix('store')->group(function () {
    Route::get('list', [StoreController::class, 'list']);
    Route::get('list/{id}', [StoreController::class, 'list']);
    Route::post('store', [StoreController::class, 'store']);
    Route::post('update', [StoreController::class, 'update']);
    Route::post('update-photo', [StoreController::class, 'updatePhoto']);
    Route::post('update-status', [StoreController::class, 'updateStatus']);
    Route::post('delete', [StoreController::class, 'delete']);
});
/* ====================================================== END STORE ==================================================== */

/* ====================================================== PRODUCT ==================================================== */
Route::prefix('product')->group(function () {
    Route::get('list', [ProductController::class, 'list']);
    Route::get('list/{id_user}', [ProductController::class, 'list']);
    Route::get('{slug}', [ProductController::class, 'listBySlug']);
    Route::get('list-store/{slug}', [ProductController::class, 'listByToko']);
    Route::post('store', [ProductController::class, 'store']);
    Route::post('update', [ProductController::class, 'update']);
    Route::post('update-photo', [ProductController::class, 'updatePhoto']);
    Route::post('update-status', [ProductController::class, 'updateStatus']);
    Route::post('delete', [ProductController::class, 'delete']);
});
/* ====================================================== END PRODUCT ==================================================== */

/* ====================================================== CART ==================================================== */
Route::prefix('cart')->group(function () {
    Route::get('list/{user_id}', [CartsController::class, 'list']);
    Route::get('sum/{user_id}', [CartsController::class, 'SumCart']);
    Route::post('store', [CartsController::class, 'store']);
    Route::post('update', [CartsController::class, 'update']);
    Route::post('minus-cart', [CartsController::class, 'minusCart']);
    Route::post('plus-cart', [CartsController::class, 'plusCart']);
    Route::post('delete', [CartsController::class, 'delete']);
});
/* ====================================================== END CART ==================================================== */

/* ====================================================== WISHLIST ==================================================== */
Route::prefix('wishlist')->group(function () {
    Route::get('list/{user_id}', [WishlistController::class, 'list']);
    Route::post('store', [WishlistController::class, 'store']);
    Route::post('delete', [WishlistController::class, 'delete']);
});
/* ====================================================== END WISHLIST ==================================================== */

/* ====================================================== SUBSCRIBE ==================================================== */
Route::prefix('subscribe')->group(function () {
    Route::get('list', [SubscribeController::class, 'list']);
    Route::post('store', [SubscribeController::class, 'store']);
    Route::post('delete', [SubscribeController::class, 'delete']);
});
/* ====================================================== END SUBSCRIBE ==================================================== */

/* ====================================================== PAYMENT METHOD ==================================================== */
Route::prefix('payment')->group(function () {
    Route::get('list', [PaymentMethodController::class, 'list']);
    Route::get('list/{id}', [PaymentMethodController::class, 'list']);
    Route::post('store', [PaymentMethodController::class, 'store']);
    Route::post('update', [PaymentMethodController::class, 'update']);
    Route::post('update-icon', [PaymentMethodController::class, 'updatePhoto']);
    Route::post('update-status', [PaymentMethodController::class, 'updateStatus']);
    Route::post('delete', [PaymentMethodController::class, 'delete']);
});
/* ====================================================== END PAYMENT METHOD ==================================================== */

/* ====================================================== REVIEW ==================================================== */
Route::prefix('review')->group(function () {
    Route::get('list', [ReviewController::class, 'list']);
    Route::post('store', [ReviewController::class, 'store']);
    Route::post('delete', [ReviewController::class, 'delete']);
});
/* ====================================================== END REVIEW ==================================================== */

/* ====================================================== TRANSACTION ==================================================== */
Route::prefix('transaction')->group(function () {
    Route::post('store', [TransactionController::class, 'store']);
});
/* ====================================================== END TRANSACTION ==================================================== */

/* ====================================================== CALLBACK ==================================================== */
Route::prefix('callback')->group(function () {
    Route::post('notif', [PaymentController::class, 'notif']);
});
/* ====================================================== END CALLBACK ==================================================== */