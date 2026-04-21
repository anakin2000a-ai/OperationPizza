<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoanRequestStore;
use App\Http\Requests\LoanRequestUpdate;
use App\Services\Api\LoanService;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class LoanController extends Controller
{
    public function __construct(
        protected LoanService $loanService
    ) {
    }

    public function index(): JsonResponse
    {
        try {
            $perPage = min(max((int) request('per_page', 10), 1), 50);

            $loans = $this->loanService->getAllSortedByLoanAmountWithTax($perPage);

            return response()->json([
                'success' => true,
                'data' => $loans,
            ], 200);

        } catch (Throwable $e) {
            Log::error('Error fetching loans', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching loans',
            ], 500);
        }
    }

    public function store(LoanRequestStore $request): JsonResponse
    {
        try {
            $loan = $this->loanService->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Loan created successfully!',
                'data' => $loan,
            ], 201);

        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (Throwable $e) {
            Log::error('Error creating loan', [
                'user_id' => auth()->id(),
                'payload' => $request->validated(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating loan',
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $loan = $this->loanService->find($id);

            return response()->json([
                'success' => true,
                'data' => $loan,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Loan not found',
            ], 404);

        } catch (Throwable $e) {
            Log::error('Error fetching loan', [
                'user_id' => auth()->id(),
                'loan_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching loan',
            ], 500);
        }
    }

    public function update(LoanRequestUpdate $request, int $id): JsonResponse
    {
        try {
            $loan = $this->loanService->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Loan updated successfully!',
                'data' => $loan,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Loan not found',
            ], 404);

        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (Throwable $e) {
            Log::error('Error updating loan', [
                'user_id' => auth()->id(),
                'loan_id' => $id,
                'payload' => $request->validated(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating loan',
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->loanService->softDelete($id);

            return response()->json([
                'success' => true,
                'message' => 'Loan soft deleted successfully!',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Loan not found',
            ], 404);

        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (Throwable $e) {
            Log::error('Error deleting loan', [
                'user_id' => auth()->id(),
                'loan_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting loan',
            ], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $loan = $this->loanService->restore($id);

            return response()->json([
                'success' => true,
                'message' => 'Loan restored successfully!',
                'data' => $loan,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Loan not found',
            ], 404);

        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (Throwable $e) {
            Log::error('Error restoring loan', [
                'user_id' => auth()->id(),
                'loan_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error restoring loan',
            ], 500);
        }
    }

    public function forceDelete(int $id): JsonResponse
    {
        try {
            $this->loanService->forceDelete($id);

            return response()->json([
                'success' => true,
                'message' => 'Loan permanently deleted!',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Loan not found',
            ], 404);

        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (Throwable $e) {
            Log::error('Error permanently deleting loan', [
                'user_id' => auth()->id(),
                'loan_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error permanently deleting loan',
            ], 500);
        }
    }
}