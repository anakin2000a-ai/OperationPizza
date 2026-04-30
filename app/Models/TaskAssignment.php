<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'cleaning_task_id', 'store_id', 'assigned_at', 'status'];

    // Relationship with Cleaning Task
    public function cleaningTask()
    {
        return $this->belongsTo(CleaningTask::class);
    }

    // Relationship with Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Relationship with Store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Relationship with Task Submissions
    public function taskSubmissions()
    {
        return $this->hasMany(TaskSubmission::class);
    }
}