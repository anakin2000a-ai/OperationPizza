<?php
use App\Http\Controllers\Api\PayrollController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum',   \App\Http\Middleware\CheckStoreManagerRole::class])->group(function () {
    // Store-specific payroll routes (SecondShiftStoreManager and ThirdShiftStoreManager)
    Route::prefix('stores/{store}/payroll')->group(function () {
        // Route to create a payroll entry (Second Shift Manager sends scorecard to payroll)
        Route::post('/create', [PayrollController::class, 'create']);
        
        // Route for Third Shift Store Manager to approve payroll
        Route::patch('/approve-third-shift/{id}', [PayrollController::class, 'approveByThirdShiftStoreManager']);
    });
});

Route::middleware(['auth:sanctum'])->group(function () {
    // Routes for Senior Manager to approve payroll
    Route::prefix('payroll')->group(function () {
        // Route for Senior Manager to approve payroll for any store
        Route::patch('/approve-senior-manager/{id}', [PayrollController::class, 'approveBySeniorManager']);
    });
});