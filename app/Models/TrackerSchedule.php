<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackerSchedule extends Model
{
    protected $fillable = ['scheduleWeekId'];

    public function masterSchedule()
    {
        return $this->belongsTo(MasterSchedule::class, 'scheduleWeekId');
    }

    public function trackerDetails()
    {
        return $this->hasMany(TrackerDetail::class);
    }
}