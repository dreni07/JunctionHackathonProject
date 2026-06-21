<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Authorization\Permissions;
use App\Http\Requests\Operations\UpdateEventRequest as UpdateEventFormRequest;
use App\Models\Event;
use App\Services\Operations\EventManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends OperationsController
{
    public function __construct(private readonly EventManagementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize(Permissions::EVENTS_VIEW);

        $paginator = $this->service->paginate(
            $request->user(),
            $request->only(['status', 'search']),
            $this->perPage($request),
        );

        return $this->paginatedJson($paginator, fn (Event $item) => $this->service->serialize($item));
    }

    public function show(Request $request, Event $event): JsonResponse
    {
        $this->authorize(Permissions::EVENTS_VIEW);

        return $this->handleOperation(function () use ($request, $event): JsonResponse {
            $model = $this->service->find($request->user(), $event);

            return $this->json($this->service->serialize($model));
        });
    }

    public function update(UpdateEventFormRequest $request, Event $event): JsonResponse
    {
        $this->authorize(Permissions::EVENTS_MANAGE);

        return $this->handleOperation(function () use ($request, $event): JsonResponse {
            $model = $this->service->update($request->user(), $event, $request->validated());

            return $this->json($this->service->serialize($model));
        });
    }
}
