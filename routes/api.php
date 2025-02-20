<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\TransferController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Route::get('/transactions/{id}', [TransactionController::class, 'show']);

    Route::get('/transactions/{id}', [TransactionController::class, 'show'])
        ->middleware('user.role:view-transaction');

    Route::post('/transfer', [TransferController::class, 'store'])
        ->middleware('user.role:request-transfer');
});
