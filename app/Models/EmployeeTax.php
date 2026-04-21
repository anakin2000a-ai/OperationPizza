<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTax extends Model
{
    use HasFactory ;

    // ✅ MUST match your migration table name
    protected $table = 'employeetaxes';

    protected $fillable = [
        'employeeId',
        'taxesId',
        'createdBy',
        'editedBy'
    ];

    /**
     * Employee relationship
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employeeId');
    }

    /**
     * Tax relationship
     */
    public function tax()
    {
        return $this->belongsTo(Tax::class, 'taxesId');
    }

    /**
     * Created By User
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    /**
     * Edited By User
     */
    public function editedByUser()
    {
        return $this->belongsTo(User::class, 'editedBy');
    }
}