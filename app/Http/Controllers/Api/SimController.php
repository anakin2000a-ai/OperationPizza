<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SimRequestStore;
use App\Http\Requests\SimRequestUpdate;
use App\Services\Api\SimService;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class SimController extends Controller
{
    public function __construct(protected SimService $simService)
    {
    }

    public function store(SimRequestStore $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['createdBy'] = auth()->id();

            $sim = $this->simService->create($data);

            return response()->json([
                'success' => true,
                'message' => 'SIM card created successfully!',
                'data' => $sim,
            ], 201);

        } catch (Throwable $e) {
            Log::error('Error creating SIM card', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating SIM card',
            ], 500);
        }
    }

    public function update(SimRequestUpdate $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['editedBy'] = auth()->id();

            $sim = $this->simService->update($id, $data);

            return response()->json([
                'success' => true,
                'message' => 'SIM card updated successfully!',
                'data' => $sim,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'SIM card not found',
            ], 404);
        } catch (Throwable $e) {
            Log::error('Error updating SIM card', [
                'user_id' => auth()->id(),
                'sim_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating SIM card',
            ], 500);
        }
    }

    public function index(): JsonResponse
    {
        try {
            $perPage = min((int) request('per_page', 10), 50);
            $sims = $this->simService->index($perPage);

            return response()->json([
                'success' => true,
                'data' => $sims,
            ]);
        } catch (Throwable $e) {
            Log::error('Error fetching SIM cards', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching SIM cards',
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->simService->softDelete($id);

            return response()->json([
                'success' => true,
                'message' => 'SIM card soft-deleted successfully!',
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'SIM card not found',
            ], 404);
        } catch (Throwable $e) {
            Log::error('Error deleting SIM card', [
                'user_id' => auth()->id(),
                'sim_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting SIM card',
            ], 500);
        }
    }

    public function forceDelete(int $id): JsonResponse
    {
        try {
            $this->simService->forceDelete($id);

            return response()->json([
                'success' => true,
                'message' => 'SIM card permanently deleted!',
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'SIM card not found',
            ], 404);
        } catch (Throwable $e) {
            Log::error('Error force-deleting SIM card', [
                'user_id' => auth()->id(),
                'sim_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error force-deleting SIM card',
            ], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $sim = $this->simService->restore($id);

            return response()->json([
                'success' => true,
                'message' => 'SIM card restored successfully!',
                'data' => $sim,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'SIM card not found',
            ], 404);
        } catch (Throwable $e) {
            Log::error('Error restoring SIM card', [
                'user_id' => auth()->id(),
                'sim_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error restoring SIM card',
            ], 500);
        }
    }
}