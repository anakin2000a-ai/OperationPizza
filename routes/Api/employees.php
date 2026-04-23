<?php

use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum',\App\Http\Middleware\CheckStoreAccess::class, 'throttle:60,1'])->group(function () {
    Route::prefix('stores/{store}')->group(function () {
        Route::post('/employees', [EmployeeController::class, 'store']);
        Route::put('/employees/{employeeId}', [EmployeeController::class, 'update']);
        Route::get('/employees', [EmployeeController::class, 'index']);
        Route::get('/employees/{employeeId}', [EmployeeController::class, 'show']);
        Route::delete('/employees/{employeeId}', [EmployeeController::class, 'destroy']);
        Route::delete('/employees/{employeeId}/deductions', [EmployeeController::class, 'destroyDeductions']);
    });
});