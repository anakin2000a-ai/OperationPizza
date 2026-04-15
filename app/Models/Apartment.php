<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    protected $fillable = ['ApartmentRent', 'createdBy', 'editedBy'];

    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }
}