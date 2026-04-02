<?php

namespace App\Services\Api;

 
use App\Models\Employee;

class getEmployee
{
    public function getEmployeesByStore(int $storeId)
    {
        return Employee::where('store_id', $storeId)->get();
    }
}