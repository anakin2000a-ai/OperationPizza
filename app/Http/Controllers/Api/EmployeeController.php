<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Store;
use App\Services\Api\EmployeeService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService
    ) {
    }

    public function store(StoreEmployeeRequest $request, Store $store): JsonResponse
    {
        try {
            $employee = $this->employeeService->create($request->validated(), $store);

            return response()->json([
                'success' => true,
                'data' => $employee->load(['taxes.tax', 'deductions', 'skills']),
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Failed to create employee', [
                'store_id' => $store->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create employee',
            ], 500);
        }
    }

    public function update(UpdateEmployeeRequest $request, Store $store, int $employeeId): JsonResponse
    {
        try {
            $employee = $this->employeeService->findEmployeeInStore($store->id, $employeeId);

            $employee = $this->employeeService->update($request->validated(), $employee);

            return response()->json([
                'success' => true,
                'data' => $employee->load(['taxes.tax', 'deductions', 'skills']),
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found in this store',
            ], 404);

        } catch (\Throwable $e) {
            Log::error('Failed to update employee', [
                'store_id' => $store->id,
                'employee_id' => $employeeId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update employee',
            ], 500);
        }
    }

    public function index(Store $store): JsonResponse
    {
        try {
            $perPage = min((int) request('per_page', 10), 50);

            $employees = \App\Models\Employee::where('store_id', $store->id)
                ->with(['taxes.tax', 'deductions', 'skills'])
                ->orderBy('FirstName', 'asc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $employees,
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to fetch employees', [
                'store_id' => $store->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employees',
            ], 500);
        }
    }

    public function show(Store $store, int $employeeId): JsonResponse
    {
        try {
            $employee = $this->employeeService->findEmployeeInStore($store->id, $employeeId);

            $employee->load([
                'taxes.tax',
                'deductions.apartment',
                'deductions.sim',
                'skills',
            ]);

            return response()->json([
                'success' => true,
                'data' => $employee,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found in this store',
            ], 404);

        } catch (\Throwable $e) {
            Log::error('Failed to fetch employee', [
                'store_id' => $store->id,
                'employee_id' => $employeeId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employee',
            ], 500);
        }
    }

    public function destroy(Store $store, int $employeeId): JsonResponse
    {
        try {
            $employee = $this->employeeService->findEmployeeInStore($store->id, $employeeId);

            $this->employeeService->delete($employee);

            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully',
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found in this store',
            ], 404);

        } catch (\Throwable $e) {
            Log::error('Failed to delete employee', [
                'store_id' => $store->id,
                'employee_id' => $employeeId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete employee',
            ], 500);
        }
    }

    public function destroyDeductions(Store $store, int $employeeId): JsonResponse
    {
        try {
            $this->employeeService->deleteDeductionByEmployeeId($store->id, $employeeId);

            return response()->json([
                'success' => true,
                'message' => 'Employee deductions deleted successfully',
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee or deductions not found in this store',
            ], 404);

        } catch (\Throwable $e) {
            Log::error('Failed to delete employee deductions', [
                'store_id' => $store->id,
                'employee_id' => $employeeId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete employee deductions',
            ], 500);
        }
    }
}