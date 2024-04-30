<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExchangeController;
use Illuminate\Http\Middleware\HandleCors;
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

Route::prefix('v1')->middleware(HandleCors::class)->group(function () {

    /*
     * Public routes
     */
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('refresh-token', [AuthController::class, 'refreshToken']);
    });

    Route::post('gateway/payment', [ExchangeController::class, 'storePayment']);

    /*
     * Authenticated routes
     */
    Route::middleware('auth:api')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('verify-profile', [AuthController::class, 'verifyProfile']);
        });

        Route::prefix('exchange')->group(function () {
            Route::get('rates', [ExchangeController::class, 'index']);
            Route::post('send', [ExchangeController::class, 'sendMoney']);
        });
    });

});
