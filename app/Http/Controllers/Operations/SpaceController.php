<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Authorization\Permissions;
use App\Models\Space;
use App\Services\Operations\SpaceCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpaceController extends OperationsController
{
    public function __construct(private readonly SpaceCatalogService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize(Permissions::SPACES_VIEW);

        return $this->handleOperation(function () use ($request): JsonResponse {
            $paginator = $this->service->paginate(
                $request->user(),
                $request->only(['zone', 'search']),
                $this->perPage($request),
            );

            return $this->paginatedJson($paginator, fn (Space $item) => $this->service->serialize($item));
        });
    }

    public function show(Request $request, Space $space): JsonResponse
    {
        $this->authorize(Permissions::SPACES_VIEW);

        return $this->handleOperation(function () use ($request, $space): JsonResponse {
            $model = $this->service->find($request->user(), $space);

            return $this->json($this->service->serialize($model));
        });
    }
}
