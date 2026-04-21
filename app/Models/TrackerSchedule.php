<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrackerSchedule extends Model
{   
    protected $table = 'tracker_schedule';
    protected $fillable = ['scheduleWeekId'];
    use SoftDeletes;
    public function masterSchedule()
    {
        return $this->belongsTo(MasterSchedule::class, 'scheduleWeekId');
    }

   public function trackerDetails()
    {
        return $this->hasMany(TrackerDetail::class, 'trackerId');
    }
}