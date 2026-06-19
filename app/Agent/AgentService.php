<?php

namespace App\Agent;

use App\Services\GroqService;

class AgentService
{
    private const MAX_STEPS = 6;

    private const SYSTEM_PROMPT = <<<'PROMPT'
        You are a study and research assistant. You help the user understand and explore
        the documents and notes they have uploaded.

        You have tools available:
        - document_search: semantic search over the user's uploaded documents. Prefer this
          for questions about what the user has uploaded.
        - file_search: reads the docs knowledge library on disk (Pyramid spaces, booking
          and approval policies, tenants, event guidelines). Prefer this for questions
          about how the Pyramid works and what events are likely to be approved.
        - db_query: read-only SQL over the documents table for counts, filters, and listings.
        - web_search: the public web, for general or current information not in the documents.

        Use tools when they help. When you have enough information, answer clearly and concisely.
        If a tool returns nothing useful, say so honestly rather than inventing facts.
        PROMPT;

    /**
     * @param  array<string, Tool>  $tools  keyed by tool name
     */
    public function __construct(
        private readonly GroqService $groq,
        private readonly array $tools,
    ) {}

    /**
     * Run the agent loop over a conversation and return the final reply plus
     * the names of any tools that were used.
     *
     * @param  list<array{role: string, content: string}>  $conversation
     * @return array{reply: string, tools_used: list<string>}
     */
    public function run(array $conversation): array
    {
        /** @var list<array<string, mixed>> $messages */
        $messages = array_merge(
            [['role' => 'system', 'content' => self::SYSTEM_PROMPT]],
            $conversation,
        );

        $toolDefinitions = $this->toolDefinitions();
        $toolsUsed = [];

        for ($step = 0; $step < self::MAX_STEPS; $step++) {
            $message = $this->groq->chatWithTools($messages, $toolDefinitions);
            $messages[] = $message;

            $toolCalls = $message['tool_calls'] ?? null;

            if (! is_array($toolCalls) || $toolCalls === []) {
                $content = $message['content'] ?? '';

                return [
                    'reply' => is_string($content) ? $content : '',
                    'tools_used' => array_values(array_unique($toolsUsed)),
                ];
            }

            foreach ($toolCalls as $toolCall) {
                if (! is_array($toolCall)) {
                    continue;
                }

                $name = $toolCall['function']['name'] ?? '';
                $name = is_string($name) ? $name : '';
                $toolsUsed[] = $name;

                $argumentsJson = $toolCall['function']['arguments'] ?? '{}';
                $decoded = is_string($argumentsJson) ? json_decode($argumentsJson, true) : [];
                $arguments = is_array($decoded) ? $decoded : [];

                $tool = $this->tools[$name] ?? null;
                $result = $tool !== null
                    ? $tool->execute($arguments)
                    : "Unknown tool: {$name}";

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => is_string($toolCall['id'] ?? null) ? $toolCall['id'] : '',
                    'content' => $result,
                ];
            }
        }

        // Step cap reached — ask for a final answer with no further tools.
        return [
            'reply' => $this->groq->chat($messages),
            'tools_used' => array_values(array_unique($toolsUsed)),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function toolDefinitions(): array
    {
        return array_values(array_map(
            fn (Tool $tool): array => [
                'type' => 'function',
                'function' => [
                    'name' => $tool->name(),
                    'description' => $tool->description(),
                    'parameters' => $tool->parameters(),
                ],
            ],
            $this->tools,
        ));
    }
}
