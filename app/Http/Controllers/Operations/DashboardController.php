<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Authorization\Permissions;
use App\Services\Operations\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends OperationsController
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function show(Request $request): JsonResponse
    {
        $this->authorize(Permissions::REQUESTS_VIEW);

        return $this->json($this->dashboard->summary($request->user()));
    }
}
