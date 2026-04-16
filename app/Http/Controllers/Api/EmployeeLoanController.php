<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeLoanStoreRequest;
 use App\Services\Api\EmployeeLoanService;
use Illuminate\Http\Request;
use Exception;

class EmployeeLoanController extends Controller
{
    private $service;

    public function __construct(EmployeeLoanService $service)
    {
        $this->service = $service;
    }

    // ✅ INDEX
    public function index()
    {
        try {
            return response()->json($this->service->getAll());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ✅ STORE
    public function store(EmployeeLoanStoreRequest $request)
    {
        try {
            $data = $this->service->create($request->validated());

            return response()->json([
                'message' => 'Created successfully',
                'data' => $data
            ], 201);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ✅ SHOW
    public function show($id)
    {
        try {
            return response()->json($this->service->find($id));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    // ✅ UPDATE
   

    // ✅ SOFT DELETE
    public function destroy(Request $request, $id)
    {
        $request->validate([
            'ReasonForDeletion' => 'required|string'
        ]);

        try {
            $this->service->softDelete($id, $request->ReasonForDeletion);

            return response()->json([
                'message' => 'Deleted successfully'
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}