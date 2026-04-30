<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CleaningTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'frequency', 
        'times_per_frequency'
    ];

    // Relationship with Task Assignments
    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignment::class);
    }
}