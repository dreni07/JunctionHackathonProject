<?php

declare(strict_types=1);

namespace App\Agent;

use App\Agent\Tools\EndCallTool;
use App\Agent\Tools\FileSearchTool;
use App\Agent\Tools\PyramidInfoQueryTool;
use App\Agent\Tools\StartEventRequestTool;
use App\Services\FileSearchService;
use App\Services\OpenAiChatService;
use Illuminate\Support\Facades\Date;

/**
 * The Pyramid's event advisor: a friendly front-of-house agent that explains
 * what the Pyramid offers and answers questions using real venue, pricing and
 * facility data. When the visitor clearly wants to actually book, it raises an
 * escalation flag so the controller can hand over to the event-intake agent.
 */
class EventAdvisorAgent
{
    private const MAX_STEPS = 6;

    public function __construct(
        private readonly OpenAiChatService $llm,
        private readonly FileSearchService $files,
    ) {}

    /**
     * @param  list<array{role: string, content: string}>  $conversation
     * @return array{reply: string, escalate: bool, ended: bool, tools_used: list<string>}
     */
    public function run(array $conversation): array
    {
        $startTool = new StartEventRequestTool;
        $endTool = new EndCallTool;

        /** @var array<string, Tool> $tools */
        $tools = [];
        foreach ([new PyramidInfoQueryTool, new FileSearchTool($this->files), $startTool, $endTool] as $tool) {
            $tools[$tool->name()] = $tool;
        }

        /** @var list<array<string, mixed>> $messages */
        $messages = array_merge(
            [['role' => 'system', 'content' => $this->systemPrompt()]],
            $conversation,
        );

        $definitions = $this->toolDefinitions($tools);
        $toolsUsed = [];
        $reply = '';

        for ($step = 0; $step < self::MAX_STEPS; $step++) {
            $message = $this->llm->chatWithTools($messages, $definitions);
            $messages[] = $message;

            $toolCalls = $message['tool_calls'] ?? null;

            if (! is_array($toolCalls) || $toolCalls === []) {
                $reply = is_string($message['content'] ?? null) ? $message['content'] : '';
                break;
            }

            foreach ($toolCalls as $toolCall) {
                if (! is_array($toolCall)) {
                    continue;
                }

                $name = is_string($toolCall['function']['name'] ?? null) ? $toolCall['function']['name'] : '';
                $toolsUsed[] = $name;

                $decoded = json_decode((string) ($toolCall['function']['arguments'] ?? '{}'), true);
                $arguments = is_array($decoded) ? $decoded : [];

                $tool = $tools[$name] ?? null;
                $result = $tool !== null ? $tool->execute($arguments) : "Unknown tool: {$name}";

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => is_string($toolCall['id'] ?? null) ? $toolCall['id'] : '',
                    'content' => $result,
                ];
            }

            // Once the visitor wants to book, stop advising and hand over.
            if ($startTool->escalated()) {
                break;
            }
        }

        if ($reply === '' && ! $startTool->escalated()) {
            $reply = $this->llm->chat($messages);
        }

        return [
            'reply' => trim($reply),
            'escalate' => $startTool->escalated(),
            'ended' => $endTool->ended(),
            'tools_used' => array_values(array_unique($toolsUsed)),
        ];
    }

    /**
     * @param  array<string, Tool>  $tools
     * @return list<array<string, mixed>>
     */
    private function toolDefinitions(array $tools): array
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
            $tools,
        ));
    }

    private function systemPrompt(): string
    {
        $today = Date::now()->toDayDateTimeString();

        return <<<PROMPT
            You are Cleopatra, the warm, knowledgeable event advisor at the Pyramid of Tirana — a friendly
            woman. You are speaking out loud, so keep every reply short, friendly and natural — one or two
            sentences, like a helpful host on a call. Today is {$today}.

            YOUR ROLE: help a curious visitor understand what the Pyramid offers for events. Answer their
            questions clearly and honestly — about spaces, how many people fit, the kinds of events that work
            here, rough pricing, opening hours and rules. Make it easy for them to picture hosting here.

            HOW TO ANSWER (very important):
              - Base every concrete answer on REAL data — call db_query to look up venues, capacities, areas,
                pricing and rules before you state numbers. Never invent a capacity or price.
              - Use file_search for policy, booking and guideline questions.
              - Speak like a person: describe a room by what it is ("our large event hall in the basement"),
                never by codes like "EV-B1-007". No jargon, no field names, no "database".
              - If they tell you the shape of their event (e.g. "around 100 people"), look up which spaces fit
                and tell them their options and a ballpark price.

            SWITCHING TO BOOKING: You only ADVISE — you do not take the booking yourself. The moment the visitor
            clearly wants to actually organize or book an event (they say things like "I want to book",
            "let's set it up", "how do I reserve", "let's do it", or they start giving the details of an event
            they want to hold), call start_event_request to hand them over to the booking assistant. Do not
            escalate while they are only asking questions or weighing options — let them explore as long as they
            like first.

            ENDING: If the visitor is just done and says goodbye, you may call end_call and give a short, warm
            farewell.

            Your opening line should warmly welcome them and invite their questions about hosting at the Pyramid
            (for example: "Hi! I'm Cleopatra — happy to tell you anything about hosting an event at the Pyramid.
            What would you like to know?").
            PROMPT;
    }
}
