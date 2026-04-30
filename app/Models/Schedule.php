<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'employee_id','schedule_week_id','date',
        'start_time','end_time',
        'actual_start_time','actual_end_time',
        'skill_id','edited_by'
    ];
     // Add this property
    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'actual_start_time' => 'datetime:H:i',
        'actual_end_time' => 'datetime:H:i',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function masterSchedule()
    {
        return $this->belongsTo(MasterSchedule::class, 'schedule_week_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}