<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SimRequestStore;
use App\Http\Requests\SimRequestUpdate;
use App\Services\Api\SimService;
use Illuminate\Http\Request;
use Exception;

class SimController extends Controller
{
    protected $simService;

    public function __construct(SimService $simService)
    {
        $this->simService = $simService;
    }

    // Store a new SIM card
    public function store(SimRequestStore $request)
    {
        try {
            $data = $request->validated();
            $data['createdBy'] = auth()->id();  // Set createdBy with authenticated user's ID
            $sim = $this->simService->create($data);
            return response()->json(['message' => 'SIM card created successfully!', 'data' => $sim], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error creating SIM card', 'error' => $e->getMessage()], 500);
        }
    }

    // Update an existing SIM card
    public function update(SimRequestUpdate $request, $id)
    {
        try {
            $data = $request->validated();
            $data['editedBy'] = auth()->id();  // Set editedBy with authenticated user's ID
            $sim = $this->simService->update($id, $data);
            return response()->json(['message' => 'SIM card updated successfully!', 'data' => $sim], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error updating SIM card', 'error' => $e->getMessage()], 500);
        }
    }

    // Index method to list all SIM cards
    public function index()
    {
        try {
            $sims = $this->simService->index();
            return response()->json(['data' => $sims], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error fetching SIM cards', 'error' => $e->getMessage()], 500);
        }
    }

    // Soft delete a SIM card
    public function destroy($id)
    {
        try {
            $this->simService->softDelete($id);
            return response()->json(['message' => 'SIM card soft-deleted successfully!'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error deleting SIM card', 'error' => $e->getMessage()], 500);
        }
    }

    // Force delete a SIM card
    public function forceDelete($id)
    {
        try {
            $this->simService->forceDelete($id);
            return response()->json(['message' => 'SIM card permanently deleted!'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error force-deleting SIM card', 'error' => $e->getMessage()], 500);
        }
    }

    // Restore a SIM card
    public function restore($id)
    {
        try {
            $this->simService->restore($id);
            return response()->json(['message' => 'SIM card restored successfully!'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error restoring SIM card', 'error' => $e->getMessage()], 500);
        }
    }
}