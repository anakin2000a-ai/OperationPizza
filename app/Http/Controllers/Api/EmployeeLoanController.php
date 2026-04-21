<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeLoanDeleteRequest;
use App\Http\Requests\EmployeeLoanStoreRequest;
use App\Services\Api\EmployeeLoanService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmployeeLoanController extends Controller
{
    public function __construct(
        private EmployeeLoanService $service
    ) {}

    public function index(): JsonResponse
    {
        try {
            $data = $this->service->getAll(
                min((int) request('per_page', 10), 50)
            );

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);

        } catch (Throwable $e) {
            Log::error('Failed to fetch employee loans', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employee loans',
            ], 500);
        }
    }

    public function store(EmployeeLoanStoreRequest $request): JsonResponse
    {
        try {
            $data = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Created successfully',
                'data' => $data,
            ], 201);

        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (Throwable $e) {
            Log::error('Failed to create employee loan', [
                'user_id' => auth()->id(),
                'payload' => $request->validated(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create employee loan',
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = $this->service->find($id);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee loan not found',
            ], 404);

        } catch (Throwable $e) {
            Log::error('Failed to fetch employee loan', [
                'loan_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employee loan',
            ], 500);
        }
    }

    public function destroy(EmployeeLoanDeleteRequest $request, int $id): JsonResponse
    {
        try {
            $this->service->softDelete(
                $id,
                $request->validated('ReasonForDeletion')
            );

            return response()->json([
                'success' => true,
                'message' => 'Deleted successfully',
            ]);

        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee loan not found',
            ], 404);

        } catch (Throwable $e) {
            Log::error('Failed to delete employee loan', [
                'loan_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete employee loan',
            ], 500);
        }
    }
}