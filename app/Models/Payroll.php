<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    protected $table = 'payroll';
    use SoftDeletes;
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