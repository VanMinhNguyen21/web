<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\CheckRoleUser;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/login',[LoginController::class,'login']);

Route::middleware('auth:sanctum','checkRole')->prefix('admin')->group(function () {
    Route::apiResource('categories',CategoryController::class);
    Route::apiResource('products',ProductController::class);
    Route::post('/change_status_product',[ProductController::class,'updateStatusProduct']);
    Route::apiResource('/suppliers',SupplierController::class);
    Route::apiResource('/orders', OrderController::class);
    Route::post('/change_status_order', [OrderController::class,'changeStatusOrder'])->name('admin.change_status_order');
    // Route::get('/order',[OrderController::class,'getOrderByStatus']);
    Route::apiResource('/users',UserController::class);

});
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/product',[ProductController::class,'index']);
    Route::post('/change_status_order', [OrderController::class,'changeStatusOrder'])->name('user.change_status_order');
    Route::apiResource('/cart',CartController::class);
    Route::get('order_history',[OrderController::class,'orderHistory']);
    Route::get('/profile/{id}',[UserController::class,'getProfile']);
});

