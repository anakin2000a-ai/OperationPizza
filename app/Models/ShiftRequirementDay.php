<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftRequirementDay extends Model
{
    protected $fillable = [
        'store_id',
        'day_of_week',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function times()
    {
        return $this->hasMany(ShiftRequirementTime::class, 'shift_requirement_day_id');
    }
}