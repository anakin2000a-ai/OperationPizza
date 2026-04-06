<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skill extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'description'];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_skills')
            ->withPivot('rating');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function templateDetails()
    {
        return $this->hasMany(ScheduleTemplateDetail::class);
    }
}