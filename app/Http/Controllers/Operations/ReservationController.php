<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Authorization\Permissions;
use App\Http\Requests\Operations\StoreReservationRequest;
use App\Models\Event;
use App\Models\Reservation;
use App\Services\Operations\ReservationManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends OperationsController
{
    public function __construct(private readonly ReservationManagementService $service) {}

    public function index(Request $request, Event $event): JsonResponse
    {
        $this->authorize(Permissions::RESERVATIONS_VIEW);

        return $this->handleOperation(function () use ($request, $event): JsonResponse {
            $items = $this->service->listForEvent($request->user(), $event);

            return $this->json($items->map(fn (Reservation $item) => $this->service->serialize($item))->values()->all());
        });
    }

    public function store(StoreReservationRequest $request, Event $event): JsonResponse
    {
        $this->authorize(Permissions::RESERVATIONS_MANAGE);

        return $this->handleOperation(function () use ($request, $event): JsonResponse {
            $reservation = $this->service->createForEvent($request->user(), $event, $request->validated());

            return $this->json($this->service->serialize($reservation), 201);
        });
    }

    public function destroy(Request $request, Reservation $reservation): JsonResponse
    {
        $this->authorize(Permissions::RESERVATIONS_MANAGE);

        return $this->handleOperation(function () use ($request, $reservation): JsonResponse {
            $this->service->delete($request->user(), $reservation);

            return response()->json(null, 204);
        });
    }
}
