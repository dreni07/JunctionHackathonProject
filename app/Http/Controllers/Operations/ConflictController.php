<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Authorization\Permissions;
use App\Http\Requests\Operations\ResolveConflictRequest;
use App\Models\Conflict;
use App\Services\Operations\ConflictManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConflictController extends OperationsController
{
    public function __construct(private readonly ConflictManagementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize(Permissions::CONFLICTS_VIEW);

        return $this->handleOperation(function () use ($request): JsonResponse {
            $paginator = $this->service->paginate(
                $request->user(),
                $request->only(['status']),
                $this->perPage($request),
            );

            return $this->paginatedJson($paginator, fn (Conflict $item) => $this->service->serialize($item));
        });
    }

    public function show(Request $request, Conflict $conflict): JsonResponse
    {
        $this->authorize(Permissions::CONFLICTS_VIEW);

        return $this->json($this->service->serialize($this->service->find($request->user(), $conflict)));
    }

    public function resolve(ResolveConflictRequest $request, Conflict $conflict): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_MANAGE);

        return $this->handleOperation(function () use ($request, $conflict): JsonResponse {
            $model = $this->service->resolve($request->user(), $conflict, $request->validated());

            return $this->json($this->service->serialize($model));
        });
    }
}
