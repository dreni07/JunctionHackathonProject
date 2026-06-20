<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Http\Requests\Operations\StoreTenantExpenseRequest;
use App\Http\Requests\Operations\StoreTenantPaymentRequest;
use App\Http\Requests\Operations\UpdateTenantFinanceProfileRequest;
use App\Services\Operations\TenantFinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantFinanceController extends OperationsController
{
    public function __construct(private readonly TenantFinanceService $service) {}

    public function index(Request $request): JsonResponse
    {
        return $this->handleOperation(function () use ($request): JsonResponse {
            return $this->json($this->service->dashboard($request->user()));
        });
    }

    public function updateProfile(UpdateTenantFinanceProfileRequest $request): JsonResponse
    {
        return $this->handleOperation(function () use ($request): JsonResponse {
            $profile = $this->service->updateProfile(
                $request->user(),
                $request->validated(),
            );

            return $this->json($this->service->serializeProfile($profile));
        });
    }

    public function storePayment(StoreTenantPaymentRequest $request): JsonResponse
    {
        return $this->handleOperation(function () use ($request): JsonResponse {
            $payment = $this->service->recordPayment(
                $request->user(),
                $request->validated(),
            );

            return $this->json([
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'method' => $payment->method->value,
                'paid_at' => $payment->paid_at?->toIso8601String(),
            ], 201);
        });
    }

    public function storeExpense(StoreTenantExpenseRequest $request): JsonResponse
    {
        return $this->handleOperation(function () use ($request): JsonResponse {
            $expense = $this->service->recordExpense(
                $request->user(),
                $request->validated(),
            );

            return $this->json($this->service->serializeExpense($expense), 201);
        });
    }
}
