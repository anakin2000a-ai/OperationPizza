<?php

namespace App\Services\Api;

use App\Models\Deduction;
use App\Models\Employee;
use App\Models\EmployeeTax;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function create(array $data, $store): Employee
    {
        return DB::transaction(function () use ($data, $store) {
            $employee = Employee::create([
                'store_id'  => $store->id,
                'FirstName' => $data['FirstName'],
                'LastName'  => $data['LastName'],
                'HaveCar'   => $data['HaveCar'],
                'phone'     => $data['phone'],
                'email'     => $data['email'],
                'hire_date' => $data['hire_date'],
                'status'    => $data['status'],
            ]);

            if (!empty($data['ApartmentId']) || !empty($data['SimId'])) {
                Deduction::create([
                    'employeeId'  => $employee->id,
                    'ApartmentId' => $data['ApartmentId'] ?? null,
                    'SimId'       => $data['SimId'] ?? null,
                    'createdBy'   => auth()->id(),
                    'editedBy'    => null,
                ]);
            }

            if (!empty($data['taxesId'])) {
                EmployeeTax::create([
                    'employeeId' => $employee->id,
                    'taxesId'    => $data['taxesId'],
                    'createdBy'  => auth()->id(),
                    'editedBy'   => null,
                ]);
            }

            return $employee->fresh();
        });
    }

    public function findEmployeeInStore(int $storeId, int $employeeId): Employee
    {
        return Employee::where('store_id', $storeId)->findOrFail($employeeId);
    }

    public function update(array $data, Employee $employee): Employee
    {
        return DB::transaction(function () use ($data, $employee) {
            $employeeData = array_filter([
                'FirstName' => $data['FirstName'] ?? null,
                'LastName'  => $data['LastName'] ?? null,
                'HaveCar'   => $data['HaveCar'] ?? null,
                'phone'     => $data['phone'] ?? null,
                'email'     => $data['email'] ?? null,
                'hire_date' => $data['hire_date'] ?? null,
                'status'    => $data['status'] ?? null,
            ], fn ($value) => !is_null($value));

            if (!empty($employeeData)) {
                $employee->update($employeeData);
            }

            if (array_key_exists('ApartmentId', $data) || array_key_exists('SimId', $data)) {
                $deduction = Deduction::where('employeeId', $employee->id)->first();
                $hasDeductionValues = !empty($data['ApartmentId']) || !empty($data['SimId']);

                if ($deduction && $hasDeductionValues) {
                    $deduction->update([
                        'ApartmentId' => $data['ApartmentId'] ?? null,
                        'SimId'       => $data['SimId'] ?? null,
                        'editedBy'    => auth()->id(),
                    ]);
                } elseif ($deduction && !$hasDeductionValues) {
                    $deduction->delete();
                } elseif (!$deduction && $hasDeductionValues) {
                    Deduction::create([
                        'employeeId'  => $employee->id,
                        'ApartmentId' => $data['ApartmentId'] ?? null,
                        'SimId'       => $data['SimId'] ?? null,
                        'createdBy'   => auth()->id(),
                        'editedBy'    => null,
                    ]);
                }
            }

            if (array_key_exists('taxesId', $data)) {
                $tax = EmployeeTax::where('employeeId', $employee->id)->first();

                if ($tax && !empty($data['taxesId'])) {
                    $tax->update([
                        'taxesId'  => $data['taxesId'],
                        'editedBy' => auth()->id(),
                    ]);
                } elseif ($tax && empty($data['taxesId'])) {
                    $tax->delete();
                } elseif (!$tax && !empty($data['taxesId'])) {
                    EmployeeTax::create([
                        'employeeId' => $employee->id,
                        'taxesId'    => $data['taxesId'],
                        'createdBy'  => auth()->id(),
                        'editedBy'   => null,
                    ]);
                }
            }

            return $employee->fresh();
        });
    }

    public function delete(Employee $employee): void
    {
        DB::transaction(function () use ($employee) {
            EmployeeTax::where('employeeId', $employee->id)->delete();
            Deduction::where('employeeId', $employee->id)->delete();

            $employee->skills()->detach();

            $employee->delete();
        });
    }

    public function deleteDeductionByEmployeeId(int $storeId, int $employeeId): void
    {
        DB::transaction(function () use ($storeId, $employeeId) {
            $employee = $this->findEmployeeInStore($storeId, $employeeId);

            $deleted = Deduction::where('employeeId', $employee->id)->delete();

            if (!$deleted) {
                throw new ModelNotFoundException('No deductions found for this employee.');
            }
        });
    }
}