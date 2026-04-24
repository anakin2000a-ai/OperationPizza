<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateScheduleRequest;
use App\Models\Store;
use App\Services\Api\ScheduleAIService;
use Illuminate\Http\JsonResponse;
use Throwable;

class ScheduleAIController extends Controller
{
    protected ScheduleAIService $service;

    public function __construct(ScheduleAIService $service)
    {
        $this->service = $service;
    }

    public function generate(GenerateScheduleRequest $request, Store $store): JsonResponse
    {
        try {
            $data = $this->service->generate(
                $store->id,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Schedule generated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Generation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}