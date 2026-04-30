<?php
namespace App\Services\Api;

use App\Models\CleaningTask;
use Exception;

class CleaningTaskService
{
    // Create a new cleaning task
    public function createCleaningTask(array $data)
    {
        try {
            return CleaningTask::create($data);
        } catch (Exception $e) {
            throw new Exception('Error creating cleaning task: ' . $e->getMessage());
        }
    }

    // Update an existing cleaning task
    public function updateCleaningTask(CleaningTask $task, array $data)
    {
        try {
            $task->update($data);
            return $task;
        } catch (Exception $e) {
            throw new Exception('Error updating cleaning task: ' . $e->getMessage());
        }
    }

    // Delete a cleaning task
    public function deleteCleaningTask(CleaningTask $task)
    {
        try {
            $task->delete();
        } catch (Exception $e) {
            throw new Exception('Error deleting cleaning task: ' . $e->getMessage());
        }
    }

    // Get all cleaning tasks
    public function getAllCleaningTasks()
    {
        try {
            return CleaningTask::all();
        } catch (Exception $e) {
            throw new Exception('Error fetching cleaning tasks: ' . $e->getMessage());
        }
    }

    // Get a specific cleaning task
    public function getCleaningTaskById($id)
    {
        try {
            return CleaningTask::findOrFail($id);
        } catch (Exception $e) {
            throw new Exception('Error finding cleaning task: ' . $e->getMessage());
        }
    }
}
