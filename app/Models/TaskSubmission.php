<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_assignment_id',
        'image_path',
    ];

    // Relationship with Task Assignment
    public function taskAssignment()
    {
        return $this->belongsTo(TaskAssignment::class);
    }
}