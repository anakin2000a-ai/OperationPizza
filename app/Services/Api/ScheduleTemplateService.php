<?php

namespace App\Services\Api;

use App\Models\ScheduleTemplate;
use App\Models\ScheduleTemplateDetail;
use App\Models\MasterSchedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleTemplateService
{
    public function getAll(): Collection
    {
        return ScheduleTemplate::with('details')->orderBy('id', 'asc')->get();
    }

    public function saveGeneralTemplate(array $data, int $userId): ScheduleTemplate
    {
        return DB::transaction(function () use ($data, $userId) {

            $template = ScheduleTemplate::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($data['details'] as $detail) {

                $template->details()->create([
                    'day_of_week' => $detail['day_of_week'],
                    'start_time' => $detail['start_time'],
                    'end_time' => $detail['end_time'],
                    'skill_id' => $detail['skill_id'],
                ]);
            }

            return $template->load('details');
        });
    }

    public function loadTemplatePreview(array $data): array
    {
        $template = ScheduleTemplate::with('details')
            ->findOrFail($data['template_id']);

        $shifts = [];

        foreach ($template->details as $detail) {

            $date = $this->mapDayToDate(
                $detail->day_of_week,
                $data['start_date']
            );

            $shifts[] = [
                'date' => $date,
                'start_time' => $detail->start_time,
                'end_time' => $detail->end_time,
                'skill_id' => $detail->skill_id,
                'employee_id' => null,
                'source' => 'template'
            ];
        }

        return [
            'template_shifts' => $shifts
        ];
    }
    public function getById(int $id): ScheduleTemplate
    {
        return ScheduleTemplate::with('details')->findOrFail($id);
    }

    public function delete(ScheduleTemplate $template): bool
    {
        return $template->delete();
    }
    private function mapDayToDate(string $day, string $startDate): string
    {
        $start = \Carbon\Carbon::parse($startDate);

        $map = [
            'tuesday' => 0,
            'wednesday' => 1,
            'thursday' => 2,
            'friday' => 3,
            'saturday' => 4,
            'sunday' => 5,
            'monday' => 6,
        ];

        return $start->copy()->addDays($map[$day])->toDateString();
    }

     
}