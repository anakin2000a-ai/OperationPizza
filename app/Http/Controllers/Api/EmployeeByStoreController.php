<?php

namespace App\Http\Controllers\Api;

use Throwable;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetEmployeesByStoreRequest;
use App\Services\Api\getEmployee;

class EmployeeByStoreController extends Controller
{
    protected getEmployee $service;

    public function __construct(getEmployee $service)
    {
        $this->service = $service;
    }
    public function employeesByStore(GetEmployeesByStoreRequest $request): JsonResponse
    {
        
        try {
            $data = $this->service->getEmployeesByStore(
                $request->validated()['store_id']
            );

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch employees',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}