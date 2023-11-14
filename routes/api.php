<?php

use App\Http\Controllers\Api\AnalysController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShapeController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WishListController;
use App\Http\Controllers\Api\XaphuongController;
use App\Http\Controllers\TestController;
use App\Http\Middleware\CheckRoleUser;
use App\Models\Slider;
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
Route::post('/register',[LoginController::class,'register']);

Route::middleware('auth:sanctum','checkRole')->prefix('admin')->group(function () {
    Route::apiResource('categories',CategoryController::class);
    Route::apiResource('products',ProductController::class);
    Route::post('/change_status_product',[ProductController::class,'updateStatusProduct']);
    Route::apiResource('/suppliers',SupplierController::class);
    Route::apiResource('/orders', OrderController::class);
    Route::post('/change_status_order', [OrderController::class,'changeStatusOrder'])->name('admin.change_status_order');
    // Route::get('/order',[OrderController::class,'getOrderByStatus']);
    Route::apiResource('/users',UserController::class);
    Route::apiResource('/analys',AnalysController::class);
    Route::get('/total_user',[AnalysController::class,'totalUser']);
    Route::get('/total_product',[AnalysController::class,'totalProduct']);
    Route::post('/change_password',[UserController::class,'changePassword']);
    Route::get('products-admin',[ProductController::class, "getProductForAdmin"]);
    Route::post('update-image-product',[ProductController::class, "changeImageProduct"]);
    Route::delete("/cancel_order/{id}", [OrderController::class, "destroy"]);
    Route::post('/update-product',[ProductController::class,"updateImageProduct"]);
    Route::apiResource("/slider",SliderController::class);
    Route::get('/order-by-ordercode', [OrderController::class, "getOrderByCode"]);
    Route::post("/change-status-slider",[SliderController::class, "changeStatusSlider"]);
    Route::get('/products-by-name',[ProductController::class,'getProductByName']);
    Route::apiResource('/shapes', ShapeController::class);
    Route::apiResource('/materials', MaterialController::class);
});

Route::get('/product',[ProductController::class,'index']);
Route::get('/product-details/{id}',[ProductController::class,'show']);
Route::apiResource('/tinh',XaphuongController::class);
Route::post('/quan',[XaphuongController::class,'getQuanHuyen']);
Route::post('/xa',[XaphuongController::class,'getXaPhuong']);
// Route::apiResource("/slider",SliderController::class);
Route::apiResource('categories',CategoryController::class);
Route::get('/product/10-product',[ProductController::class,'get10Product']);
Route::get('/product_has_price_new',[ProductController::class,'getProductHasPriceNew']);
Route::get("/slider-active",[SliderController::class, "getSliderActive"]);
Route::get('/categories-by-id-client/{id}',[CategoryController::class, "show"]);
Route::get('/product-by-name',[ProductController::class,'getProductByNameClient']);
Route::get('/shape', [ShapeController::class, 'index']);
Route::get('/material', [MaterialController::class, 'index']);

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/change_status_order', [OrderController::class,'changeStatusOrder'])->name('user.change_status_order');
    Route::apiResource('/cart',CartController::class);
    Route::get('order_history',[OrderController::class,'orderHistory']);
    Route::get('/profile/{id}',[UserController::class,'getProfile']);
    Route::post('/change_password',[UserController::class,'changePassword']);
    Route::post('/order', [OrderController::class, "store"]);
    Route::delete("/cancel_order/{id}", [OrderController::class, "destroy"]);
    Route::apiResource('/wish_list',WishListController::class);

});

