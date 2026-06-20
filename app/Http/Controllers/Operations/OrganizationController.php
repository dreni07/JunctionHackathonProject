<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Http\Requests\Operations\UpdateOrganizationRequest;
use App\Models\Organization;
use App\Services\Operations\OrganizationManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends OperationsController
{
    public function __construct(private readonly OrganizationManagementService $service) {}

    public function show(Request $request, Organization $organization): JsonResponse
    {
        return $this->handleOperation(function () use ($request, $organization): JsonResponse {
            $model = $this->service->find($request->user(), $organization);

            return $this->json($this->service->serialize($model));
        });
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization): JsonResponse
    {
        return $this->handleOperation(function () use ($request, $organization): JsonResponse {
            $model = $this->service->update($request->user(), $organization, $request->validated());

            return $this->json($this->service->serialize($model));
        });
    }
}
