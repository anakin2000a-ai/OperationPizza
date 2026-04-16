<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeLoan extends Model
{
    use SoftDeletes;
    protected $table = 'employeesloans';
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