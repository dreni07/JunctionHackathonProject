<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Authorization\Permissions;
use App\Http\Requests\Operations\UpdateEventRequestStatusRequest;
use App\Models\EventRequest;
use App\Services\Operations\EventManagementService;
use App\Services\Operations\EventRequestManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventRequestController extends OperationsController
{
    public function __construct(
        private readonly EventRequestManagementService $service,
        private readonly EventManagementService $events,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_VIEW);

        $paginator = $this->service->paginate(
            $request->user(),
            $request->only(['status', 'search']),
            $this->perPage($request),
        );

        return $this->paginatedJson($paginator, fn (EventRequest $item) => $this->service->serialize($item));
    }

    public function show(Request $request, EventRequest $eventRequest): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_VIEW);

        return $this->handleOperation(function () use ($request, $eventRequest): JsonResponse {
            $model = $this->service->find($request->user(), $eventRequest);

            return $this->json($this->service->serialize($model));
        });
    }

    public function update(UpdateEventRequestStatusRequest $request, EventRequest $eventRequest): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_MANAGE);

        return $this->handleOperation(function () use ($request, $eventRequest): JsonResponse {
            $model = $this->service->updateStatus($request->user(), $eventRequest, $request->validated());

            return $this->json($this->service->serialize($model));
        });
    }

    public function convert(Request $request, EventRequest $eventRequest): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_MANAGE);

        return $this->handleOperation(function () use ($request, $eventRequest): JsonResponse {
            $event = $this->service->convertToEvent($request->user(), $eventRequest);

            return $this->json($this->events->serialize($event), 201);
        });
    }

    /**
     * Decline a request, with a reason the organizer is told (and that is
     * saved for the agent's learning set).
     */
    public function reject(Request $request, EventRequest $eventRequest): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_MANAGE);

        return $this->handleOperation(function () use ($request, $eventRequest): JsonResponse {
            $validated = $request->validate([
                'reason' => ['required', 'string', 'min:3', 'max:1000'],
            ]);

            $model = $this->service->reject($request->user(), $eventRequest, $validated['reason']);

            return $this->json($this->service->serialize($model));
        });
    }
}
