<?php

namespace App\Services\Api;

use App\Models\ShiftRequirementDay;
use App\Models\ShiftRequirementTime;
use Illuminate\Support\Facades\DB;
use Exception;

class ShiftRequirementService
{
    public function index(?int $storeId = null)
    {
        return ShiftRequirementDay::with('times.skill')
            ->when($storeId, function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->orderBy('store_id')
            ->orderByRaw("
                FIELD(day_of_week, 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday')
            ")
            ->get();
    }
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            $day = ShiftRequirementDay::create([
                'store_id' => $data['store_id'],
                'day_of_week' => $data['day_of_week'],
            ]);

            foreach ($data['times'] as $time) {
                ShiftRequirementTime::create([
                    'shift_requirement_day_id' => $day->id,
                    'start_time' => $time['start_time'],
                    'end_time' => $time['end_time'],
                    'skill_id' => $time['skill_id'],
                    'required_employees' => $time['required_employees'],
                ]);
            }

            return $day->load('times.skill');
        });
    }

    public function show(int $id)
    {
        return ShiftRequirementDay::with('times.skill')->findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {

            $day = ShiftRequirementDay::findOrFail($id);

            if (isset($data['day_of_week'])) {
                $day->update([
                    'day_of_week' => $data['day_of_week']
                ]);
            }

            if (isset($data['times'])) {

                foreach ($data['times'] as $time) {

                    if (isset($time['id'])) {
                        // update
                        ShiftRequirementTime::where('id', $time['id'])
                            ->update([
                                'start_time' => $time['start_time'],
                                'end_time' => $time['end_time'],
                                'skill_id' => $time['skill_id'],
                                'required_employees' => $time['required_employees'],
                            ]);
                    } else {
                        // create new
                        ShiftRequirementTime::create([
                            'shift_requirement_day_id' => $day->id,
                            'start_time' => $time['start_time'],
                            'end_time' => $time['end_time'],
                            'skill_id' => $time['skill_id'],
                            'required_employees' => $time['required_employees'],
                        ]);
                    }
                }
            }

            return $day->load('times.skill');
        });
    }

    public function delete(int $id)
    {
        $day = ShiftRequirementDay::findOrFail($id);
        $day->delete();
    }
}