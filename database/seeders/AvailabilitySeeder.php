<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('availabilities')->insert([
            ['id' => 1,  'employee_id' => 1, 'day_of_week' => 'sunday',    'created_at' => null, 'updated_at' => null],
            ['id' => 2,  'employee_id' => 1, 'day_of_week' => 'monday',    'created_at' => null, 'updated_at' => null],
            ['id' => 3,  'employee_id' => 1, 'day_of_week' => 'tuesday',   'created_at' => null, 'updated_at' => null],
            ['id' => 4,  'employee_id' => 1, 'day_of_week' => 'wednesday', 'created_at' => null, 'updated_at' => null],
            ['id' => 5,  'employee_id' => 1, 'day_of_week' => 'thursday',  'created_at' => null, 'updated_at' => null],
            ['id' => 6,  'employee_id' => 1, 'day_of_week' => 'friday',    'created_at' => null, 'updated_at' => null],
            ['id' => 7,  'employee_id' => 1, 'day_of_week' => 'saturday',  'created_at' => null, 'updated_at' => null],

            ['id' => 8,  'employee_id' => 2, 'day_of_week' => 'sunday',    'created_at' => null, 'updated_at' => null],
            ['id' => 9,  'employee_id' => 2, 'day_of_week' => 'monday',    'created_at' => null, 'updated_at' => null],
            ['id' => 10, 'employee_id' => 2, 'day_of_week' => 'tuesday',   'created_at' => null, 'updated_at' => null],
            ['id' => 11, 'employee_id' => 2, 'day_of_week' => 'wednesday', 'created_at' => null, 'updated_at' => null],
            ['id' => 12, 'employee_id' => 2, 'day_of_week' => 'thursday',  'created_at' => null, 'updated_at' => null],
            ['id' => 13, 'employee_id' => 2, 'day_of_week' => 'friday',    'created_at' => null, 'updated_at' => null],
            ['id' => 14, 'employee_id' => 2, 'day_of_week' => 'saturday',  'created_at' => null, 'updated_at' => null],
        ]);
    }
}