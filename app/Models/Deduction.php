<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    protected $fillable = ['employeeId', 'ApartmentId', 'SimId', 'createdBy', 'editedBy'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employeeId');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'ApartmentId');
    }

    public function sim()
    {
        return $this->belongsTo(Sim::class, 'SimId');
    }
}