<?php

use App\Http\Controllers\Api\TaskAssignmentForEmployeeController;
use Illuminate\Support\Facades\Route;
 
Route::middleware('auth:sanctum')->group(function () {
    // View assigned tasks for the authenticated employee
    Route::get('employee/tasks', [TaskAssignmentForEmployeeController::class, 'getEmployeeTasks']);

    // Submit a task with a picture (employee marks task as completed)
    Route::post('tasks/{id}/submit', [TaskAssignmentForEmployeeController::class, 'submitTaskWithPicture']);
});