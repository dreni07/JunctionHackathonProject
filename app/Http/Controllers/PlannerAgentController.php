<?php

namespace App\Http\Controllers;

use App\Agent\EventAdvisorAgent;
use App\Agent\EventIntakeAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlannerAgentController extends Controller
{
    public function __construct(
        private readonly EventIntakeAgent $intake,
        private readonly EventAdvisorAgent $advisor,
    ) {}

    /**
     * Run one turn of the planner. The conversation begins in "advisor" mode —
     * Aria answers questions about what the Pyramid offers — and switches to
     * "intake" mode (the booking agent) the moment the visitor wants to
     * actually organize an event. The client echoes the returned `mode` back on
     * the next turn so the conversation stays in the right agent.
     */
    public function converse(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'messages' => ['required', 'array', 'min:1'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:8000'],
            'mode' => ['sometimes', 'string', 'in:advisor,intake'],
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

        $mode = (string) $request->input('mode', 'advisor');

        // Already booking: stay with the intake agent.
        if ($mode === 'intake') {
            return response()->json($this->intakeResponse($history));
        }

        // Advising. If the visitor signals they want to book, hand the same
        // conversation straight over to the intake agent for this turn's reply.
        $advice = $this->advisor->run($history);

        if ($advice['escalate']) {
            return response()->json($this->intakeResponse($history, handedOver: true));
        }

        return response()->json([
            'reply' => $advice['reply'],
            'review' => null,
            'submitted' => null,
            'ended' => $advice['ended'],
            'tools_used' => $advice['tools_used'],
            'mode' => 'advisor',
            'handed_over' => false,
        ]);
    }

    /**
     * @param  list<array{role: string, content: string}>  $history
     * @return array<string, mixed>
     */
    private function intakeResponse(array $history, bool $handedOver = false): array
    {
        return [
            ...$this->intake->run($history),
            'mode' => 'intake',
            'handed_over' => $handedOver,
        ];
    }
}
