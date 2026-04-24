<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftRequirementTime extends Model
{
    protected $fillable = [
        'shift_requirement_day_id',
        'start_time',
        'end_time',
        'skill_id',
        'required_employees',
    ];

    public function day()
    {
        return $this->belongsTo(ShiftRequirementDay::class, 'shift_requirement_day_id');
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }
}