<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sim extends Model
{
    use SoftDeletes;
     protected $table = 'sims';
     protected $fillable = ['simCardInstallment','SimCardType', 'createdBy', 'editedBy'];

    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }
}