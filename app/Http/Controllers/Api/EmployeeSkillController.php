<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeSkillRequest;
use App\Http\Requests\UpdateEmployeeSkillRequest;
use App\Services\Api\EmployeeSkillService;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class EmployeeSkillController extends Controller
{
    protected EmployeeSkillService $service;

    public function __construct(EmployeeSkillService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        try {
            $data = $this->service->getAll();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreEmployeeSkillRequest $request): JsonResponse
    {
        try {
            $data = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Created successfully',
                'data' => $data
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = $this->service->getById($id);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Not found'
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateEmployeeSkillRequest $request, int $id): JsonResponse
    {
        try {
            $record = $this->service->getById($id);
            $updated = $this->service->update($record, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Updated successfully',
                'data' => $updated
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Not found'
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $record = $this->service->getById($id);
            $this->service->delete($record);

            return response()->json([
                'success' => true,
                'message' => 'Deleted successfully'
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Not found'
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}