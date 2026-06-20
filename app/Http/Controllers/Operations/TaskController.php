<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Authorization\Permissions;
use App\Http\Requests\Operations\StoreTaskRequest;
use App\Http\Requests\Operations\UpdateTaskRequest;
use App\Models\Task;
use App\Services\Operations\TaskManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends OperationsController
{
    public function __construct(private readonly TaskManagementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize(Permissions::TASKS_VIEW);

        return $this->handleOperation(function () use ($request): JsonResponse {
            $paginator = $this->service->paginate(
                $request->user(),
                $request->only(['event_id', 'state', 'phase']),
                $this->perPage($request),
            );

            return $this->paginatedJson($paginator, fn (Task $item) => $this->service->serialize($item));
        });
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        $this->authorize(Permissions::TASKS_VIEW);

        return $this->handleOperation(function () use ($request, $task): JsonResponse {
            $model = $this->service->find($request->user(), $task);

            return $this->json($this->service->serialize($model));
        });
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $this->authorize(Permissions::TASKS_MANAGE);

        return $this->handleOperation(function () use ($request): JsonResponse {
            $task = $this->service->create($request->user(), $request->validated());

            return $this->json($this->service->serialize($task), 201);
        });
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize(Permissions::TASKS_MANAGE);

        return $this->handleOperation(function () use ($request, $task): JsonResponse {
            $model = $this->service->update($request->user(), $task, $request->validated());

            return $this->json($this->service->serialize($model));
        });
    }
}
