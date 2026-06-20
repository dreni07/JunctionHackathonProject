<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Http\Requests\Operations\StoreTenantWorkerRequest;
use App\Models\User;
use App\Services\Operations\TenantFinanceService;
use App\Services\Operations\TenantWorkerProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantWorkerController extends OperationsController
{
    public function __construct(
        private readonly TenantWorkerProvisioningService $service,
        private readonly TenantFinanceService $finance,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->handleOperation(function () use ($request): JsonResponse {
            $manager = $request->user();
            $workers = $this->service->listForManager($manager);

            return $this->json([
                'workers' => $workers
                    ->map(fn (User $worker): array => $this->service->serializeWorker($worker))
                    ->values()
                    ->all(),
                'assignable_roles' => $manager->tenant?->assignableWorkerRoles() ?? [],
                'stats' => $this->finance->teamStats($manager),
            ]);
        });
    }

    public function store(StoreTenantWorkerRequest $request): JsonResponse
    {
        return $this->handleOperation(function () use ($request): JsonResponse {
            $worker = $this->service->create($request->user(), $request->validated());

            return $this->json($this->service->serializeWorker($worker), 201);
        });
    }
}
