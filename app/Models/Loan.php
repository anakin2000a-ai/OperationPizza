<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = ['loanAmount', 'taxValue', 'loanAmountWithTax', 'loanType', 'createdBy', 'editedBy'];

    public function employeesLoans()
    {
        return $this->hasMany(EmployeeLoan::class);
    }
}