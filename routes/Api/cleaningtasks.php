<?php

use App\Http\Controllers\Api\CleaningTaskController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth:sanctum', \App\Http\Middleware\SeniorManagerMiddleware::class])->group(function () {
        Route::prefix('cleaning-tasks')->group(function() {
        Route::post('/', [CleaningTaskController::class, 'create']);  // Create cleaning task
        Route::get('/', [CleaningTaskController::class, 'index']);    // Get all tasks
        Route::get('{id}', [CleaningTaskController::class, 'show']);   // Get a single task
        Route::put('{cleaning_task}', [CleaningTaskController::class, 'update']);  // Update a task
        Route::delete('{id}', [CleaningTaskController::class, 'destroy']);  // Delete a task
    });
});