<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackerDetail extends Model
{
    protected $fillable = [
        'trackerId', 'employeeId', 'respect', 'uniforms', 'commitmentToAttend', 
        'performance', 'finalResult', 'date'
    ];

    public function trackerSchedule()
    {
        return $this->belongsTo(TrackerSchedule::class, 'trackerId');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employeeId');
    }
}