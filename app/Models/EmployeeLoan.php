<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EmployeeLoan extends Model
{
    protected $fillable = ['employeeId', 'loansId', 'loanStatus', 'loanStartDate', 'loanRentAmount', 'createdBy', 'editedBy'];

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loansId');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employeeId');
    }
}