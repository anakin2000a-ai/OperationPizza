<?php

use App\Http\Controllers\Api\EmployeeLoanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', \App\Http\Middleware\SeniorManagerMiddleware::class,'throttle:60,1',])->group(function () {
    Route::prefix('employee-loans')->group(function () {
        Route::get('/', [EmployeeLoanController::class, 'index']);
        Route::post('/', [EmployeeLoanController::class, 'store']);
        Route::get('/{id}', [EmployeeLoanController::class, 'show']);
        Route::delete('/{id}', [EmployeeLoanController::class, 'destroy']);
    });
});