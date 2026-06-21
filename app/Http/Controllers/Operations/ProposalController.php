<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Authorization\Permissions;
use App\Http\Requests\Operations\ApprovalDecisionRequest;
use App\Http\Requests\Operations\StoreProposalRequest;
use App\Http\Requests\Operations\StoreQuotationLineItemRequest;
use App\Http\Requests\Operations\UpdateProposalRequest;
use App\Models\FinalProposal;
use App\Models\QuotationLineItem;
use App\Services\Operations\QuotationManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalController extends OperationsController
{
    public function __construct(private readonly QuotationManagementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize(Permissions::QUOTATIONS_VIEW);

        return $this->handleOperation(function () use ($request): JsonResponse {
            $paginator = $this->service->paginate(
                $request->user(),
                $request->only(['status', 'event_request_id']),
                $this->perPage($request),
            );

            return $this->paginatedJson($paginator, fn (FinalProposal $item) => $this->service->serialize($item));
        });
    }

    public function store(StoreProposalRequest $request): JsonResponse
    {
        $this->authorize(Permissions::QUOTATIONS_CREATE);

        return $this->handleOperation(function () use ($request): JsonResponse {
            $proposal = $this->service->create($request->user(), $request->validated());

            return $this->json($this->service->serialize($proposal), 201);
        });
    }

    public function show(Request $request, FinalProposal $proposal): JsonResponse
    {
        $this->authorize(Permissions::QUOTATIONS_VIEW);

        return $this->handleOperation(function () use ($request, $proposal): JsonResponse {
            $model = $this->service->find($request->user(), $proposal);

            return $this->json($this->service->serialize($model));
        });
    }

    public function update(UpdateProposalRequest $request, FinalProposal $proposal): JsonResponse
    {
        $this->authorize(Permissions::QUOTATIONS_CREATE);

        return $this->handleOperation(function () use ($request, $proposal): JsonResponse {
            $model = $this->service->update($request->user(), $proposal, $request->validated());

            return $this->json($this->service->serialize($model));
        });
    }

    public function storeLineItem(StoreQuotationLineItemRequest $request, FinalProposal $proposal): JsonResponse
    {
        $this->authorize(Permissions::QUOTATIONS_CREATE);

        return $this->handleOperation(function () use ($request, $proposal): JsonResponse {
            $lineItem = $this->service->addLineItem($request->user(), $proposal, $request->validated());

            return $this->json([
                'id' => $lineItem->id,
                'description' => $lineItem->description,
                'category' => $lineItem->category->value,
                'quantity' => $lineItem->quantity,
                'unit_price' => $lineItem->unit_price,
                'total' => $lineItem->total,
            ], 201);
        });
    }

    public function destroyLineItem(Request $request, FinalProposal $proposal, QuotationLineItem $lineItem): JsonResponse
    {
        $this->authorize(Permissions::QUOTATIONS_CREATE);

        return $this->handleOperation(function () use ($request, $proposal, $lineItem): JsonResponse {
            $this->service->removeLineItem($request->user(), $proposal, $lineItem);

            return response()->json(null, 204);
        });
    }

    public function submit(Request $request, FinalProposal $proposal): JsonResponse
    {
        $this->authorize(Permissions::QUOTATIONS_CREATE);

        return $this->handleOperation(function () use ($request, $proposal): JsonResponse {
            $model = $this->service->submit($request->user(), $proposal);

            return $this->json($this->service->serialize($model));
        });
    }

    public function approve(ApprovalDecisionRequest $request, FinalProposal $proposal): JsonResponse
    {
        $this->authorize(Permissions::QUOTATIONS_APPROVE);

        return $this->handleOperation(function () use ($request, $proposal): JsonResponse {
            $model = $this->service->approve($request->user(), $proposal, $request->validated('notes'));

            return $this->json($this->service->serialize($model));
        });
    }

    public function reject(ApprovalDecisionRequest $request, FinalProposal $proposal): JsonResponse
    {
        $this->authorize(Permissions::QUOTATIONS_APPROVE);

        return $this->handleOperation(function () use ($request, $proposal): JsonResponse {
            $model = $this->service->reject($request->user(), $proposal, $request->validated('notes'));

            return $this->json($this->service->serialize($model));
        });
    }
}
