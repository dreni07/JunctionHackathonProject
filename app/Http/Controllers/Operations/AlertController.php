<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Authorization\Permissions;
use App\Enums\AlertCategory;
use App\Enums\RiskLevel;
use App\Models\Alert;
use App\Services\Operations\AlertManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AlertController extends OperationsController
{
    public function __construct(private readonly AlertManagementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_VIEW);

        return $this->handleOperation(function () use ($request): JsonResponse {
            $paginator = $this->service->paginate(
                $request->user(),
                $request->only(['status', 'severity', 'category', 'mine']),
                $this->perPage($request),
            );

            return $this->paginatedJson($paginator, fn (Alert $item) => $this->service->serialize($item));
        });
    }

    /**
     * Raise a new alert (any worker may flag an issue).
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_VIEW);

        return $this->handleOperation(function () use ($request): JsonResponse {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:160'],
                'message' => ['required', 'string', 'max:4000'],
                'severity' => ['required', Rule::enum(RiskLevel::class)],
                'category' => ['nullable', Rule::enum(AlertCategory::class)],
                'event_id' => ['nullable', 'uuid', 'exists:events,id'],
                'space_ids' => ['array', 'max:20'],
                'space_ids.*' => ['uuid', 'exists:spaces,id'],
            ]);

            $alert = $this->service->create($request->user(), $validated);

            return $this->json($this->service->serialize($alert), 201);
        });
    }

    public function resolve(Request $request, Alert $alert): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_VIEW);

        return $this->handleOperation(function () use ($request, $alert): JsonResponse {
            $model = $this->service->resolve($request->user(), $alert);

            return $this->json($this->service->serialize($model));
        });
    }

    public function markRead(Request $request, Alert $alert): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_VIEW);

        return $this->handleOperation(function () use ($request, $alert): JsonResponse {
            $model = $this->service->markAsRead($request->user(), $alert);

            return $this->json($this->service->serialize($model));
        });
    }

    public function dismiss(Request $request, Alert $alert): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_VIEW);

        return $this->handleOperation(function () use ($request, $alert): JsonResponse {
            $model = $this->service->dismiss($request->user(), $alert);

            return $this->json($this->service->serialize($model));
        });
    }
}
