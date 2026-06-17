<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OperationalChange;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperationalChangePollController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $since = $request->string('since')->toString();

        $query = OperationalChange::query()
            ->orderBy('occurred_at')
            ->orderBy('id');

        if ($since !== '') {
            $query->where('id', '>', $since);
        }

        $changes = $query
            ->limit(50)
            ->get()
            ->map(fn (OperationalChange $change): array => [
                'id' => $change->id,
                'model_type' => $change->model_type,
                'model_id' => $change->model_id,
                'action' => $change->action,
                'summary' => $change->summary,
                'payload' => $change->payload,
                'occurred_at' => $change->occurred_at?->toIso8601String(),
            ])
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'changes' => $changes,
            'last_id' => $changes !== [] ? $changes[array_key_last($changes)]['id'] : $since,
        ]);
    }
}
