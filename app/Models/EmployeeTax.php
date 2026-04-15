<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 

class EmployeeTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'employeeId', 'taxesId', 'createdBy', 'editedBy'
    ];

    /**
     * Relationship with the Employee model.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employeeId');
    }

    /**
     * Relationship with the Tax model.
     */
    public function tax()
    {
        return $this->belongsTo(Tax::class, 'taxesId');
    }

    /**
     * Relationship with the User model (for createdBy and editedBy).
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function editedByUser()
    {
        return $this->belongsTo(User::class, 'editedBy');
    }
}