<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaxRequestStore;
use App\Http\Requests\TaxRequestUpdate;
use App\Services\Api\TaxService;
use Illuminate\Http\Request;
use Exception;

class TaxController extends Controller
{
    protected $taxService;

    public function __construct(TaxService $taxService)
    {
        $this->taxService = $taxService;
    }

    // Store a new tax
    public function store(TaxRequestStore $request)
    {
        try {
            $data = $request->validated();
            $data['createdBy'] = auth()->id();  // Set the authenticated user for 'createdBy'
            $tax = $this->taxService->create($data);
            return response()->json(['message' => 'Tax created successfully!', 'data' => $tax], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error creating tax', 'error' => $e->getMessage()], 500);
        }
    }

    // Update an existing tax
    public function update(TaxRequestUpdate $request, $id)
    {
        try {
            $data = $request->validated();
            $data['editedBy'] = auth()->id();  // Set the authenticated user for 'editedBy'
            $tax = $this->taxService->update($id, $data);
            return response()->json(['message' => 'Tax updated successfully!', 'data' => $tax], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error updating tax', 'error' => $e->getMessage()], 500);
        }
    }

    // Show a tax by ID
    public function show($id)
    {
        try {
            $tax = $this->taxService->find($id);
            return response()->json(['data' => $tax], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Tax not found', 'error' => $e->getMessage()], 404);
        }
    }

    // Index - Get all taxes sorted by created_at
    public function index()
    {
        try {
            $taxes = $this->taxService->getAllSortedByCreatedAt();
            return response()->json(['data' => $taxes], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error fetching taxes', 'error' => $e->getMessage()], 500);
        }
    }

    // Soft delete a tax
    public function destroy($id)
    {
        try {
            $this->taxService->softDelete($id);
            return response()->json(['message' => 'Tax soft deleted successfully!'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error deleting tax', 'error' => $e->getMessage()], 500);
        }
    }

    // Restore a soft-deleted tax
    public function restore($id)
    {
        try {
            $tax = $this->taxService->restore($id);
            return response()->json(['message' => 'Tax restored successfully!', 'data' => $tax], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error restoring tax', 'error' => $e->getMessage()], 500);
        }
    }

    // Permanently delete a tax
    public function forceDelete($id)
    {
        try {
            $this->taxService->forceDelete($id);
            return response()->json(['message' => 'Tax permanently deleted!'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error permanently deleting tax', 'error' => $e->getMessage()], 500);
        }
    }
}