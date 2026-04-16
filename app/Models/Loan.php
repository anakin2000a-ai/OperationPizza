<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use SoftDeletes;
    protected $fillable = ['loanName','loanAmount', 'taxValue', 'loanAmountWithTax', 'loanType', 'createdBy', 'deletedBy','ReasonForDeletion'];

    public function employeesLoans()
    {
        return $this->hasMany(EmployeeLoan::class);
    }
}