<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Authorization\Permissions;
use App\Models\ActivityLog;
use App\Services\Operations\ActivityLogQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends OperationsController
{
    public function __construct(private readonly ActivityLogQueryService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize(Permissions::ACTIVITY_VIEW);

        return $this->handleOperation(function () use ($request): JsonResponse {
            $paginator = $this->service->paginate(
                $request->user(),
                $request->only(['event_id', 'event_request_id', 'final_proposal_id']),
                $this->perPage($request),
            );

            return $this->paginatedJson($paginator, fn (ActivityLog $item) => $this->service->serialize($item));
        });
    }
}
