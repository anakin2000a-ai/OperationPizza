<?php

namespace App\Services\Api;

use App\Models\EmployeeSkill;
use Illuminate\Database\Eloquent\Collection;

class EmployeeSkillService
{
    public function getAll(): Collection
    {
        return EmployeeSkill::with(['employee', 'skill'])
            ->orderBy('employee_id', 'asc')
            ->get();
    }

    public function getById(int $id): EmployeeSkill
    {
        return EmployeeSkill::with(['employee', 'skill'])->findOrFail($id);
    }

    public function create(array $data): EmployeeSkill
    {
        return EmployeeSkill::create($data);
    }

    public function update(EmployeeSkill $employeeSkill, array $data): EmployeeSkill
    {
        $employeeSkill->update($data);
        return $employeeSkill->fresh();
    }

    public function delete(EmployeeSkill $employeeSkill): bool
    {
        return $employeeSkill->delete();
    }
}