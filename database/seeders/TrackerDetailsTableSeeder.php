<?php

namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrackerDetailsTableSeeder extends Seeder
{
    public function run(): void
    {
     DB::table('tracker_details')->insert([
            [
                'trackerId' => 1,
                'employeeId' => 1,
                'respect' => 1,
                'uniforms' => 1,
                'commitmentToAttend' => 1,
                'performance' => 0,
                'finalResult' => 75,
                'date' => '2026-04-20',
                'moneyOwed' => 0,
                'ReasonForMoneyOwed' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ],
            [
                'trackerId' => 1,
                'employeeId' => 2,
                'respect' => 1,
                'uniforms' => 1,
                'commitmentToAttend' => 1,
                'performance' => 0,
                'finalResult' => 75,
                'date' => '2026-04-14',
                'moneyOwed' => 0,
                'ReasonForMoneyOwed' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ],
            [
                'trackerId' => 1,
                'employeeId' => 1,
                'respect' => 1,
                'uniforms' => 1,
                'commitmentToAttend' => 1,
                'performance' => 0,
                'finalResult' => 75,
                'date' => '2026-04-15',
                'moneyOwed' => 0,
                'ReasonForMoneyOwed' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ],
            [
                'trackerId' => 1,
                'employeeId' => 2,
                'respect' => 1,
                'uniforms' => 1,
                'commitmentToAttend' => 1,
                'performance' => 0,
                'finalResult' => 75,
                'date' => '2026-04-15',
                'moneyOwed' => 0,
                'ReasonForMoneyOwed' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ],
            [
                'trackerId' => 1,
                'employeeId' => 1,
                'respect' => 1,
                'uniforms' => 1,
                'commitmentToAttend' => 1,
                'performance' => 0,
                'finalResult' => 75,
                'date' => '2026-04-16',
                'moneyOwed' => 0,
                'ReasonForMoneyOwed' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ],
            [
                'trackerId' => 1,
                'employeeId' => 2,
                'respect' => 1,
                'uniforms' => 1,
                'commitmentToAttend' => 1,
                'performance' => 0,
                'finalResult' => 75,
                'date' => '2026-04-16',
                'moneyOwed' => 0,
                'ReasonForMoneyOwed' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ],
            [
                'trackerId' => 1,
                'employeeId' => 1,
                'respect' => 1,
                'uniforms' => 1,
                'commitmentToAttend' => 1,
                'performance' => 0,
                'finalResult' => 75,
                'date' => '2026-04-17',
                'moneyOwed' => 0,
                'ReasonForMoneyOwed' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ],
            [
                'trackerId' => 1,
                'employeeId' => 1,
                'respect' => 1,
                'uniforms' => 1,
                'commitmentToAttend' => 1,
                'performance' => 0,
                'finalResult' => 75,
                'date' => '2026-04-18',
                'moneyOwed' => 0,
                'ReasonForMoneyOwed' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ],
            [
                'trackerId' => 1,
                'employeeId' => 1,
                'respect' => 1,
                'uniforms' => 1,
                'commitmentToAttend' => 1,
                'performance' => 0,
                'finalResult' => 75,
                'date' => '2026-04-19',
                'moneyOwed' => 0,
                'ReasonForMoneyOwed' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ]
        ]);

    }
}