<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoanRequestStore;
use App\Http\Requests\LoanRequestUpdate;
use App\Services\Api\LoanService;
use Illuminate\Http\Request;
use Exception;

class LoanController extends Controller
{
    protected $loanService;

    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
    }

    // Store a new loan
    public function store(LoanRequestStore $request)
    {
        try {
            $data = $request->validated();
            $data['createdBy'] = auth()->id();  // Set the authenticated user for 'createdBy'
            $loan = $this->loanService->create($data);
            return response()->json(['message' => 'Loan created successfully!', 'data' => $loan], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error creating loan', 'error' => $e->getMessage()], 500);
        }
    }

    // Update an existing loan
    public function update(LoanRequestUpdate $request, $id)
    {
        try {
            $data = $request->validated();
            $data['editedBy'] = auth()->id();  // Set the authenticated user for 'editedBy'
            $loan = $this->loanService->update($id, $data);
            return response()->json(['message' => 'Loan updated successfully!', 'data' => $loan], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error updating loan', 'error' => $e->getMessage()], 500);
        }
    }

    // Show a loan by ID
    public function show($id)
    {
        try {
            $loan = $this->loanService->find($id);
            return response()->json(['data' => $loan], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Loan not found', 'error' => $e->getMessage()], 404);
        }
    }

    // Get all loans sorted by loanAmountWithTax
    public function index()
    {
        try {
            $loans = $this->loanService->getAllSortedByLoanAmountWithTax();
            return response()->json(['data' => $loans], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error fetching loans', 'error' => $e->getMessage()], 500);
        }
    }

    // Soft delete a loan
    public function destroy($id)
    {
        try {
            $this->loanService->softDelete($id);
            return response()->json(['message' => 'Loan soft deleted successfully!'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error deleting loan', 'error' => $e->getMessage()], 500);
        }
    }

    // Restore a soft-deleted loan
    public function restore($id)
    {
        try {
            $loan = $this->loanService->restore($id);
            return response()->json(['message' => 'Loan restored successfully!', 'data' => $loan], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error restoring loan', 'error' => $e->getMessage()], 500);
        }
    }

    // Permanently delete a loan
    public function forceDelete($id)
    {
        try {
            $this->loanService->forceDelete($id);
            return response()->json(['message' => 'Loan permanently deleted!'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error permanently deleting loan', 'error' => $e->getMessage()], 500);
        }
    }
}