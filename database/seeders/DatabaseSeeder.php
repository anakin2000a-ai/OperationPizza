<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
 
 
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // StoreSeeder::class,
            // EmployeeSeeder::class,
            // UserSeeder::class,

            AvailabilitySeeder::class,
            AvailabilityTimeSeeder::class,
            // SchedulesTableSeeder::class,
            // TrackerDetailsTableSeeder::class,
        ]);
    }
}