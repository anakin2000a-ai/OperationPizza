<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $fillable = ['taxAmount', 'taxtype', 'createdBy', 'editedBy'];

    public function employeeTaxes()
    {
        return $this->hasMany(EmployeeTax::class);
    }
}