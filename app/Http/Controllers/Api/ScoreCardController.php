<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Http\Requests\CreateScoreCardRequest;
use App\Http\Requests\UpdateScoreCardRequest;
use App\Models\Store;
use App\Services\Api\ScoreCardService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Throwable;

class ScoreCardController extends Controller
{
    public function __construct(
        protected ScoreCardService $service
    ) {}

    public function create(CreateScoreCardRequest $request, $store): JsonResponse
    {
        try {
            $storeModel = \App\Models\Store::where('store', $store)->firstOrFail();
            $data = $this->service->create(
                $request->validated()['schedule_week_id'],
                $storeModel->id
            );

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create score cards',
                'error' => $e->getMessage()
            ], 422);
        }
    }
  

     public function index(string $store): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->service->index($store)
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch score cards',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $store, int $id): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->service->show($store, $id)
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'Not found'
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch score card',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function softDelete(string $store, int $id): JsonResponse
    {
        try {
            $this->service->softDelete($store, $id);

            return response()->json([
                'success' => true,
                'message' => 'Deleted successfully'
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function restore(string $store, int $id): JsonResponse
    {
        try {
            $this->service->restore($store, $id);

            return response()->json([
                'success' => true,
                'message' => 'Restored successfully'
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function forceDelete(string $store, int $id): JsonResponse
    {
        try {
            $this->service->forceDelete($store, $id);

            return response()->json([
                'success' => true,
                'message' => 'Force deleted successfully'
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}