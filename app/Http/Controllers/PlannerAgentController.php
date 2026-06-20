<?php

namespace App\Http\Controllers;

use App\Agent\EventIntakeAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlannerAgentController extends Controller
{
    public function __construct(private readonly EventIntakeAgent $agent) {}

    /**
     * Run one turn of the voice event-intake agent and return its spoken reply
     * plus any review summary or submission result for the UI.
     */
    public function converse(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'messages' => ['required', 'array', 'min:1'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:8000'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $history = [];

        foreach ($request->input('messages') as $message) {
            if (is_array($message)) {
                $history[] = [
                    'role' => (string) ($message['role'] ?? ''),
                    'content' => (string) ($message['content'] ?? ''),
                ];
            }
        }

        return response()->json($this->agent->run($history));
    }
}
