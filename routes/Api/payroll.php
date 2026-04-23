<?php
use App\Http\Controllers\Api\PayrollController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum',   \App\Http\Middleware\CheckStoreAccess::class])->group(function () {
    // Store-specific payroll routes (SecondShiftStoreManager and ThirdShiftStoreManager)
    Route::prefix('stores/{store}/payroll')->group(function () {
    
        // Route to create a payroll entry (Second Shift Manager sends scorecard to payroll)

        Route::post('/create', [PayrollController::class, 'create']);
        Route::get('/storemanagerindex', [PayrollController::class, 'indexByStore']);
        Route::get('/storemanagershow/{id}', [PayrollController::class, 'showByStore']);

   
        // Route for Third Shift Store Manager to approve payroll
       Route::patch('/approve-third-shift/{id}', [PayrollController::class, 'approveByThirdShiftStoreManager'])->whereNumber('id');
       Route::delete('/softdelete/{id}', [PayrollController::class, 'deleteByStore']);
       Route::patch('/restore/{id}', [PayrollController::class, 'restoreByStore']);
       Route::delete('/force/{id}', [PayrollController::class, 'forceDeleteByStore']);

    
    });
});

Route::middleware(['auth:sanctum', \App\Http\Middleware\SeniorManagerMiddleware::class])->group(function () {
    // Routes for Senior Manager to approve payroll
    Route::prefix('payroll')->group(function () {
        Route::get('/senior/index', [PayrollController::class, 'indexAll']);
        Route::get('/senior/show/{id}', [PayrollController::class, 'showAll']);

        // Route for Senior Manager to approve payroll for any store
        Route::patch('/approve-senior-manager/{id}', [PayrollController::class, 'approveBySeniorManager']);

        Route::delete('/softdelete/{id}', [PayrollController::class, 'deleteAll']);
        Route::patch('/restore/{id}', [PayrollController::class, 'restoreAll']);
        Route::delete('/force/{id}', [PayrollController::class, 'forceDeleteAll']);

    });
});