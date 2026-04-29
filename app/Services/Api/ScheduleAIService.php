<?php
 
namespace App\Services\Api;

use App\Models\DayOff;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\ShiftRequirementDay;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\MasterSchedule;
use App\Models\TrackerSchedule;

class ScheduleAIService
{
    public function generate(int $storeId): array
    {
        // =========================
        // 1. PREPARE DATES
        // =========================
        $latest = MasterSchedule::where('store_id', $storeId)
            ->orderByDesc('end_date')
            ->first();

        if ($latest) {
            $start = Carbon::parse($latest->end_date)->addDay();
        } else {
            $start = Carbon::now();
        }

        if ($start->dayOfWeek !== Carbon::TUESDAY) {
            $start = $start->next(Carbon::TUESDAY);
        }

        $end = (clone $start)->addDays(6);

        // =========================
        // 2. GENERATE SCHEDULES IN MEMORY
        // =========================
        $pendingSchedules = collect();
        $errors = [];
        $hasCriticalError = false;

        $currentDate = clone $start;

        while ($currentDate <= $end) {

            $dayName = strtolower($currentDate->format('l'));

            $day = ShiftRequirementDay::with('times')
                ->where('store_id', $storeId)
                ->where('day_of_week', $dayName)
                ->first();

            if (!$day || $day->times->isEmpty()) {
                $currentDate->addDay();
                continue;
            }

            foreach ($day->times as $requirement) {

                $candidates = $this->getCandidates($storeId, $requirement, $currentDate, $pendingSchedules);

                if ($candidates->count() < $requirement->required_employees) {
                    $errors[] = [
                        'day' => $dayName,
                        'shift' => $requirement->start_time . '-' . $requirement->end_time,
                        'required_employees' => $requirement->required_employees,
                        'available_employees' => $candidates->count(),
                        'message' => "Not enough employees available for this shift."
                    ];
                    $hasCriticalError = true;
                    break 2;  
                }

                $selected = $candidates
                    ->sortByDesc('score')
                    ->take($requirement->required_employees);

                foreach ($selected as $employee) {
                    $pendingSchedules->push([
                        'employee_id' => $employee->id,
                        'date' => $currentDate->toDateString(),
                        'start_time' => $requirement->start_time,
                        'end_time' => $requirement->end_time,
                        'skill_id' => $requirement->skill_id,
                        'score' => $employee->score,
                    ]);
                }
            }

            $currentDate->addDay();
        }

        // =========================
        // 3. Chcek with errors
        // =========================
        if ($hasCriticalError || $pendingSchedules->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Schedule generation failed due to insufficient employees for one or more shifts.',
                'errors' => $errors,
            ];
        }

