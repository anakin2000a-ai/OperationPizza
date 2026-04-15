<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Sim extends Model
{
    protected $fillable = ['simCardInstallment', 'createdBy', 'editedBy'];

    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }
}