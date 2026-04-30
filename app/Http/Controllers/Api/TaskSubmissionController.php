<?php
namespace App\Http\Controllers\API;

use App\Models\TaskAssignment;
use App\Models\CleaningTask;
use App\Models\Store;
use App\Services\TaskAssignmentService;
use App\Http\Requests\CreateTaskAssignmentRequest;
use App\Http\Requests\UpdateTaskAssignmentRequest;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade as PDF;

class TaskAssignmentController extends Controller
{
    protected $taskAssignmentService;

    public function __construct(TaskAssignmentService $taskAssignmentService)
    {
        $this->taskAssignmentService = $taskAssignmentService;
    }

    // Create a new task assignment
    public function assignTask(CreateTaskAssignmentRequest $request)
    {
        try {
            // Validate and create task assignment
            $taskAssignment = TaskAssignment::create($request->validated());

            return response()->json([
                'message' => 'Task assigned successfully', 
                'data' => $taskAssignment
            ], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Get all task assignments for a specific store
    public function getAssignments(Request $request)
    {
        try {
            $storeId = $request->store_id;
            $taskAssignments = TaskAssignment::where('store_id', $storeId)->get();

            return response()->json(['data' => $taskAssignments], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Update a task assignment (e.g., change employee, task or assignment date)
    public function updateTaskAssignment(UpdateTaskAssignmentRequest $request, $id)
    {
        try {
            $taskAssignment = TaskAssignment::findOrFail($id);
            $taskAssignment->update($request->validated());

            return response()->json(['message' => 'Task assignment updated successfully', 'data' => $taskAssignment], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Delete a task assignment
    public function deleteTaskAssignment($store, $id)
{
    try {
        // Resolve store by name
        $resolvedStore = Store::where('name', $store)->first();
        if (!$resolvedStore) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        // Find the task assignment by store_id and task assignment ID
        $taskAssignment = TaskAssignment::where('store_id', $resolvedStore->id)
                                         ->where('id', $id)
                                         ->first();

        if (!$taskAssignment) {
            return response()->json(['message' => 'Task assignment not found for this store'], 404);
        }

        // Delete the task assignment
        $taskAssignment->delete();

        return response()->json(['message' => 'Task assignment deleted successfully'], 200);
    } catch (Exception $e) {
        return response()->json(['error' => 'Failed to delete task assignment: ' . $e->getMessage()], 500);
    }
}

    // Export PDF of task assignments for a store
    public function exportPDF(Request $request)
    {
        try {
            $storeId = $request->store_id;
            $taskAssignments = TaskAssignment::where('store_id', $storeId)->get();
            $pdf = PDF::loadView('pdf.task_assignments', compact('taskAssignments'));

            return $pdf->download('task_assignments_report.pdf');
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}