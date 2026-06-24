<?php

use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/',[HomeController::class,'index']);


Route::post('/checkout/{plan}', [\App\Http\Controllers\CheckoutController::class, 'initiate'])
     ->name('checkout')
     ->middleware('auth');

Route::get('/payment/success', [\App\Http\Controllers\CheckoutController::class, 'success'])
     ->name('payment.success')
     ->middleware('auth');

Route::get('/payment/cancel', [\App\Http\Controllers\CheckoutController::class, 'cancel'])
     ->name('payment.cancel')
     ->middleware('auth');

Route::post('/payment/razorpay/verify', [\App\Http\Controllers\CheckoutController::class, 'verifyRazorpay'])
     ->middleware('auth');

Route::get('/my-invoices', [InvoiceController::class, 'index']);