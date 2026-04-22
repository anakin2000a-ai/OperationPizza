<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SchedulesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('schedules')->insert([
            [
                'employee_id' => 1,
                'schedule_week_id' => 1,
                'date' => '2026-04-20',
                'start_time' => '11:00:00',
                'end_time' => '13:00:00',
                'actual_start_time' => '10:00:00',
                'actual_end_time' => '13:00:00',
                'skill_id' => 1,
                'edited_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ],
            [
                'employee_id' => 2,
                'schedule_week_id' => 1,
                'date' => '2026-04-14',
                'start_time' => '11:00:00',
                'end_time' => '13:00:00',
                'actual_start_time' => '10:00:00',
                'actual_end_time' => '13:00:00',
                'skill_id' => 1,
                'edited_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ],
            [
                'employee_id' => 1,
                'schedule_week_id' => 1,
                'date' => '2026-04-15',
                'start_time' => '11:00:00',
                'end_time' => '13:00:00',
                'actual_start_time' => '10:00:00',
                'actual_end_time' => '13:00:00',
                'skill_id' => 1,
                'edited_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ],
            [
                'employee_id' => 2,
                'schedule_week_id' => 1,
                'date' => '2026-04-15',
                'start_time' => '11:00:00',
                'end_time' => '13:00:00',
                'actual_start_time' => '10:00:00',
                'actual_end_time' => '13:00:00',
                'skill_id' => 1,
                'edited_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ],
            [
                'employee_id' => 1,
                'schedule_week_id' => 1,
                'date' => '2026-04-16',
                'start_time' => '11:00:00',
                'end_time' => '13:00:00',
                'actual_start_time' => '10:00:00',
                'actual_end_time' => '13:00:00',
                'skill_id' => 1,
                'edited_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ],
            [
                'employee_id' => 2,
                'schedule_week_id' => 1,
                'date' => '2026-04-16',
                'start_time' => '11:00:00',
                'end_time' => '13:00:00',
                'actual_start_time' => '10:00:00',
                'actual_end_time' => '13:00:00',
                'skill_id' => 1,
                'edited_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ],
            [
                'employee_id' => 1,
                'schedule_week_id' => 1,
                'date' => '2026-04-17',
                'start_time' => '11:00:00',
                'end_time' => '13:00:00',
                'actual_start_time' => '10:00:00',
                'actual_end_time' => '23:00:00',
                'skill_id' => 1,
                'edited_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ],
            [
                'employee_id' => 1,
                'schedule_week_id' => 1,
                'date' => '2026-04-18',
                'start_time' => '11:00:00',
                'end_time' => '13:00:00',
                'actual_start_time' => '10:00:00',
                'actual_end_time' => '23:00:00',
                'skill_id' => 1,
                'edited_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ],
            [
                'employee_id' => 1,
                'schedule_week_id' => 1,
                'date' => '2026-04-19',
                'start_time' => '11:00:00',
                'end_time' => '13:00:00',
                'actual_start_time' => '10:00:00',
                'actual_end_time' => '23:00:00',
                'skill_id' => 1,
                'edited_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null
            ]
        ]);

}
}