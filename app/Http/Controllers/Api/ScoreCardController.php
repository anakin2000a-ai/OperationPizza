<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Http\Requests\CreateScoreCardRequest;
use App\Http\Requests\UpdateScoreCardRequest;
use App\Services\Api\ScoreCardService;
use Illuminate\Http\JsonResponse;
use Throwable;

class ScoreCardController extends Controller
{
    public function __construct(
        protected ScoreCardService $service
    ) {}

    public function create(CreateScoreCardRequest $request): JsonResponse
    {
        try {
            $data = $this->service->create(
                $request->validated()['schedule_week_id']
            );

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create score cards',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function update(UpdateScoreCardRequest $request, int $id): JsonResponse
    {
        try {
            $data = $this->service->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update score card',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}