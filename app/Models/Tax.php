<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model
{
    use SoftDeletes;

    protected $fillable = ['taxAmount', 'taxtype', 'createdBy', 'editedBy'];

    public function employeeTaxes()
    {
        return $this->hasMany(EmployeeTax::class);
    }
}