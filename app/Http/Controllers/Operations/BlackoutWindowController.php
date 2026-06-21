<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Authorization\Permissions;
use App\Http\Requests\Operations\StoreBlackoutWindowRequest;
use App\Http\Requests\Operations\UpdateBlackoutWindowRequest;
use App\Models\BlackoutWindow;
use App\Services\Operations\BlackoutWindowManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlackoutWindowController extends OperationsController
{
    public function __construct(private readonly BlackoutWindowManagementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize(Permissions::SPACES_MANAGE);

        return $this->handleOperation(function () use ($request): JsonResponse {
            $items = $this->service->list($request->user());

            return $this->json($items->map(fn (BlackoutWindow $item): array => [
                'id' => $item->id,
                'scope' => $item->scope,
                'days' => $item->days,
                'start_time' => $item->start_time,
                'end_time' => $item->end_time,
                'reason' => $item->reason,
            ])->values()->all());
        });
    }

    public function store(StoreBlackoutWindowRequest $request): JsonResponse
    {
        $this->authorize(Permissions::SPACES_MANAGE);

        return $this->handleOperation(function () use ($request): JsonResponse {
            $window = $this->service->create($request->user(), $request->validated());

            return $this->json([
                'id' => $window->id,
                'scope' => $window->scope,
                'days' => $window->days,
                'start_time' => $window->start_time,
                'end_time' => $window->end_time,
                'reason' => $window->reason,
            ], 201);
        });
    }

    public function update(UpdateBlackoutWindowRequest $request, BlackoutWindow $blackoutWindow): JsonResponse
    {
        $this->authorize(Permissions::SPACES_MANAGE);

        return $this->handleOperation(function () use ($request, $blackoutWindow): JsonResponse {
            $window = $this->service->update($request->user(), $blackoutWindow, $request->validated());

            return $this->json([
                'id' => $window->id,
                'scope' => $window->scope,
                'days' => $window->days,
                'start_time' => $window->start_time,
                'end_time' => $window->end_time,
                'reason' => $window->reason,
            ]);
        });
    }

    public function destroy(Request $request, BlackoutWindow $blackoutWindow): JsonResponse
    {
        $this->authorize(Permissions::SPACES_MANAGE);

        return $this->handleOperation(function () use ($request, $blackoutWindow): JsonResponse {
            $this->service->delete($request->user(), $blackoutWindow);

            return response()->json(null, 204);
        });
    }
}
