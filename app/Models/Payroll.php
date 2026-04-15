<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'employeeId', 'scorecardId', 'loanAmount', 'deductions', 'taxes', 
        'finalSalary', 'approvedByThirdShiftStoreManager', 'approvedBySeniorManager','approvedBySeniorManagerId', 'approvedByThirdShiftStoreManagerId',
        'paymentDate', 'paymentStatus'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employeeId');
    }

    public function scoreCard()
    {
        return $this->belongsTo(ScoreCard::class, 'scorecardId');
    }
}