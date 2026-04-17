<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\Employee;
use App\Models\Store;
use App\Services\Api\EmployeeService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function store(StoreEmployeeRequest $request, Store $store): JsonResponse
    {
        try {
            $employee = $this->employeeService->create(
                $request->validated(),
                $store
            );

            return response()->json([
                'success' => true,
                'data' => $employee
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to create employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateEmployeeRequest $request, Store $store, Employee $employee): JsonResponse
    {
        try {
             if ($employee->store_id != $store->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee does not belong to this store'
                ], 404);
            }
            $employee = $this->employeeService->update(
                $request->validated(),
                $employee
            );

            return response()->json([
                'success' => true,
                'data' => $employee
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to update employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Store $store): JsonResponse
    {
        try {
            $employees = Employee::where('store_id', $store->id)
                ->orderBy('FirstName')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $employees
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch employees',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Store $store, Employee $employee): JsonResponse
    {
        try {
            if ($employee->store_id != $store->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee does not belong to this store'
                ], 404);
            }
            $employee->load([
                'taxes',
                'deductions',
                'skills',
                
            ]);

            return response()->json([
                'success' => true,
                'data' => $employee
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy(Store $store, Employee $employee): JsonResponse
    {
        try {
            if ($employee->store_id != $store->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee does not belong to this store'
                ], 404);
            }

            $this->employeeService->delete($employee);

            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to delete employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroyDeductions(Store $store, int $employeeId): JsonResponse
    {
        try {
            $this->employeeService->deleteDeduction($store->id, $employeeId);

            return response()->json([
                'success' => true,
                'message' => 'Deduction deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee or deduction not found in this store'
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete deduction',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
     
}