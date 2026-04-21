<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrackerDetail extends Model
{
    use SoftDeletes;
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