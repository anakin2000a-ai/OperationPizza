<?php

use App\Http\Controllers\Api\LoanController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
    \App\Http\Middleware\SeniorManagerMiddleware::class,
    'throttle:60,1',
])->group(function () {
    Route::get('loans', [LoanController::class, 'index']);
    Route::post('loans', [LoanController::class, 'store']);
    Route::get('loans/{id}', [LoanController::class, 'show']);
    Route::put('loans/{id}', [LoanController::class, 'update']);
    Route::delete('loans/{id}', [LoanController::class, 'destroy']);

    Route::post('loans/{id}/restore', [LoanController::class, 'restore']);
    Route::delete('loans/{id}/force-delete', [LoanController::class, 'forceDelete']);
});