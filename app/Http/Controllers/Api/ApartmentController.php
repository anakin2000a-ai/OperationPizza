<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApartmentRequest;
use App\Http\Requests\ApartmentRequestUpdate;
use App\Services\Api\ApartmentService;
use Illuminate\Http\Request;
use Exception;
class ApartmentController extends Controller
{
    protected $apartmentService;

    public function __construct(ApartmentService $apartmentService)
    {
        $this->apartmentService = $apartmentService;
    }

    // Store a new apartment
    public function store(ApartmentRequest $request)
    {
        try {
            // Get validated data
            $data = $request->validated();

            // Add the 'createdBy' field (authenticated user's ID)
            $data['createdBy'] = auth()->id();

            // Create the apartment using the service
            $apartment = $this->apartmentService->create($data);

            return response()->json(['message' => 'Apartment created successfully!', 'data' => $apartment], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error creating apartment', 'error' => $e->getMessage()], 500);
        }
    }

    // Show an apartment by ID
    public function show($id)
    {
        try {
            $apartment = $this->apartmentService->find($id);
            return response()->json(['data' => $apartment], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Apartment not found', 'error' => $e->getMessage()], 404);
        }
    }

    // Update an apartment
    public function update(ApartmentRequestUpdate $request, $id)
    {
        try {
            // Get validated data
            $data = $request->validated();

            // Add the 'editedBy' field (authenticated user's ID)
            $data['editedBy'] = auth()->id();

            // Update apartment using the service
            $apartment = $this->apartmentService->update($id, $data);

            return response()->json(['message' => 'Apartment updated successfully!', 'data' => $apartment], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error updating apartment', 'error' => $e->getMessage()], 500);
        }
    }

    // Soft delete an apartment
    public function destroy($id)
    {
        try {
            $this->apartmentService->softDelete($id);
            return response()->json(['message' => 'Apartment soft deleted successfully!'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error deleting apartment', 'error' => $e->getMessage()], 500);
        }
    }

    // Restore a soft-deleted apartment
    public function restore($id)
    {
        try {
            $apartment = $this->apartmentService->restore($id);
            return response()->json(['message' => 'Apartment restored successfully!', 'data' => $apartment], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error restoring apartment', 'error' => $e->getMessage()], 500);
        }
    }

    // Permanently delete an apartment
    public function forceDelete($id)
    {
        try {
            $this->apartmentService->forceDelete($id);
            return response()->json(['message' => 'Apartment permanently deleted!'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error permanently deleting apartment', 'error' => $e->getMessage()], 500);
        }
    }

    // Index - Get all apartments, sorted by id
    public function index()
    {
        try {
            $apartments = $this->apartmentService->getAllSortedById();
            return response()->json(['data' => $apartments], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error fetching apartments', 'error' => $e->getMessage()], 500);
        }
    }
}