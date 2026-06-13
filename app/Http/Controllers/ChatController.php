<?php

namespace App\Http\Controllers;

use App\Services\GroqService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    private const SYSTEM_PROMPT = 'You are a helpful study and research assistant. Answer clearly and concisely.';

    public function __construct(private readonly GroqService $groq) {}

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

        /** @var list<array{role: string, content: string}> $history */
        $history = $request->input('messages');

        $messages = array_merge(
            [['role' => 'system', 'content' => self::SYSTEM_PROMPT]],
            $history,
        );

        $reply = $this->groq->chat($messages);

        return response()->json([
            'reply' => $reply,
        ]);
    }
}
