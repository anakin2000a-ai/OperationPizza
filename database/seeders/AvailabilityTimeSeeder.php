<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AvailabilityTimeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('availability_times')->insert([
            ['id' => 1,  'from' => '10:00:00', 'to' => '23:58:00', 'availability_id' => 1,  'created_at' => null, 'updated_at' => null],
            ['id' => 2,  'from' => '10:00:00', 'to' => '23:00:00', 'availability_id' => 2,  'created_at' => null, 'updated_at' => null],
            ['id' => 3,  'from' => '10:00:00', 'to' => '23:58:00', 'availability_id' => 3,  'created_at' => null, 'updated_at' => null],
            ['id' => 4,  'from' => '10:00:00', 'to' => '23:00:00', 'availability_id' => 4,  'created_at' => null, 'updated_at' => null],
            ['id' => 5,  'from' => '10:00:00', 'to' => '23:58:00', 'availability_id' => 5,  'created_at' => null, 'updated_at' => null],
            ['id' => 6,  'from' => '10:00:00', 'to' => '23:00:00', 'availability_id' => 6,  'created_at' => null, 'updated_at' => null],
            ['id' => 7,  'from' => '10:00:00', 'to' => '23:58:00', 'availability_id' => 7,  'created_at' => null, 'updated_at' => null],

            ['id' => 12, 'from' => '10:00:00', 'to' => '23:58:00', 'availability_id' => 8,  'created_at' => null, 'updated_at' => null],
            ['id' => 13, 'from' => '10:00:00', 'to' => '23:00:00', 'availability_id' => 9,  'created_at' => null, 'updated_at' => null],
            ['id' => 14, 'from' => '10:00:00', 'to' => '23:58:00', 'availability_id' => 10, 'created_at' => null, 'updated_at' => null],
            ['id' => 15, 'from' => '10:00:00', 'to' => '23:00:00', 'availability_id' => 11, 'created_at' => null, 'updated_at' => null],
            ['id' => 16, 'from' => '10:00:00', 'to' => '23:58:00', 'availability_id' => 12, 'created_at' => null, 'updated_at' => null],
            ['id' => 17, 'from' => '10:00:00', 'to' => '23:00:00', 'availability_id' => 13, 'created_at' => null, 'updated_at' => null],
        ]);
    }
}