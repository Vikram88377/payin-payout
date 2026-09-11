<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MerchantController;
use App\Http\Controllers\Api\PayinController;
use App\Http\Controllers\Api\PayoutController;



// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('/payins', [PayinController::class, 'store']);
Route::get('/payins/{transactionId}', [PayinController::class, 'show']);
 
Route::post('/payouts', [PayoutController::class, 'store']);
Route::get('/payouts/{transactionId}', [PayoutController::class, 'show']);
 
Route::get('/merchants/{id}/wallet', [MerchantController::class, 'wallet']);