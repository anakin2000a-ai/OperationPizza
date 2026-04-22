<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePayrollRequest;
use App\Services\Api\PayrollService;
use Illuminate\Http\JsonResponse;
use Throwable;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    // Create payroll and send to the system
    public function create(CreatePayrollRequest $request): JsonResponse
    {
        try {
            $data = $this->payrollService->createPayroll($request->validated());

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to create payroll',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Approve by Third Shift Store Manager
    public function approveByThirdShiftStoreManager(int $id): JsonResponse
    {
        try {
            $this->payrollService->approveByThirdShiftStoreManager($id);

            return response()->json([
                'success' => true,
                'message' => 'Approved by Third Shift Store Manager'
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Approval failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Approve by Senior Manager
    public function approveBySeniorManager(int $id): JsonResponse
    {
        try {
            $this->payrollService->approveBySeniorManager($id);

            return response()->json([
                'success' => true,
                'message' => 'Approved by Senior Manager'
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Approval failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}