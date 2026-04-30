<?php
namespace App\Http\Controllers\API;

use App\Models\CleaningTask;
use App\Services\Api\CleaningTaskService;
use App\Http\Requests\CreateCleaningTaskRequest;
use App\Http\Requests\UpdateCleaningTaskRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class CleaningTaskController extends Controller
{
    protected $cleaningTaskService;

    public function __construct(CleaningTaskService $cleaningTaskService)
    {
        $this->cleaningTaskService = $cleaningTaskService;
    }

    // Create a new cleaning task
    public function create(CreateCleaningTaskRequest $request)
    {
        try {
            $task = $this->cleaningTaskService->createCleaningTask($request->validated());
            return response()->json(['message' => 'Task created successfully', 'data' => $task], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Get all cleaning tasks
    public function index()
    {
        try {
            $tasks = $this->cleaningTaskService->getAllCleaningTasks();
            return response()->json(['data' => $tasks], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Get a specific cleaning task by ID
    public function show($id)
    {
        try {
            $task = $this->cleaningTaskService->getCleaningTaskById($id);
            return response()->json(['data' => $task], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    // Update a cleaning task
    public function update(UpdateCleaningTaskRequest $request, CleaningTask $cleaning_task)
    {
        try {
            $task = $this->cleaningTaskService->getCleaningTaskById($cleaning_task->id);
            $updatedTask = $this->cleaningTaskService->updateCleaningTask($task, $request->validated());
            return response()->json(['message' => 'Task updated successfully', 'data' => $updatedTask], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Delete a cleaning task
    public function destroy($id)
    {
        try {
            $task = $this->cleaningTaskService->getCleaningTaskById($id);
            $this->cleaningTaskService->deleteCleaningTask($task);
            return response()->json(['message' => 'Task deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}