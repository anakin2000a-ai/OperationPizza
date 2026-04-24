<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateScheduleRequest;
use App\Models\Store;
use App\Services\Api\ScheduleAIService;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Http\Request;

class ScheduleAIController extends Controller
{
    protected ScheduleAIService $service;

    public function __construct(ScheduleAIService $service)
    {
        $this->service = $service;
    }
      // Method to get scheduling suggestions without creating the schedules
    public function getSuggestions(Request $request, Store $store): JsonResponse
    {
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        try {
            $suggestions = $this->service->getSuggestions($store->id, $data);

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching suggestions: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generate(Store $store): JsonResponse
    {
        try {
            $data = $this->service->generate(
                $store->id
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