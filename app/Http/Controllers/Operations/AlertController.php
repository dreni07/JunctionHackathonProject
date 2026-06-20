<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Authorization\Permissions;
use App\Models\Alert;
use App\Services\Operations\AlertManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends OperationsController
{
    public function __construct(private readonly AlertManagementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_VIEW);

        return $this->handleOperation(function () use ($request): JsonResponse {
            $paginator = $this->service->paginate(
                $request->user(),
                $request->only(['status']),
                $this->perPage($request),
            );

            return $this->paginatedJson($paginator, fn (Alert $item) => $this->service->serialize($item));
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
