<?php

namespace App\Http\Controllers;

use App\Services\EventRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class EventRequestController extends Controller
{
    public function __construct(private readonly EventRequestService $service) {}

    /**
     * Create an event request from gathered details. This is the endpoint the
     * agent's api_tool ultimately fulfils when the user confirms submission.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'event_type' => ['required', 'string'],
            'description' => ['required', 'string'],
            'attendees' => ['required', 'integer', 'min:1'],
            'preferred_start_at' => ['required', 'date'],
            'preferred_end_at' => ['required', 'date', 'after:preferred_start_at'],
            'raw_intake' => ['nullable', 'string'],
        ]);

        try {
            $eventRequest = $this->service->create($validated, $validated['raw_intake'] ?? null);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'event_request' => $eventRequest->only([
                'id', 'title', 'event_type', 'attendees',
                'preferred_start_at', 'preferred_end_at', 'status',
            ]),
        ], 201);
    }
}
