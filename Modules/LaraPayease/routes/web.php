<?php

use Illuminate\Support\Facades\Route;
use Modules\LaraPayease\Http\Controllers\StripePaymentController;
use Modules\LaraPayease\Http\Controllers\PaystackPaymentController;

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
Route::post('payease/stripe', [StripePaymentController::class, 'prepareCharge'])->name('payease.stripe')->middleware('throttle:30,1');
Route::post('payease/paystack', [PaystackPaymentController::class, 'prepareCharge'])->name('payease.paystack')->middleware('throttle:30,1');
Route::get('payease/paystack/callback', [PaystackPaymentController::class, 'handleCallback'])->name('payease.paystack.callback')->middleware('throttle:60,1');
