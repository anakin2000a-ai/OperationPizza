<?php
// app/Http/Controllers/API/TaskAssignmentController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitTaskWithPictureRequest;
use App\Services\Api\TaskAssignmentForEmployeeService;
use Exception;
use Illuminate\Http\Request;

class TaskAssignmentForEmployeeController extends Controller
{
    protected $taskAssignmentForEmployeeService;

    public function __construct(TaskAssignmentForEmployeeService $taskAssignmentForEmployeeService)
    {
        $this->taskAssignmentForEmployeeService = $taskAssignmentForEmployeeService;
    }

    // Employee views their assigned tasks
    public function getEmployeeTasks(Request $request)
    {
        try {
            // Get tasks assigned to the authenticated employee
            $tasks = $this->taskAssignmentForEmployeeService->getTasksForEmployee($request->user()->id);

            return response()->json(['data' => $tasks], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch tasks: ' . $e->getMessage()], 500);
        }
    }

    // Employee submits a task with a picture
    public function submitTaskWithPicture(SubmitTaskWithPictureRequest $request, $id)
    {
        try {
            // Submit the task with the picture and mark it as completed
            $task = $this->taskAssignmentForEmployeeService->submitTaskWithPicture($id, $request->validated(), $request->user()->id);

            return response()->json(['message' => 'Task submitted successfully', 'data' => $task], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to submit task: ' . $e->getMessage()], 500);
        }
    }
}