<?php

namespace App\Http\Controllers;

use App\Agent\AgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function __construct(private readonly AgentService $agent) {}

    /**
     * Render the chat page.
     */
    public function index(): Response
    {
        return Inertia::render('chat');
    }

    /**
     * Send the conversation to Groq and return the assistant's reply.
     */
    public function send(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'messages' => ['required', 'array', 'min:1'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:8000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        // Keep only role + content; the client may attach UI-only fields
        // (e.g. which tools were used) that the LLM API rejects.
        $messages = $request->input('messages');
        $history = [];

        foreach (is_array($messages) ? $messages : [] as $message) {
            if (is_array($message)) {
                $history[] = [
                    'role' => (string) ($message['role'] ?? ''),
                    'content' => (string) ($message['content'] ?? ''),
                ];
            }
        }

        $result = $this->agent->run($history);

        return response()->json($result);
    }
}
