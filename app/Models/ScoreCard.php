<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ScoreCard extends Model
{
    protected $fillable = ['employeeId', 'scheduleWeekId', 'totalHoursWorked', 'trackerScore', 'finalSalary'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employeeId');
    }

    public function masterSchedule()
    {
        return $this->belongsTo(MasterSchedule::class, 'scheduleWeekId');
    }
}