        // =========================
        // 4. final save
        // =========================
        return DB::transaction(function () use ($storeId, $start, $end, $pendingSchedules) {

            $masterSchedule = MasterSchedule::create([
                'store_id' => $storeId,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'created_by' => auth()->id(),
            ]);

            TrackerSchedule::create([
                'scheduleWeekId' => $masterSchedule->id,
            ]);

            $results = [];

            foreach ($pendingSchedules as $scheduleData) {
                Schedule::create([
                    'employee_id' => $scheduleData['employee_id'],
                    'schedule_week_id' => $masterSchedule->id,
                    'date' => $scheduleData['date'],
                    'start_time' => $scheduleData['start_time'],
                    'end_time' => $scheduleData['end_time'],
                    'skill_id' => $scheduleData['skill_id'],
                ]);

                $results[] = [
                    'employee_id' => $scheduleData['employee_id'],
                    'date' => $scheduleData['date'],
                    'shift' => $scheduleData['start_time'] . '-' . $scheduleData['end_time'],
                    'score' => $scheduleData['score']
                ];
            }

            return [
                'success' => true,
                'master_schedule' => $masterSchedule,
                'schedules' => $results
            ];
        });
    }

    private function getCandidates(int $storeId, $requirement, Carbon $date, ?Collection $pendingSchedules = null): Collection
    {
        $dateString = $date->toDateString();

        $dbDailySchedules = Schedule::where('date', $dateString)->get()->toArray();
        
        if ($pendingSchedules) {
            $memDailySchedules = $pendingSchedules->where('date', $dateString)->values()->toArray();
            $dailySchedules = collect(array_merge($dbDailySchedules, $memDailySchedules))->groupBy('employee_id');
        } else {
            $dailySchedules = collect($dbDailySchedules)->groupBy('employee_id');
        }

        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd = now()->endOfWeek()->toDateString();

        $dbWeeklySchedules = Schedule::whereBetween('date', [$weekStart, $weekEnd])->get()->toArray();
        
        if ($pendingSchedules) {
            $memWeeklySchedules = $pendingSchedules->whereBetween('date', [$weekStart, $weekEnd])->values()->toArray();
            $weeklySchedules = collect(array_merge($dbWeeklySchedules, $memWeeklySchedules))->groupBy('employee_id');
        } else {
            $weeklySchedules = collect($dbWeeklySchedules)->groupBy('employee_id');
        }

        $employees = Employee::where('store_id', $storeId)
            ->where('status', 'hired')
            ->whereHas('skills', function ($q) use ($requirement) {
                $q->where('skill_id', $requirement->skill_id);
            })
            ->with(['skills', 'availability.times'])
            ->get();

        // ✅ Root Fix: Convert Shift times to numerical seconds to avoid text comparison errors.
        $reqStartTimestamp = strtotime($requirement->start_time);
        $reqEndTimestamp = strtotime($requirement->end_time);
        $shiftHours = ($reqEndTimestamp - $reqStartTimestamp) / 3600;

        return $employees->filter(function ($employee) use ($date, $dailySchedules, $reqStartTimestamp, $reqEndTimestamp, $shiftHours) {
            // Skip the employee if they have an approved day off on this date
            if ($this->hasApprovedDayOff($employee->id, $date)) {
                return false;
            }
            $employeeSchedules = $dailySchedules[$employee->id] ?? collect();

            // ✅ Checking for Overlap Using Numbers            
                $hasOverlap = $employeeSchedules->contains(function ($schedule) use ($reqStartTimestamp, $reqEndTimestamp) {
                $scheduleStart = strtotime($schedule['start_time']);
                $scheduleEnd = strtotime($schedule['end_time']);
                
                return $scheduleStart < $reqEndTimestamp && $scheduleEnd > $reqStartTimestamp;
            });

             $isConsecutiveShift = $employeeSchedules->some(function ($schedule) use ($reqStartTimestamp, $reqEndTimestamp) {
                $scheduleStart = strtotime($schedule['start_time']);
                $scheduleEnd = strtotime($schedule['end_time']);
                
                return $scheduleEnd === $reqStartTimestamp || $scheduleStart === $reqEndTimestamp;
            });

            if ($hasOverlap && !$isConsecutiveShift) {
                return false; 
            }

            // ✅ Calculating Hours Using Numbers   
            $totalHours = $employeeSchedules->sum(function ($s) {
                return (strtotime($s['end_time']) - strtotime($s['start_time'])) / 3600;
            });

            if (($totalHours + $shiftHours) > 15) return false; 

            // ✅ Passing numbers to the availability function
            if (!$this->isAvailable($employee, $reqStartTimestamp, $reqEndTimestamp, $date)) return false;

            return true;

        })->map(function ($employee) use ($requirement, $weeklySchedules, $dailySchedules) {
            
            $skillScore = $employee->skills->where('id', $requirement->skill_id)->first()?->pivot->rating ?? 0;
            $availabilityScore = 100;
            $performanceScore = 50;
            $weeklyLoad = count($weeklySchedules[$employee->id] ?? []);
            $sameDayBonus = count($dailySchedules[$employee->id] ?? []) > 0 ? 50 : 0;

            $employee->score = ($skillScore * 0.5) + ($performanceScore * 0.3) + ($availabilityScore * 0.2) - ($weeklyLoad * 3) + $sameDayBonus;

            return $employee;
        });
    }

    // ✅ Update the function to accept numbers (Timestamps) and compare using them.
    private function isAvailable($employee, int $reqStartTimestamp, int $reqEndTimestamp, Carbon $date): bool
    {
        $dayName = strtolower($date->format('l'));

        foreach ($employee->availability as $availability) {
            if ($availability->day_of_week !== $dayName) {
                continue;
            }

            foreach ($availability->times as $time) {
                // ✅ Convert availability times to numbers as well.
                $availStart = strtotime($time->from);
                $availEnd = strtotime($time->to);
                
                if ($availStart <= $reqStartTimestamp && $availEnd >= $reqEndTimestamp) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getSuggestions(int $storeId, array $data): array
    {
        return DB::transaction(function () use ($storeId, $data) {
            $start = Carbon::parse($data['start_date']);
            $end = Carbon::parse($data['end_date']);
            $results = [];
            
            // ✅ Set to track proposed employees in memory (same concept as pendingSchedules)
            $suggestedSchedules = collect();

            while ($start <= $end) {
                $dayName = strtolower($start->format('l'));
                $day = ShiftRequirementDay::with('times')->where('store_id', $storeId)->where('day_of_week', $dayName)->first();

                if (!$day || $day->times->isEmpty()) {
                    $start->addDay();
                    continue;
                }

                foreach ($day->times as $requirement) {
                    
                   // ✅ Pass `suggestedSchedules` so the system is aware of previous suggestions for the same day.
                    $candidates = $this->getCandidates($storeId, $requirement, $start, $suggestedSchedules);

                    if ($candidates->count() < $requirement->required_employees) {
                        $results[] = [
                            'day' => $dayName, 
                            'shift' => $requirement->start_time . '-' . $requirement->end_time, 
                            'required_employees' => $requirement->required_employees,
                            'available_employees' => $candidates->count(),
                            'message' => "Not enough employees available."
                        ];
                        continue;
                    }

                    $selectedCandidates = $candidates->sortByDesc('score')->take($requirement->required_employees);
                    
                    $suggestedEmployees = $selectedCandidates->map(function ($employee) use ($requirement) {
                        return [
                            'employee_id' => $employee->id, 
                            'name' => $employee->FirstName . ' ' . $employee->LastName, 
                            'skill_score' => $employee->score, 
                            'shift' => $requirement->start_time . '-' . $requirement->end_time
                        ];
                    });

                    // ✅ Add the selected employees to memory so they are remembered for the next shift.
                    foreach ($selectedCandidates as $employee) {
                        $suggestedSchedules->push([
                            'employee_id' => $employee->id,
                            'date' => $start->toDateString(),
                            'start_time' => $requirement->start_time,
                            'end_time' => $requirement->end_time,
                        ]);
                    }

                    $results[] = [
                        'day' => $dayName, 
                        'shift' => $requirement->start_time . '-' . $requirement->end_time, 
                        'suggestions' => $suggestedEmployees
                    ];
                }
                $start->addDay();
            }
            return $results;
        });
    }
    private function hasApprovedDayOff(int $employeeId, Carbon $date): bool
    {
        // Check if the employee has a day off on the given date with 'approved' status.
        $dayOff = DayOff::where('employee_id', $employeeId)
                        ->whereDate('date', $date->toDateString())
                        ->where('acceptedStatus', 'approved')
                        ->first();

        // If there's an approved day off, return true, otherwise false.
        return $dayOff !== null;
    }
}
// class ScheduleAIService
// {
 
//     public function generate(int $storeId): array
//     {
//         // =========================
//         // 1. PREPARE DATES (بدون إنشاء Master بعد)
//         // =========================
//         $latest = MasterSchedule::where('store_id', $storeId)
//             ->orderByDesc('end_date')
//             ->first();

//         if ($latest) {
//             $start = Carbon::parse($latest->end_date)->addDay();
//         } else {
//             $start = Carbon::now();
//         }

//         // ✅ force Tuesday start
//         if ($start->dayOfWeek !== Carbon::TUESDAY) {
//             $start = $start->next(Carbon::TUESDAY);
//         }

//         $end = (clone $start)->addDays(6);

//         // =========================
//         // 2. GENERATE SCHEDULES IN MEMORY أولاً
//         // =========================
//         $pendingSchedules = collect();
//         $errors = [];

//         $currentDate = clone $start;

//         while ($currentDate <= $end) {

//             $dayName = strtolower($currentDate->format('l'));

//             $day = ShiftRequirementDay::with('times')
//                 ->where('store_id', $storeId)
//                 ->where('day_of_week', $dayName)
//                 ->first();

//             if (!$day || $day->times->isEmpty()) {
//                 $currentDate->addDay();
//                 continue;
//             }

//             foreach ($day->times as $requirement) {

//                 $candidates = $this->getCandidates($storeId, $requirement, $currentDate);

//                 if ($candidates->count() < $requirement->required_employees) {
//                     $errors[] = [
//                         'day' => $dayName,
//                         'shift' => $requirement->start_time . '-' . $requirement->end_time,
//                         'message' => "Not enough employees available for this shift."
//                     ];
//                     continue;
//                 }

//                 $selected = $candidates
//                     ->sortByDesc('score')
//                     ->take($requirement->required_employees);

//                 foreach ($selected as $employee) {
//                     $pendingSchedules->push([
//                         'employee_id' => $employee->id,
//                         'date' => $currentDate->toDateString(),
//                         'start_time' => $requirement->start_time,
//                         'end_time' => $requirement->end_time,
//                         'skill_id' => $requirement->skill_id,
//                         'score' => $employee->score,
//                     ]);
//                 }
//             }

//             $currentDate->addDay();
//         }

//         // =========================
//         // 3. التحقق: هل توجد schedules؟
//         // =========================
//         if ($pendingSchedules->isEmpty()) {
//             return [
//                 'success' => false,
//                 'message' => 'No schedules could be generated.',
//                 'errors' => $errors,
//             ];
//         }

//         // =========================
//         // 4. إنشاء Master & Tracker وحفظ Schedules
//         // =========================
//         return DB::transaction(function () use ($storeId, $start, $end, $pendingSchedules, $errors) {

//             $masterSchedule = MasterSchedule::create([
//                 'store_id' => $storeId,
//                 'start_date' => $start->toDateString(),
//                 'end_date' => $end->toDateString(),
//                 'created_by' => auth()->id(),
//             ]);

//             TrackerSchedule::create([
//                 'scheduleWeekId' => $masterSchedule->id,
//             ]);

//             $results = [];

//             foreach ($pendingSchedules as $scheduleData) {
//                 Schedule::create([
//                     'employee_id' => $scheduleData['employee_id'],
//                     'schedule_week_id' => $masterSchedule->id,
//                     'date' => $scheduleData['date'],
//                     'start_time' => $scheduleData['start_time'],
//                     'end_time' => $scheduleData['end_time'],
//                     'skill_id' => $scheduleData['skill_id'],
//                 ]);

//                 $results[] = [
//                     'employee_id' => $scheduleData['employee_id'],
//                     'date' => $scheduleData['date'],
//                     'shift' => $scheduleData['start_time'] . '-' . $scheduleData['end_time'],
//                     'score' => $scheduleData['score']
//                 ];
//             }

//             return [
//                 'success' => true,
//                 'master_schedule' => $masterSchedule,
//                 'schedules' => $results,
//                 'errors' => $errors,
//             ];
//         });
//     }

//     private function getCandidates(int $storeId, $requirement, Carbon $date): Collection
//     {
//         // ✅ preload schedules for the day (avoid N+1 query issue)
//         $dailySchedules = Schedule::where('date', $date->toDateString())
//             ->get()
//             ->groupBy('employee_id');

//         // ✅ preload weekly schedules for fairness (prevent overloading employees with too many shifts)
//         $weeklySchedules = Schedule::whereBetween('date', [
//             now()->startOfWeek(),
//             now()->endOfWeek()
//         ])->get()->groupBy('employee_id');

//         $employees = Employee::where('store_id', $storeId)
//             ->where('status', 'hired') // Ensure the employee is hired
//             ->whereHas('skills', function ($q) use ($requirement) {
//                 $q->where('skill_id', $requirement->skill_id); // Match required skill
//             })
//             ->with([
//                 'skills',
//                 'availability.times'
//             ])
//             ->get();

//         return $employees->filter(function ($employee) use ($requirement, $date, $dailySchedules) {

//             $employeeSchedules = $dailySchedules[$employee->id] ?? collect();

//             // ✅ Check for consecutive shifts (10:00 AM - 5:00 PM followed by 5:00 PM - 10:00 PM)
//             $hasOverlap = $employeeSchedules->contains(function ($schedule) use ($requirement) {
//                 return $schedule->start_time < $requirement->end_time &&
//                     $schedule->end_time > $requirement->start_time;
//             });

//             // Allow employee to work multiple consecutive shifts
//             $isConsecutiveShift = $employeeSchedules->some(function ($schedule) use ($requirement) {
//                 return ($schedule->end_time === $requirement->start_time || $schedule->start_time === $requirement->end_time);
//             });

//             if ($hasOverlap && !$isConsecutiveShift) {
//                 return false; // Exclude employee if they have an overlap but it's not a consecutive shift
//             }

//             // ✅ Max hours (16h/day check)
//             $totalHours = $employeeSchedules->sum(function ($s) {
//                 return (strtotime($s->end_time) - strtotime($s->start_time)) / 3600;
//             });

//             $shiftHours = (strtotime($requirement->end_time) - strtotime($requirement->start_time)) / 3600;

//             if (($totalHours + $shiftHours) > 16) return false; // If total hours for the day exceed 16 hours, exclude this employee

//             // ✅ Availability check (Is employee available for this shift?)
//             if (!$this->isAvailable($employee, $requirement, $date)) return false;

//             return true;

//         })->map(function ($employee) use ($requirement, $weeklySchedules) {

//             $skillScore = $employee->skills
//                 ->where('id', $requirement->skill_id)
//                 ->first()?->pivot->rating ?? 0;

//             $availabilityScore = 100;
//             $performanceScore = 50;

//             // ✅ Fairness (weekly load)
//             $weeklyLoad = count($weeklySchedules[$employee->id] ?? []);

//             $employee->score =
//                 ($skillScore * 0.5) +
//                 ($performanceScore * 0.3) +
//                 ($availabilityScore * 0.2)
//                 - ($weeklyLoad * 3); // Penalize employees with heavy weekly loads

//             return $employee;
//         });
//     }

//     private function isAvailable($employee, $requirement, Carbon $date): bool
//     {
//         $dayName = strtolower($date->format('l'));

//         foreach ($employee->availability as $availability) {

//             if ($availability->day_of_week !== $dayName) {
//                 continue;
//             }

//             foreach ($availability->times as $time) {

//                 if (
//                     $time->from <= $requirement->start_time &&
//                     $time->to >= $requirement->end_time
//                 ) {
//                     return true;
//                 }
//             }
//         }

//         return false;
//     }

//     public function getSuggestions(int $storeId, array $data): array
//     {
//         return DB::transaction(function () use ($storeId, $data) {

//             $start = Carbon::parse($data['start_date']);
//             $end = Carbon::parse($data['end_date']);

//             $results = [];

//             while ($start <= $end) {

//                 $dayName = strtolower($start->format('l'));

//                 $day = ShiftRequirementDay::with('times')
//                     ->where('store_id', $storeId)
//                     ->where('day_of_week', $dayName)
//                     ->first();

//                 if (!$day || $day->times->isEmpty()) {
//                     $start->addDay();
//                     continue;
//                 }

//                 foreach ($day->times as $requirement) {

//                     $candidates = $this->getCandidates($storeId, $requirement, $start);

//                     if ($candidates->count() < $requirement->required_employees) {
//                         $results[] = [
//                             'day' => $dayName,
//                             'shift' => $requirement->start_time . '-' . $requirement->end_time,
//                             'message' => "Not enough employees available for this shift."
//                         ];
//                         continue;
//                     }

//                     $suggestedEmployees = $candidates
//                         ->sortByDesc('score')
//                         ->take($requirement->required_employees)
//                         ->map(function ($employee) use ($requirement) {
//                             return [
//                                 'employee_id' => $employee->id,
//                                 'name' => $employee->FirstName . ' ' . $employee->LastName,
//                                 'skill_score' => $employee->score,
//                                 'shift' => $requirement->start_time . '-' . $requirement->end_time,
//                             ];
//                         });

//                     $results[] = [
//                         'day' => $dayName,
//                         'shift' => $requirement->start_time . '-' . $requirement->end_time,
//                         'suggestions' => $suggestedEmployees
//                     ];
//                 }

//                 $start->addDay();
//             }

//             return $results;
//         });
//     }
// }
   

  
    // public function generate(int $storeId): array
    // {
    //     return DB::transaction(function () use ($storeId) {

    //         // =========================
    //         // 1. CREATE NEW MASTER SCHEDULE
    //         // =========================
    //         $latest = MasterSchedule::where('store_id', $storeId)
    //             ->orderByDesc('end_date')
    //             ->first();

    //         if ($latest) {
    //             $start = Carbon::parse($latest->end_date)->addDay();
    //         } else {
    //             $start = Carbon::now();
    //         }

    //         // ✅ force Tuesday start
    //         if ($start->dayOfWeek !== Carbon::TUESDAY) {
    //             $start = $start->next(Carbon::TUESDAY);
    //         }

    //         $end = (clone $start)->addDays(6);

    //         $masterSchedule = MasterSchedule::create([
    //             'store_id' => $storeId,
    //             'start_date' => $start->toDateString(),
    //             'end_date' => $end->toDateString(),
    //             'created_by' => auth()->id(),
    //         ]);

    //         // ✅ tracker
    //         TrackerSchedule::create([
    //             'scheduleWeekId' => $masterSchedule->id,
    //         ]);

    //         // =========================
    //         // 2. GENERATE SCHEDULES
    //         // =========================
    //         $results = [];

    //         while ($start <= $end) {

    //             $dayName = strtolower($start->format('l'));

    //             $day = ShiftRequirementDay::with('times')
    //                 ->where('store_id', $storeId)
    //                 ->where('day_of_week', $dayName)
    //                 ->first();

    //             if (!$day || $day->times->isEmpty()) {
    //                 $start->addDay();
    //                 continue;
    //             }

    //             foreach ($day->times as $requirement) {

    //                 $candidates = $this->getCandidates($storeId, $requirement, $start);

    //                 if ($candidates->count() < $requirement->required_employees) {
    //                     $results[] = [
    //                         'day' => $dayName,
    //                         'shift' => $requirement->start_time . '-' . $requirement->end_time,
    //                         'message' => "Not enough employees available for this shift."
    //                     ];
    //                     return [
    //                         'message' => "Schedule generated with warnings",
    //                      ];
    //                     continue;
    //                 }

    //                 $selected = $candidates
    //                     ->sortByDesc('score')
    //                     ->take($requirement->required_employees);

    //                 foreach ($selected as $employee) {

    //                     Schedule::create([
    //                         'employee_id' => $employee->id,
    //                         'schedule_week_id' => $masterSchedule->id, // ✅ FIXED
    //                         'date' => $start->toDateString(),
    //                         'start_time' => $requirement->start_time,
    //                         'end_time' => $requirement->end_time,
    //                         'skill_id' => $requirement->skill_id,
    //                     ]);

    //                     $results[] = [
    //                         'employee_id' => $employee->id,
    //                         'date' => $start->toDateString(),
    //                         'shift' => $requirement->start_time . '-' . $requirement->end_time,
    //                         'score' => $employee->score
    //                     ];
    //                 }
    //             }

    //             $start->addDay();
    //         }

    //         return [
    //             'master_schedule' => $masterSchedule,
    //             'schedules' => $results
    //         ];
    //     });
    // }
    // private function getCandidates(int $storeId, $requirement, Carbon $date): Collection
    // {
    //     // ✅ preload schedules for the day (avoid N+1 query issue)
    //     $dailySchedules = Schedule::where('date', $date->toDateString())
    //         ->get()
    //         ->groupBy('employee_id');

    //     // ✅ preload weekly schedules for fairness (prevent overloading employees with too many shifts)
    //     $weeklySchedules = Schedule::whereBetween('date', [
    //         now()->startOfWeek(),
    //         now()->endOfWeek()
    //     ])->get()->groupBy('employee_id');

    //     $employees = Employee::where('store_id', $storeId)
    //         ->where('status', 'hired') // Ensure the employee is hired
    //         ->whereHas('skills', function ($q) use ($requirement) {
    //             $q->where('skill_id', $requirement->skill_id); // Match required skill
    //         })
    //         ->with([
    //             'skills',
    //             'availability.times'
    //         ])
    //         ->get();

    //     return $employees->filter(function ($employee) use ($requirement, $date, $dailySchedules) {

    //         $employeeSchedules = $dailySchedules[$employee->id] ?? collect();

    //         // ✅ Check for consecutive shifts (10:00 AM - 5:00 PM followed by 5:00 PM - 10:00 PM)
    //         $hasOverlap = $employeeSchedules->contains(function ($schedule) use ($requirement) {
    //             return $schedule->start_time < $requirement->end_time &&
    //                 $schedule->end_time > $requirement->start_time;
    //         });

    //         // Allow employee to work multiple consecutive shifts
    //         $isConsecutiveShift = $employeeSchedules->some(function ($schedule) use ($requirement) {
    //             return ($schedule->end_time === $requirement->start_time || $schedule->start_time === $requirement->end_time);
    //         });

    //         if ($hasOverlap && !$isConsecutiveShift) {
    //             return false; // Exclude employee if they have an overlap but it’s not a consecutive shift
    //         }

    //         // ✅ Max hours (10h/day check)
    //         $totalHours = $employeeSchedules->sum(function ($s) {
    //             return (strtotime($s->end_time) - strtotime($s->start_time)) / 3600;
    //         });

    //         $shiftHours = (strtotime($requirement->end_time) - strtotime($requirement->start_time)) / 3600;

    //         if (($totalHours + $shiftHours) > 16) return false; // If total hours for the day exceed 10 hours, exclude this employee

    //         // ✅ Availability check (Is employee available for this shift?)
    //         if (!$this->isAvailable($employee, $requirement, $date)) return false;

    //         return true;

    //     })->map(function ($employee) use ($requirement, $weeklySchedules) {

    //         $skillScore = $employee->skills
    //             ->where('id', $requirement->skill_id)
    //             ->first()?->pivot->rating ?? 0;

    //         $availabilityScore = 100;
    //         $performanceScore = 50;

    //         // ✅ Fairness (weekly load)
    //         $weeklyLoad = count($weeklySchedules[$employee->id] ?? []);

    //         $employee->score =
    //             ($skillScore * 0.5) +
    //             ($performanceScore * 0.3) +
    //             ($availabilityScore * 0.2)
    //             - ($weeklyLoad * 3); // Penalize employees with heavy weekly loads

    //         return $employee;
    //     });
    // }

    // private function isAvailable($employee, $requirement, Carbon $date): bool
    // {
    //     $dayName = strtolower($date->format('l'));

    //     foreach ($employee->availability as $availability) {

    //         if ($availability->day_of_week !== $dayName) {
    //             continue;
    //         }

    //         foreach ($availability->times as $time) {

    //             if (
    //                 $time->from <= $requirement->start_time &&
    //                 $time->to >= $requirement->end_time
    //             ) {
    //                 return true;
    //             }
    //         }
    //     }

    //     return false;
    // }

    // public function getSuggestions(int $storeId, array $data): array
    // {
    //     return DB::transaction(function () use ($storeId, $data) {

    //         $start = Carbon::parse($data['start_date']);
    //         $end = Carbon::parse($data['end_date']);

    //         $results = [];

    //         while ($start <= $end) {

    //             $dayName = strtolower($start->format('l'));

    //             $day = ShiftRequirementDay::with('times')
    //                 ->where('store_id', $storeId)
    //                 ->where('day_of_week', $dayName)
    //                 ->first();

    //             if (!$day || $day->times->isEmpty()) {
    //                 $start->addDay();
    //                 continue;
    //             }

    //             foreach ($day->times as $requirement) {

    //                 $candidates = $this->getCandidates($storeId, $requirement, $start);

    //                 if ($candidates->count() < $requirement->required_employees) {
    //                     $results[] = [
    //                         'day' => $dayName,
    //                         'shift' => $requirement->start_time . '-' . $requirement->end_time,
    //                         'message' => "Not enough employees available for this shift."
    //                     ];
    //                     continue;
    //                 }

    //                 $suggestedEmployees = $candidates
    //                     ->sortByDesc('score')
    //                     ->take($requirement->required_employees)
    //                     ->map(function ($employee) use ($requirement) {
    //                         return [
    //                             'employee_id' => $employee->id,
    //                             'name' => $employee->FirstName . ' ' . $employee->LastName,
    //                             'skill_score' => $employee->score,
    //                             'shift' => $requirement->start_time . '-' . $requirement->end_time,
    //                         ];
    //                     });

    //                 $results[] = [
    //                     'day' => $dayName,
    //                     'shift' => $requirement->start_time . '-' . $requirement->end_time,
    //                     'suggestions' => $suggestedEmployees
    //                 ];
    //             }

    //             $start->addDay();
    //         }

    //         return $results;
    //     });
    // }
//     public function getSuggestions(int $storeId, array $data): array
//     {
//         return DB::transaction(function () use ($storeId, $data) {

//             $start = Carbon::parse($data['start_date']);
//             $end = Carbon::parse($data['end_date']);

//             $results = [];

//             while ($start <= $end) {

//                 $dayName = strtolower($start->format('l'));

//                 $day = ShiftRequirementDay::with('times')
//                     ->where('store_id', $storeId)
//                     ->where('day_of_week', $dayName)
//                     ->first();

//                 if (!$day || $day->times->isEmpty()) {
//                     $start->addDay();
//                     continue;
//                 }

//                 foreach ($day->times as $requirement) {

//                     $candidates = $this->getCandidates($storeId, $requirement, $start);

//                     if ($candidates->count() < $requirement->required_employees) {
//                         $results[] = [
//                             'day' => $dayName,
//                             'shift' => $requirement->start_time . '-' . $requirement->end_time,
//                             'message' => "Not enough employees available for this shift."
//                         ];
//                         continue;
//                     }

//                     $suggestedEmployees = $candidates
//                         ->sortByDesc('score')
//                         ->take($requirement->required_employees)
//                         ->map(function ($employee) use ($requirement) {
//                             return [
//                                 'employee_id' => $employee->id,
//                                 'name' => $employee->FirstName . ' ' . $employee->LastName,
//                                 'skill_score' => $employee->score,
//                                 'shift' => $requirement->start_time . '-' . $requirement->end_time,
//                             ];
//                         });

//                     $results[] = [
//                         'day' => $dayName,
//                         'shift' => $requirement->start_time . '-' . $requirement->end_time,
//                         'suggestions' => $suggestedEmployees
//                     ];
//                 }

//                 $start->addDay();
//             }

//             return $results;
//         });
//     }
//     public function generate(int $storeId): array
//     {
//         return DB::transaction(function () use ($storeId) {

//             // =========================
//             // 1. CREATE NEW MASTER SCHEDULE
//             // =========================
//             $latest = MasterSchedule::where('store_id', $storeId)
//                 ->orderByDesc('end_date')
//                 ->first();

//             if ($latest) {
//                 $start = Carbon::parse($latest->end_date)->addDay();
//             } else {
//                 $start = Carbon::now();
//             }

//             // Force Tuesday start
//             if ($start->dayOfWeek !== Carbon::TUESDAY) {
//                 $start = $start->next(Carbon::TUESDAY);
//             }

//             $end = (clone $start)->addDays(6);

//             $masterSchedule = MasterSchedule::create([
//                 'store_id' => $storeId,
//                 'start_date' => $start->toDateString(),
//                 'end_date' => $end->toDateString(),
//                 'created_by' => auth()->id(),
//             ]);

//             TrackerSchedule::create([
//                 'scheduleWeekId' => $masterSchedule->id,
//             ]);

//             // =========================
//             // 2. GENERATE SCHEDULES
//             // =========================
//             $results = [];
            
//             // ✅ FIX: Track schedules created IN MEMORY during this loop to bypass DB transaction snapshots
//             $createdSchedules = collect();

//             while ($start <= $end) {

//                 $dayName = strtolower($start->format('l'));

//                 $day = ShiftRequirementDay::with('times')
//                     ->where('store_id', $storeId)
//                     ->where('day_of_week', $dayName)
//                     ->first();

//                 if (!$day || $day->times->isEmpty()) {
//                     $start->addDay();
//                     continue;
//                 }

//                 foreach ($day->times as $requirement) {

//                     // ✅ FIX: Pass the in-memory collection to the candidate finder
//                     $candidates = $this->getCandidates($storeId, $requirement, $start, $createdSchedules);

//                     if ($candidates->count() < $requirement->required_employees) {
//                         throw new Exception(
//                             "Not enough employees for {$dayName} {$requirement->start_time}-{$requirement->end_time}"
//                         );
//                     }

//                     $selected = $candidates
//                         ->sortByDesc('score')
//                         ->take($requirement->required_employees);

//                     foreach ($selected as $employee) {

//                         $newSchedule = Schedule::create([
//                             'employee_id' => $employee->id,
//                             'schedule_week_id' => $masterSchedule->id,
//                             'date' => $start->toDateString(),
//                             'start_time' => $requirement->start_time,
//                             'end_time' => $requirement->end_time,
//                             'skill_id' => $requirement->skill_id,
//                         ]);

//                         // ✅ FIX: Push to our memory collection so the next shift knows about this one!
//                         $createdSchedules->push($newSchedule);

//                         $results[] = [
//                             'employee_id' => $employee->id,
//                             'date' => $start->toDateString(),
//                             'shift' => $requirement->start_time . '-' . $requirement->end_time,
//                             'score' => $employee->score
//                         ];
//                     }
//                 }

//                 $start->addDay();
//             }

//             return [
//                 'master_schedule' => $masterSchedule,
//                 'schedules' => $results
//             ];
//         });
//     }

//     private function getCandidates(int $storeId, $requirement, Carbon $date, Collection $createdSchedules): Collection
//     {
//         $dateString = $date->toDateString();

//                 // ✅ FIX: Merge the un-grouped collections FIRST, then group them.
//         // This prevents the "getKey does not exist" error with Eloquent collections.
        
//         $dbDailySchedules = Schedule::where('date', $dateString)->get();
//         $memDailySchedules = $createdSchedules->where('date', $dateString);
//         $dailySchedules = $dbDailySchedules->merge($memDailySchedules)->groupBy('employee_id');

//         $weekStart = $date->copy()->startOfWeek(Carbon::TUESDAY);
//         $weekEnd = $date->copy()->endOfWeek(Carbon::MONDAY);

//         $dbWeeklySchedules = Schedule::whereBetween('date', [
//             $weekStart->toDateString(),
//             $weekEnd->toDateString()
//         ])->get();

//         $memWeeklySchedules = $createdSchedules->whereBetween('date', [
//             $weekStart->toDateString(),
//             $weekEnd->toDateString()
//         ]);

//         $weeklySchedules = $dbWeeklySchedules->merge($memWeeklySchedules)->groupBy('employee_id');

//         $employees = Employee::where('store_id', $storeId)
//             ->where('status', 'hired')
//             ->whereHas('skills', function ($q) use ($requirement) {
//                 $q->where('skill_id', $requirement->skill_id);
//             })
//             ->with(['skills', 'availability.times'])
//             ->get();

//         $reqStartTimestamp = strtotime($requirement->start_time);
//         $reqEndTimestamp = strtotime($requirement->end_time);
//         $shiftHours = ($reqEndTimestamp - $reqStartTimestamp) / 3600;

//         // ⚠️ NOTE: 10:00-17:00 (7h) + 17:00-22:00 (5h) = 12 hours. 
//         // If you want to allow 12-hour consecutive shifts, change this limit to 12 or 14!
//         $maxDailyHours = 10; 

//         return $employees->filter(function ($employee) use (
//             $requirement, 
//             $date, 
//             $dailySchedules,
//             $reqStartTimestamp,
//             $reqEndTimestamp,
//             $shiftHours,
//             $maxDailyHours
//         ) {
//             $employeeSchedules = $dailySchedules[$employee->id] ?? collect();

//             // Check for overlapping shifts
//             $hasOverlap = $employeeSchedules->contains(function ($schedule) use ($reqStartTimestamp, $reqEndTimestamp) {
//                 $scheduleStart = strtotime($schedule->start_time);
//                 $scheduleEnd = strtotime($schedule->end_time);
//                 return $scheduleStart < $reqEndTimestamp && $scheduleEnd > $reqStartTimestamp;
//             });

//             // If there is an overlap, check if it's a perfectly consecutive shift
//             if ($hasOverlap) {
//                 $isConsecutiveShift = $employeeSchedules->some(function ($schedule) use ($reqStartTimestamp, $reqEndTimestamp) {
//                     $scheduleStart = strtotime($schedule->start_time);
//                     $scheduleEnd = strtotime($schedule->end_time);
//                     return $scheduleEnd === $reqStartTimestamp || $scheduleStart === $reqEndTimestamp;
//                 });

//                 // If it overlaps but is NOT consecutive, reject them
//                 if (!$isConsecutiveShift) {
//                     return false;
//                 }
//             }

//             // Max hours check
//             $totalHours = $employeeSchedules->sum(function ($s) {
//                 return (strtotime($s->end_time) - strtotime($s->start_time)) / 3600;
//             });

//             if (($totalHours + $shiftHours) > $maxDailyHours) {
//                 return false; 
//             }

//             if (!$this->isAvailable($employee, $requirement, $date)) {
//                 return false;
//             }

//             return true;

//         })->map(function ($employee) use ($requirement, $weeklySchedules, $dailySchedules) {
            
//             $skillScore = $employee->skills
//                 ->where('id', $requirement->skill_id)
//                 ->first()?->pivot->rating ?? 0;

//             $availabilityScore = 100;
//             $performanceScore = 50;

//             $weeklyLoad = count($weeklySchedules[$employee->id] ?? []);

//             // ✅ CRITICAL FIX: "Consecutive Shift Bonus"
//             // If they are already working today, give them a MASSIVE score boost. 
//             // This guarantees they won't lose Shift 2 to another employee who didn't work Shift 1.
//             $sameDayBonus = count($dailySchedules[$employee->id] ?? []) > 0 ? 50 : 0;

//             $employee->score =
//                 ($skillScore * 0.5) +
//                 ($performanceScore * 0.3) +
//                 ($availabilityScore * 0.2)
//                 - ($weeklyLoad * 3)
//                 + $sameDayBonus; // Apply the bonus!

//             return $employee;
//         });
//     }

//     private function isAvailable($employee, $requirement, Carbon $date): bool
//     {
//         $dayName = strtolower($date->format('l'));
//         $reqStart = strtotime($requirement->start_time);
//         $reqEnd = strtotime($requirement->end_time);

//         foreach ($employee->availability as $availability) {
//             if ($availability->day_of_week !== $dayName) {
//                 continue;
//             }

//             foreach ($availability->times as $time) {
//                 $availStart = strtotime($time->from);
//                 $availEnd = strtotime($time->to);
                
//                 if ($availStart <= $reqStart && $availEnd >= $reqEnd) {
//                     return true;
//                 }
//             }
//         }

//         return false;
//     }