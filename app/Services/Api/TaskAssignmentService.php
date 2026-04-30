<?php
namespace App\Services\Api;

use App\Models\TaskAssignment;
use Exception;

class TaskAssignmentService
{
    // Create a task assignment for an employee
    public function createTaskAssignment(array $data)
    {
        try {
            $taskAssignment = TaskAssignment::create($data);
            $taskAssignment->status = 'assigned'; // Initially marked as assigned
            $taskAssignment->save();

            return $taskAssignment;
        } catch (Exception $e) {
            throw new Exception('Error creating task assignment: ' . $e->getMessage());
        }
    }

    // Update an existing task assignment
    public function updateTaskAssignment(TaskAssignment $taskAssignment, array $data)
    {
        try {
            $taskAssignment->update($data);
            return $taskAssignment;
        } catch (Exception $e) {
            throw new Exception('Error updating task assignment: ' . $e->getMessage());
        }
    }

    // Delete a task assignment
    public function deleteTaskAssignment(TaskAssignment $taskAssignment)
    {
        try {
            $taskAssignment->delete();
        } catch (Exception $e) {
            throw new Exception('Error deleting task assignment: ' . $e->getMessage());
        }
    }
    
    
}