<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Apartment extends Model
{
    protected $fillable = ['ApartmentRent','Location', 'createdBy', 'editedBy'];
    use SoftDeletes;

    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }
}