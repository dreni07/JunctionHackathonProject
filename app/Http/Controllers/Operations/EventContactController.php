<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Authorization\Permissions;
use App\Http\Requests\Operations\StoreEventContactRequest;
use App\Http\Requests\Operations\UpdateEventContactRequest;
use App\Models\EventContact;
use App\Models\EventRequest;
use App\Services\Operations\EventContactManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventContactController extends OperationsController
{
    public function __construct(private readonly EventContactManagementService $service) {}

    public function index(Request $request, EventRequest $eventRequest): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_VIEW);

        return $this->handleOperation(function () use ($request, $eventRequest): JsonResponse {
            $contacts = $this->service->listForRequest($request->user(), $eventRequest);

            return $this->json($contacts->map(fn (EventContact $c) => $this->service->serialize($c))->values()->all());
        });
    }

    public function store(StoreEventContactRequest $request, EventRequest $eventRequest): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_VIEW);

        return $this->handleOperation(function () use ($request, $eventRequest): JsonResponse {
            $contact = $this->service->create($request->user(), $eventRequest, $request->validated());

            return $this->json($this->service->serialize($contact), 201);
        });
    }

    public function update(UpdateEventContactRequest $request, EventContact $eventContact): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_VIEW);

        return $this->handleOperation(function () use ($request, $eventContact): JsonResponse {
            $contact = $this->service->update($request->user(), $eventContact, $request->validated());

            return $this->json($this->service->serialize($contact));
        });
    }

    public function destroy(Request $request, EventContact $eventContact): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_VIEW);

        return $this->handleOperation(function () use ($request, $eventContact): JsonResponse {
            $this->service->delete($request->user(), $eventContact);

            return response()->json(null, 204);
        });
    }
}
