<?php

namespace App\Services\Api;

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\ShiftRequirementDay;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class ScheduleAIService
{
    private function getCandidates(int $storeId, $requirement, Carbon $date): Collection
    {
        // ✅ preload schedules (day)
        $dailySchedules = Schedule::where('date', $date->toDateString())
            ->get()
            ->groupBy('employee_id');

        // ✅ preload weekly schedules (fairness)
        $weeklySchedules = Schedule::whereBetween('date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->get()->groupBy('employee_id');

        $employees = Employee::where('store_id', $storeId)
            ->where('status', 'hired')
            ->whereHas('skills', function ($q) use ($requirement) {
                $q->where('skill_id', $requirement->skill_id);
            })
            ->with([
                'skills',
                'availability.times'
            ])
            ->get();

        return $employees->filter(function ($employee) use ($requirement, $date, $dailySchedules) {

            $employeeSchedules = $dailySchedules[$employee->id] ?? collect();

            // ✅ overlap check
            $hasOverlap = $employeeSchedules->contains(function ($schedule) use ($requirement) {
                return $schedule->start_time < $requirement->end_time &&
                    $schedule->end_time > $requirement->start_time;
            });

            if ($hasOverlap) return false;

            // ✅ max hours (10h)
            $totalHours = $employeeSchedules->sum(function ($s) {
                return (strtotime($s->end_time) - strtotime($s->start_time)) / 3600;
            });

            $shiftHours = (strtotime($requirement->end_time) - strtotime($requirement->start_time)) / 3600;

            if (($totalHours + $shiftHours) > 10) return false;

            // ✅ availability
            if (!$this->isAvailable($employee, $requirement, $date)) return false;

            return true;

        })->map(function ($employee) use ($requirement, $weeklySchedules) {

            $skillScore = $employee->skills
                ->where('id', $requirement->skill_id)
                ->first()?->pivot->rating ?? 0;

            $availabilityScore = 100;
            $performanceScore = 50;

            // ✅ fairness
            $weeklyLoad = count($weeklySchedules[$employee->id] ?? []);

            $employee->score =
                ($skillScore * 0.5) +
                ($performanceScore * 0.3) +
                ($availabilityScore * 0.2)
                - ($weeklyLoad * 3);

            return $employee;
        });
    }
   
    public function generate(int $storeId, array $data): array
    {
        return DB::transaction(function () use ($storeId, $data) {

            $start = Carbon::parse($data['start_date']);
            $end = Carbon::parse($data['end_date']);

            $results = [];

            while ($start <= $end) {

                $dayName = strtolower($start->format('l'));

                // ✅ NEW: get day with times
                $day = ShiftRequirementDay::with('times')
                    ->where('store_id', $storeId)
                    ->where('day_of_week', $dayName)
                    ->first();

                // ✅ no config for this day → skip
                if (!$day || $day->times->isEmpty()) {
                    $start->addDay();
                    continue;
                }

                foreach ($day->times as $requirement) {

                    $candidates = $this->getCandidates($storeId, $requirement, $start);

                    if ($candidates->count() < $requirement->required_employees) {
                        throw new Exception(
                            "Not enough employees for {$dayName} {$requirement->start_time}-{$requirement->end_time}"
                        );
                    }

                    $selected = $candidates
                        ->sortByDesc('score')
                        ->take($requirement->required_employees);

                    foreach ($selected as $employee) {

                        Schedule::create([
                            'employee_id' => $employee->id,
                            'schedule_week_id' => $data['schedule_week_id'],
                            'date' => $start->toDateString(),
                            'start_time' => $requirement->start_time,
                            'end_time' => $requirement->end_time,
                            'skill_id' => $requirement->skill_id,
                        ]);

                        $results[] = [
                            'employee_id' => $employee->id,
                            'date' => $start->toDateString(),
                            'shift' => $requirement->start_time . '-' . $requirement->end_time,
                            'score' => $employee->score
                        ];
                    }
                }

                $start->addDay();
            }

            return $results;
        });
    }

    private function isAvailable($employee, $requirement, Carbon $date): bool
    {
        $dayName = strtolower($date->format('l'));

        foreach ($employee->availability as $availability) {

            if ($availability->day_of_week !== $dayName) {
                continue;
            }

            foreach ($availability->times as $time) {

                if (
                    $time->from <= $requirement->start_time &&
                    $time->to >= $requirement->end_time
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}