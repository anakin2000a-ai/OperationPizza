<?php
use App\Http\Controllers\Api\TaskAssignmentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum',\App\Http\Middleware\CheckStoreAccess::class)->group(function () {
    // Create a task assignment
    Route::post('stores/{store}/assign-task', [TaskAssignmentController::class, 'createTaskAssignment']);
    
    // Get all task assignments for a specific store
    Route::get('stores/{store}/task-assignments', [TaskAssignmentController::class, 'getAssignments']);
    
    // Update a task assignment
    Route::put('stores/{store}/task-assignments/{id}', [TaskAssignmentController::class, 'updateTaskAssignment']);
    
    // Delete a task assignment
    Route::delete('stores/{store}/task-assignments/{id}', [TaskAssignmentController::class, 'deleteTaskAssignment']);
    
    // Export task assignments to ODF (or PDF)
    Route::post('stores/{store}/export-task-assignments', [TaskAssignmentController::class, 'exportTaskAssignmentsToPDF']);
});