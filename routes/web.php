<?php

use App\Http\Controllers\VnpayController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::post('/vnpay',[VnpayController::class,'vnpay'])->name('vnpay');
Route::get('/vnpay/callback', [VnpayController::class,'callback'])->name('vnpay-callback');