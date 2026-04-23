<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $store1 = Store::where('store', 1)->first();
        $store2 = Store::where('store', 2)->first();
        $store3 = Store::where('store', 3)->first();
          DB::table('employees')->insert([
            [
                'id' => 1,
                'store_id' => 1,
                'FirstName' => 'John',
                'LastName' => 'Doe',
                'HaveCar' => 1,
                'phone' => '123456789',
                'email' => 'john@example.com',
                'hire_date' => '2026-04-21',
                 'status' => 'resignation',
                'Nationality' => 'foreigner',
                'position' => 'CrowMember',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'store_id' => 2,
                'FirstName' => 'Ahmad',
                'LastName' => 'Ali',
                'HaveCar' => 0,
                'phone' => '987654321',
                'email' => 'ahmad@example.com',
                'hire_date' => '2026-04-21',
                'status' => 'resignation',
                'Nationality' => 'foreigner',
                'position' => 'CrowMember',
                'created_at' => now(),
                'updated_at' => now(),
            ],

     
    
 
        ]);
    }
}