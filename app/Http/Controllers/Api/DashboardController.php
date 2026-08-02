<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    #[OA\Get(
        path: '/api/dashboard',
        summary: 'Aggregated statistics for the authenticated user',
        security: [['sanctum' => []]],
        tags: ['Dashboard'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Dashboard summary',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'total_projects', type: 'integer', example: 6),
                                new OA\Property(property: 'active_projects', type: 'integer', example: 4),
                                new OA\Property(property: 'total_tasks', type: 'integer', example: 42),
                                new OA\Property(property: 'completed_tasks', type: 'integer', example: 10),
                                new OA\Property(property: 'pending_tasks', type: 'integer', example: 32),
                                new OA\Property(property: 'overdue_tasks', type: 'integer', example: 5),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $summary = $this->dashboard->summaryFor($request->user());

        return $this->success($summary, 'Dashboard summary retrieved.');
    }
}
