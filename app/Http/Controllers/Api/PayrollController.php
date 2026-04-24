<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovePayrollRequest;
use App\Http\Requests\CreatePayrollRequest;
use App\Http\Requests\StoreManagerPayrollIndexRequest;
use App\Models\Store;
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
            'data' => $data,
            'message' => 'Payroll created successfully'
        ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    public function indexAll(StoreManagerPayrollIndexRequest $request): JsonResponse
    {
        try {
            $data = $this->payrollService->getPayrolls(
                null, // 👈 NO store restriction
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch payrolls',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function showAll(int $id): JsonResponse
    {
        try {
            $data = $this->payrollService->getPayrollById(null, $id);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Payroll not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
    public function indexByStore(StoreManagerPayrollIndexRequest $request, Store $store): JsonResponse
    {
        try {
            $data = $this->payrollService->getPayrolls(
                $store->id,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch payrolls',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function showByStore(Store $store, int $id): JsonResponse
    {
        try {
            $data = $this->payrollService->getPayrollById($store->id, $id);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Payroll not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    // Approve by Third Shift Store Manager
   
    // public function approveByThirdShiftStoreManager(Store $store, $id): JsonResponse
    // {
    //     try {
    //         if (!is_numeric($id) || (int)$id <= 0) {
    //             return response()->json([
    //                 'message' => 'Invalid payroll id'
    //             ], 422);
    //         }

    //         $this->payrollService->approveByThirdShiftStoreManager((int)$id);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Approved by Third Shift Store Manager'
    //         ]);

    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         return response()->json([
    //             'message' => 'Payroll not found'
    //         ], 404);
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'message' => 'Approval failed',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function approveByThirdShiftStoreManager(ApprovePayrollRequest $request, Store $store): JsonResponse 
    {
        try {
            $this->payrollService->approveByThirdShiftStoreManager(
                (int) $request->route('id'),
                 $request->input('comment')
            );

            return response()->json([
                'success' => true,
                'message' => 'Approved by Third Shift Store Manager'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Payroll not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Approval failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Approve by Senior Manager
    // public function approveBySeniorManager($id): JsonResponse
    // {
    //     try {
    //         $this->payrollService->approveBySeniorManager((int) $id);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Approved by Senior Manager'
    //         ]);
    //     } catch (Throwable $e) {
    //         return response()->json([
    //             'message' => 'Approval failed',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function approveBySeniorManager(ApprovePayrollRequest $request): JsonResponse 
    {
        try {
            $this->payrollService->approveBySeniorManager(
                (int) $request->route('id'), $request->input('comment')
            );

            return response()->json([
                'success' => true,
                'message' => 'Approved by Senior Manager'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Payroll not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Approval failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }   
    public function deleteByStore(Store $store, int $id): JsonResponse
    {
        try {
            $this->payrollService->deletePayroll($store->id, $id);

            return response()->json([
                'success' => true,
                'message' => 'Payroll soft deleted'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Delete failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }
    public function restoreByStore(Store $store, int $id): JsonResponse
    {
        try {
            $this->payrollService->restorePayroll($store->id, $id);

            return response()->json([
                'success' => true,
                'message' => 'Payroll restored'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Restore failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }
    public function forceDeleteByStore(Store $store, int $id): JsonResponse
    {
        try {
            $this->payrollService->forceDeletePayroll($store->id, $id);

            return response()->json([
                'success' => true,
                'message' => 'Payroll permanently deleted'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Force delete failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }
    public function deleteAll(int $id): JsonResponse
    {
        try {
            $this->payrollService->deletePayroll(null, $id);

            return response()->json([
                'success' => true,
                'message' => 'Payroll soft deleted'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Delete failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }
    public function restoreAll(int $id): JsonResponse
    {
        try {
            $this->payrollService->restorePayroll(null, $id);

            return response()->json([
                'success' => true,
                'message' => 'Payroll restored'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Restore failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }
    public function forceDeleteAll(int $id): JsonResponse
    {
        try {
            $this->payrollService->forceDeletePayroll(null, $id);

            return response()->json([
                'success' => true,
                'message' => 'Payroll permanently deleted'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Force delete failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}