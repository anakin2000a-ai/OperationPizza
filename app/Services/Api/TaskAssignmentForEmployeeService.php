<?php
// app/Services/Api/TaskAssignmentForEmployeeService.php
namespace App\Services\Api;

use App\Models\TaskAssignment;
use Exception;
use Illuminate\Support\Facades\Storage;

class TaskAssignmentForEmployeeService
{
    // Mark a task as completed by storing the image and changing its status
    public function submitTaskWithPicture($taskId, $validatedData, $employeeId)
    {
        try {
            // Find the task assignment by ID
            $taskAssignment = TaskAssignment::findOrFail($taskId);

            // Ensure the authenticated employee is the one assigned to this task
            if ($taskAssignment->employee_id !== $employeeId) {
                throw new Exception('Unauthorized: You are not assigned to this task');
            }

            // Store the image in the 'task_images' directory
            $imagePath = $validatedData['image']->store('task_images', 'public');

            // Update the task assignment with the image path and status
            $taskAssignment->image_path = $imagePath;
            $taskAssignment->status = 'completed'; // Mark the task as completed
            $taskAssignment->save();

            return $taskAssignment;
        } catch (Exception $e) {
            throw new Exception('Error submitting task: ' . $e->getMessage());
        }
    }
    public function getTasksForEmployee($employeeId)
    {
        try {
            // Retrieve tasks assigned to the employee
            return TaskAssignment::where('employee_id', $employeeId)->get();
        } catch (Exception $e) {
            throw new Exception('Error fetching tasks: ' . $e->getMessage());
        }
}