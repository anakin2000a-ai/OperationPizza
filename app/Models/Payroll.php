<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $table = 'payroll';

    protected $fillable = [
        'employeeId', 'scorecardId', 'loanAmount', 'deductions', 
        'finalSalary', 'approvedByThirdShiftStoreManager', 'approvedBySeniorManager','approvedBySeniorManagerId', 'approvedByThirdShiftStoreManagerId',
        'paymentDate', 'paymentStatus'
    ];

 

    public function scoreCard()
    {
        return $this->belongsTo(ScoreCard::class, 'scorecardId');
    }
}