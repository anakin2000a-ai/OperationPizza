<?php

namespace App\Services\Api;

use App\Models\EmployeeSkill;
use App\Models\MasterSchedule;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Collection;

class FiltersService
{
    public function getPublishedFlexible(array $data)
    {
        $query = MasterSchedule::query()
            ->where('published', true);

        // 🟢 حالة: start + end
        if (!empty($data['start_date']) && !empty($data['end_date'])) {

            $query->where(function ($q) use ($data) {
                $q->whereDate('start_date', '<=', $data['end_date'])
                ->whereDate('end_date', '>=', $data['start_date']);
            });
        }

        // 🟡 حالة: start فقط
        elseif (!empty($data['start_date'])) {

            $query->whereDate('end_date', '>=', $data['start_date']);
        }

        // 🔵 حالة: end فقط
        elseif (!empty($data['end_date'])) {

            $query->whereDate('start_date', '<=', $data['end_date']);
        }

        // 🎯 فلتر store
        if (!empty($data['store_id'])) {
            $query->where('store_id', $data['store_id']);
        }

        return $query->with('schedules')->get();
    }
    public function filterSchedulesByEmployee(array $data)
    {
        $query = Schedule::query()
            ->where('schedule_week_id', $data['master_schedule_id']);

        // 🎯 إذا محدد موظف
        if (!empty($data['employee_id'])) {
            $query->where('employee_id', $data['employee_id']);
        }

        return $query->with(['employee', 'skill'])->get();
    }
}