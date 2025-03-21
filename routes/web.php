<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return view('welcome');
});
Route::post('/initiate-payment', [PaymentController::class, 'initiatePayment']);
Route::post('/airtel-callback', [PaymentController::class, 'handleCallback']);
// In a route or controller:
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});