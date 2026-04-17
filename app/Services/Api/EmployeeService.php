<?php

namespace App\Services\Api;

use App\Models\Employee;
use App\Models\Deduction;
use App\Models\EmployeeTax;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function create(array $data, $store)
    {
        return DB::transaction(function () use ($data, $store) {

            $employee = Employee::create([
                'store_id' => $store->id,
                'FirstName' => $data['FirstName'],
                'LastName' => $data['LastName'],
                'HaveCar' => $data['HaveCar'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'hire_date' => $data['hire_date'],
                'status' => $data['status'],
            ]);

            // Deduction
            if (!empty($data['ApartmentId']) || !empty($data['SimId'])) {
                Deduction::create([
                    'employeeId' => $employee->id,
                    'ApartmentId' => $data['ApartmentId'] ?? null,
                    'SimId' => $data['SimId'] ?? null,
                    'createdBy' => auth()->id(),
                    'editedBy' => null,
                ]);
            }
 
            // Taxes
            if (!empty($data['taxesId'])) {
                EmployeeTax::create([
                    'employeeId' => $employee->id,
                    'taxesId' => $data['taxesId'],
                    'createdBy' => auth()->id(),
                    'editedBy' => null,
                ]);
            }

            return $employee;
        });
    }

    public function update(array $data, Employee $employee)
    {
        return DB::transaction(function () use ($data, $employee) {

            $employee->update([
                'FirstName' => $data['FirstName'],
                'LastName' => $data['LastName'],
                'HaveCar' => $data['HaveCar'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'hire_date' => $data['hire_date'],
                'status' => $data['status'],
            ]);

            // Deduction
            $deduction = Deduction::where('employeeId', $employee->id)->first();

            if ($deduction) {
                $deduction->update([
                    'ApartmentId' => $data['ApartmentId'] ?? null,
                    'SimId' => $data['SimId'] ?? null,
                    'editedBy' => auth()->id(),
                ]);
            } elseif (!empty($data['ApartmentId']) || !empty($data['SimId'])) {
                Deduction::create([
                    'employeeId' => $employee->id,
                    'ApartmentId' => $data['ApartmentId'] ?? null,
                    'SimId' => $data['SimId'] ?? null,
                    'createdBy' => auth()->id(),
                    'editedBy' => null,
                ]);
            }

            // Taxes
            $tax = EmployeeTax::where('employeeId', $employee->id)->first();

            if ($tax) {
                $tax->update([
                    'taxesId' => $data['taxesId'] ?? null,
                    'editedBy' => auth()->id(),
                ]);
            } elseif (!empty($data['taxesId'])) {
                EmployeeTax::create([
                    'employeeId' => $employee->id,
                    'taxesId' => $data['taxesId'],
                    'createdBy' => auth()->id(),
                    'editedBy' => auth()->id(),
                ]);
            }

            return $employee;
        });
    }

    public function delete(Employee $employee)
    {
        return DB::transaction(function () use ($employee) {

            EmployeeTax::where('employeeId', $employee->id)->delete();
            Deduction::where('employeeId', $employee->id)->delete();

            $employee->skills()->detach();

            $employee->delete();
        });
    }
    public function deleteDeduction(int $storeId, int $employeeId): void
    {
        DB::transaction(function () use ($storeId, $employeeId) {
            $employee = Employee::where('store_id', $storeId)
                ->findOrFail($employeeId);

            $deduction = Deduction::where('employeeId', $employee->id)
                ->firstOrFail();

            $deduction->delete();
        });
    }
   
}