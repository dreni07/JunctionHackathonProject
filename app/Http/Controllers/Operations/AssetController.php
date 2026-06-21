<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Authorization\Permissions;
use App\Http\Requests\Operations\StoreAssetReservationRequest;
use App\Http\Requests\Operations\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\AssetReservation;
use App\Models\Event;
use App\Services\Operations\AssetManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends OperationsController
{
    public function __construct(private readonly AssetManagementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize(Permissions::INVENTORY_VIEW);

        return $this->handleOperation(function () use ($request): JsonResponse {
            $paginator = $this->service->paginate(
                $request->user(),
                $request->only(['status', 'type']),
                $this->perPage($request),
            );

            return $this->paginatedJson($paginator, fn (Asset $item) => $this->service->serialize($item));
        });
    }

    public function show(Request $request, Asset $asset): JsonResponse
    {
        $this->authorize(Permissions::INVENTORY_VIEW);

        return $this->handleOperation(function () use ($request, $asset): JsonResponse {
            $model = $this->service->find($request->user(), $asset);

            return $this->json($this->service->serialize($model));
        });
    }

    public function update(UpdateAssetRequest $request, Asset $asset): JsonResponse
    {
        $this->authorize(Permissions::INVENTORY_MANAGE);

        return $this->handleOperation(function () use ($request, $asset): JsonResponse {
            $model = $this->service->update($request->user(), $asset, $request->validated());

            return $this->json($this->service->serialize($model));
        });
    }

    public function storeReservation(StoreAssetReservationRequest $request, Event $event): JsonResponse
    {
        $this->authorize(Permissions::INVENTORY_MANAGE);

        return $this->handleOperation(function () use ($request, $event): JsonResponse {
            $reservation = $this->service->reserveForEvent($request->user(), $event, $request->validated());

            return $this->json([
                'id' => $reservation->id,
                'asset_id' => $reservation->asset_id,
                'event_id' => $reservation->event_id,
                'reserved_quantity' => $reservation->reserved_quantity,
                'status' => $reservation->status->value,
            ], 201);
        });
    }

    public function destroyReservation(Request $request, AssetReservation $assetReservation): JsonResponse
    {
        $this->authorize(Permissions::INVENTORY_MANAGE);

        return $this->handleOperation(function () use ($request, $assetReservation): JsonResponse {
            $this->service->releaseReservation($request->user(), $assetReservation);

            return response()->json(null, 204);
        });
    }
}
