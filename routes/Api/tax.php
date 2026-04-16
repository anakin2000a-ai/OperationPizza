<?php
use App\Http\Controllers\Api\TaxController;
use Illuminate\Support\Facades\Route;

// Tax CRUD Routes (Protected by Sanctum and Senior Manager authorization)
Route::middleware(['auth:sanctum', \App\Http\Middleware\SeniorManagerMiddleware::class])->group(function () {
    Route::apiResource('taxes', TaxController::class);

    // Soft delete, restore, and force delete routes
    Route::post('taxes/{id}/restore', [TaxController::class, 'restore']);
    Route::delete('taxes/{id}/force-delete', [TaxController::class, 'forceDelete']);
});