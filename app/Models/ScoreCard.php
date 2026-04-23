<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScoreCard extends Model
{
    use SoftDeletes;
    protected $fillable = ['employeeId', 'scheduleWeekId', 'totalHoursWorked', 'trackerScore', 'finalSalary'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employeeId');
    }

    public function masterSchedule()
    {
        return $this->belongsTo(MasterSchedule::class, 'scheduleWeekId');
    }
    public function trackerSchedule()
    {
        return $this->hasOne(TrackerSchedule::class, 'scheduleWeekId', 'scheduleWeekId');
    }
}