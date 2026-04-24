<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShiftRequirementRequest;
use App\Http\Requests\UpdateShiftRequirementRequest;
use App\Models\Store;
use App\Services\Api\ShiftRequirementService;
use Illuminate\Http\JsonResponse;
use Throwable;

class ShiftRequirementController extends Controller
{
    public function __construct(
        private ShiftRequirementService $service
    ) {}
   public function indexByStore(Store $store): JsonResponse
    {
        try {
            $data = $this->service->index($store->id);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch shift requirements',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function store(StoreShiftRequirementRequest $request): JsonResponse
    {
        try {
            $data = $this->service->store($request->validated());

            return response()->json([
                'success' => true,
                'data' => $data
            ], 201);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Store $store,int $id): JsonResponse
    {
        try {
            $data = $this->service->show($id);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Not found'
            ], 404);
        }
    }

    public function update(UpdateShiftRequirementRequest $request,Store $store, int $id): JsonResponse
    {
        try {
            $data = $this->service->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Store $store, int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Deleted successfully'
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Delete failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}