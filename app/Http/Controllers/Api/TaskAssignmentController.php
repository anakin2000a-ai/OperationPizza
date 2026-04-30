<?php
namespace App\Http\Controllers\Api;

use App\Models\TaskAssignment;
use App\Services\Api\TaskAssignmentService;
use App\Http\Requests\CreateTaskAssignmentRequest;
use App\Http\Requests\UpdateTaskAssignmentRequest;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;

class TaskAssignmentController extends Controller
{
    protected $taskAssignmentService;

    public function __construct(TaskAssignmentService $taskAssignmentService)
    {
        $this->taskAssignmentService = $taskAssignmentService;
    }

    // Create a new task assignment
    public function createTaskAssignment(CreateTaskAssignmentRequest $request, $store)
    {
        try {
            // Resolve store by name
            $resolvedStore = Store::where('store', $store)->first();
            if (!$resolvedStore) {
                return response()->json(['message' => 'Store not found'], 404);
            }

            // Attach the store ID to the request data
            $data = $request->validated();
            $data['store_id'] = $resolvedStore->id; // Make sure store_id is being set

            // Create the task assignment
            $taskAssignment = $this->taskAssignmentService->createTaskAssignment($data);

            return response()->json([
                'message' => 'Task assigned successfully',
                'data' => $taskAssignment
            ], 201);

        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to assign task: ' . $e->getMessage()], 500);
        }
    }

    public function getAssignments(Request $request, $store)
    {
        try {
            // Resolve store by name
            $resolvedStore = Store::where('store', $store)->first();  // Assuming store name is the correct column
            if (!$resolvedStore) {
                return response()->json(['message' => 'Store not found'], 404);
            }

            // Validate and get the date from the request (date format: 'Y-m-d')
            $date = $request->input('date');
            
            // If date is provided, filter by assigned_at
            if ($date) {
                // Validate the date format
                $request->validate([
                    'date' => 'required|date_format:Y-m-d',
                ]);

                // Filter task assignments by store_id and assigned_at date
                $taskAssignments = TaskAssignment::where('store_id', $resolvedStore->id)
                                                ->whereDate('assigned_at', $date)  // Filter by assigned_at date
                                                ->get();
            } else {
                // If no date is provided, return all task assignments for the store
                $taskAssignments = TaskAssignment::where('store_id', $resolvedStore->id)->get();
            }

            // Return task assignments in response
            return response()->json(['data' => $taskAssignments], 200);
            
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch task assignments: ' . $e->getMessage()], 500);
        }
    }
    // Update an existing task assignment
    public function updateTaskAssignment(UpdateTaskAssignmentRequest $request, $id)
    {
        try {
            $taskAssignment = TaskAssignment::findOrFail($id);
            $updatedTaskAssignment = $this->taskAssignmentService->updateTaskAssignment($taskAssignment, $request->validated());

            return response()->json([
                'message' => 'Task assignment updated successfully',
                'data' => $updatedTaskAssignment
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update task assignment: ' . $e->getMessage()], 500);
        }
    }

    // Delete a task assignment
   public function deleteTaskAssignment($store, $id)
    {
        try {
            // Resolve store by name
            $resolvedStore = Store::where('store', $store)->first();
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
            $this->taskAssignmentService->deleteTaskAssignment($taskAssignment);

            return response()->json(['message' => 'Task assignment deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete task assignment: ' . $e->getMessage()], 500);
        }
    }

    // Export task assignments for a store to PDF
 
    public function exportTaskAssignmentsToPDF(Request $request, $store)
{
    try {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->input('date');

        $resolvedStore = Store::where('store', $store)->first();

        if (!$resolvedStore) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $taskAssignments = TaskAssignment::where('store_id', $resolvedStore->id)
            ->whereDate('assigned_at', $date)
            ->get();

        if ($taskAssignments->isEmpty()) {
            return response()->json(['message' => 'No task assignments found for this store on this date'], 404);
        }

        \Log::info('Task Assignments for ' . $store . ' on ' . $date . ': ', $taskAssignments->toArray());

        // ✅ FIX: Use an array instead of compact()
        $pdf = PDF::loadView('pdf.task_assignments', [
            'taskAssignments' => $taskAssignments,
            'storeName' => $resolvedStore->store,
        ]);

        \Log::info('PDF generated successfully for store: ' . $store . ' on ' . $date);

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="task_assignments_report_' . $date . '.pdf"');
    } catch (Exception $e) {
        \Log::error('Error generating PDF for store: ' . $store . ' Error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to generate PDF file: ' . $e->getMessage()], 500);
    }
}
    // public function exportTaskAssignmentsToPDF(Request $request, $store)
    // {
    //     try {
    //         // Validate the input date in the request body
    //         $request->validate([
    //             'date' => 'required|date', // Ensure the date is in a valid format
    //         ]);

    //         // Get the date from the request body
    //         $date = $request->input('date');

    //         // Resolve store by name
    //         $resolvedStore = Store::where('store', $store)->first();

    //         if (!$resolvedStore) {
    //             return response()->json(['message' => 'Store not found'], 404);
    //         }

    //         // Retrieve task assignments for the resolved store on the specified date
    //         $taskAssignments = TaskAssignment::where('store_id', $resolvedStore->id)
    //             ->whereDate('assigned_at', $date) // Filter by the specific date
    //             ->get();

    //         if ($taskAssignments->isEmpty()) {
    //             return response()->json(['message' => 'No task assignments found for this store on this date'], 404);
    //         }

    //         // Log task assignments to confirm data is being passed
    //         \Log::info('Task Assignments for ' . $store . ' on ' . $date . ': ', $taskAssignments->toArray());

    //         // Generate the PDF from the Blade view
    //         $pdf = PDF::loadView('pdf.task_assignments', compact('taskAssignments', 'resolvedStore->store'));

    //         // Log if the PDF was generated successfully
    //         \Log::info('PDF generated successfully for store: ' . $store . ' on ' . $date);

    //         // Return the generated PDF file for download
    //         return response($pdf->output(), 200)
    //             ->header('Content-Type', 'application/pdf')
    //             ->header('Content-Disposition', 'attachment; filename="task_assignments_report_' . $date . '.pdf"');
    //     } catch (Exception $e) {
    //         \Log::error('Error generating PDF for store: ' . $store . ' Error: ' . $e->getMessage());
    //         return response()->json(['error' => 'Failed to generate PDF file: ' . $e->getMessage()], 500);
    //     }
    // }
}