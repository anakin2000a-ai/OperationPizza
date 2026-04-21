<?php

namespace App\Services\Api;

use App\Models\MasterSchedule;
use App\Models\Schedule;
use App\Models\ScoreCard;
use App\Models\Employee;
use App\Models\TrackerDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScoreCardService
{
    public function create(int $scheduleWeekId, int $storeId): array
    {
        return DB::transaction(function () use ($scheduleWeekId, $storeId) {

           $master = MasterSchedule::where('id', $scheduleWeekId)
            ->where('store_id', $storeId)
            ->firstOrFail();

            $employees = Schedule::where('schedule_week_id', $scheduleWeekId)
                ->whereNotNull('employee_id')
                ->pluck('employee_id')
                ->unique();

            $created = [];
            $skipped = [];

            foreach ($employees as $employeeId) {

                // Skip if exists
                $exists = ScoreCard::where('employeeId', $employeeId)
                    ->where('scheduleWeekId', $scheduleWeekId)
                    ->exists();

                if ($exists) {
                    $skipped[] = $employeeId;
                    continue;
                }

                $employee = Employee::findOrFail($employeeId);

                $schedules = Schedule::where('employee_id', $employeeId)
                    ->where('schedule_week_id', $scheduleWeekId)
                    ->get();

                // Validate actual times
                foreach ($schedules as $schedule) {
                    if (!$schedule->actual_start_time || !$schedule->actual_end_time) {
                        throw new \Exception("Employee {$employeeId} has missing actual times on {$schedule->date}");
                    }
                }

                // Validate tracker
                $dates = $schedules->pluck('date')->unique();

                $tracker = TrackerDetail::where('employeeId', $employeeId)
                    ->whereIn('date', $dates)
                    ->get();

                $trackerDates = $tracker->pluck('date')->unique();

                if ($trackerDates->count() !== $dates->count()) {
                    throw new \Exception("Employee {$employeeId} missing tracker details");
                }

                // Calculate hours
                $totalHours = $this->calculateHours($schedules);

                // Tracker average
                $trackerScore = round($tracker->avg('finalResult'), 2);

                // 🔥 FIXED PART STARTS HERE

                $taxAmount = 0;
                $taxType = null;

                if ($employee->Nationality === 'Foreigner') {
                    $tax = $this->getEmployeeTax($employee);
                    $taxType = $tax->taxtype;
                    $taxAmount = $tax->taxAmount;
                }

                // Resolve rate
                $rate = $this->resolveRate($employee, $taxType);

                // Calculate salary
                $result = $this->calculateSalary(
                    $totalHours,
                    $rate,
                    $trackerScore,
                    $taxAmount
                );
                $salary = $result['salary'];
                $effectiveHours = $result['effectiveHours'];
                $rateWithBonus = $result['rateWithBonus'];

                // 🔥 FIXED PART ENDS HERE

                $score = ScoreCard::create([
                    'employeeId' => $employeeId,
                    'scheduleWeekId' => $scheduleWeekId,
                    'totalHoursWorked' => $totalHours,
                    'trackerScore' => $trackerScore,
                    'finalSalary' => $salary,
                    'ScoreCardStatus' => 'pending',
                ]);

                $created[] = [
                'employeeId' => $employeeId,
                'totalHours' => $totalHours,
                'effectiveHours' => $effectiveHours,
                'trackerScore' => $trackerScore,
                'rate' => $rate,
                'rateWithBonus' => $rateWithBonus,

                'taxAmount' => $taxAmount,
                'finalSalary' => $salary,
                 ];
            }

            return [
                'created' => $created,
                'skipped' => $skipped,
            ];
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {

            $score = ScoreCard::findOrFail($id);

            if ($score->ScoreCardStatus === 'paid') {
                throw new \Exception('Cannot update paid score card');
            }

            $score->update($data);

            return $score;
        });
    }

    private function calculateHours($schedules): float
    {
        $minutes = 0;

        foreach ($schedules as $schedule) {

            $start = Carbon::parse($schedule->date . ' ' . $schedule->actual_start_time);
            $end = Carbon::parse($schedule->date . ' ' . $schedule->actual_end_time);

            if ($end <= $start) {
                throw new \Exception("Invalid time range for schedule {$schedule->id}");
            }

            $minutes += $start->diffInMinutes($end);
        }

        return round($minutes / 60, 2);
    }
  

    private function resolveRate($employee, ?string $taxType): float
    {
        // American
        if ($employee->Nationality === 'American') {
            return match ($employee->position) {
                'CrowMember' => 14,
                'assistantManager' => 15,
                'shiftManager' => 16,
            };
        }

        // Foreigner
        if ($employee->Nationality === 'Foreigner') {

            if ($taxType === 'w2') {
                return match ($employee->position) {
                    'CrowMember' => 13,
                    'assistantManager' => 14,
                    'shiftManager' => 15,
                };
            }

            if ($taxType === '1099') {
                return match ($employee->position) {
                    'CrowMember' => 12,
                    'assistantManager' => 13,
                    'shiftManager' => 14,
                };
            }

            throw new \Exception("Invalid tax type for employee {$employee->id}");
        }

        throw new \Exception("Rate not defined for employee {$employee->id}");
    }
    private function calculateSalary(float $totalHours, float $rate, float $trackerScore, float $taxAmount): array
    {
        // bonus rule
        $bonus = $trackerScore >= 75 ? 1 : 0;

        // overtime
        if ($totalHours > 40) {
            $regularHours = 40;
            $overtimeHours = $totalHours - 40;

            $effectiveHours = $regularHours + ($overtimeHours * 1.5);
        } else {
            $effectiveHours = $totalHours;
        }
        $rateWithBonus = $rate + $bonus;

        // gross salary
        $grossSalary = ($rate + $bonus) * $effectiveHours;

        // deduct tax
        $finalSalary = $grossSalary - $taxAmount;

        // cap
        if ($finalSalary > 1400) {
            $finalSalary = 1400;
        }

        if ($finalSalary < 0) {
            $finalSalary = 0;
        }

        return [
            'salary' => round($finalSalary, 2),
            'effectiveHours' => round($effectiveHours, 2),
            'rateWithBonus' => round($rateWithBonus, 2),

        ];
    }

    private function getEmployeeTax($employee)
    {
        $employeeTax = DB::table('employeetaxes')
            ->where('employeeId', $employee->id)
            ->first();

        if (!$employeeTax) {
            throw new \Exception("No tax assigned for employee {$employee->id}");
        }

        $tax = DB::table('taxes')
            ->where('id', $employeeTax->taxesId)
            ->first();

        if (!$tax) {
            throw new \Exception("Tax not found for employee {$employee->id}");
        }

        return $tax; // contains taxtype + taxAmount
    }
}