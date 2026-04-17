<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'id','store_id','name','phone','email','hire_date','status','FirstName','LastName','HaveCar'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'employee_skills')
            ->withPivot('rating');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function daysOff()
    {
        return $this->hasMany(DayOff::class);
    }

    public function availability()
    {
        return $this->hasMany(Availability::class);
    }
    public function taxes()
    {
        return $this->hasMany(\App\Models\EmployeeTax::class, 'employeeId');
    }

    public function deductions()
    {
        return $this->hasMany(\App\Models\Deduction::class, 'employeeId');
    }
} 