<?php
use App\Http\Controllers\Api\LoanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', \App\Http\Middleware\SeniorManagerMiddleware::class])->group(function () {
    Route::apiResource('loans', LoanController::class);

    // Soft delete, restore, and force delete routes
    Route::post('loans/{id}/restore', [LoanController::class, 'restore']);
    Route::delete('loans/{id}/force-delete', [LoanController::class, 'forceDelete']);
});