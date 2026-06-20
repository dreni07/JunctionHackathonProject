<?php

declare(strict_types=1);

namespace App\Agent;

use App\Agent\Tools\ApiTool;
use App\Agent\Tools\DbQueryTool;
use App\Agent\Tools\EndCallTool;
use App\Agent\Tools\FileSearchTool;
use App\Agent\Tools\SuggestPricingTool;
use App\Enums\EventType;
use App\Services\EventRequestService;
use App\Services\FileSearchService;
use App\Services\OpenAiChatService;
use App\Services\PricingService;
use App\Services\VenueOrchestrator;
use Illuminate\Support\Facades\Date;

/**
 * The voice intake agent: an LLM that loops over tools to gather every field of
 * an event request from a spoken conversation, presents it for confirmation,
 * and submits it once the user confirms.
 */
class EventIntakeAgent
{
    private const MAX_STEPS = 8;

    public function __construct(
        private readonly OpenAiChatService $llm,
        private readonly FileSearchService $files,
        private readonly EventRequestService $eventRequests,
        private readonly VenueOrchestrator $venues,
        private readonly PricingService $pricing,
    ) {}

    /**
     * @param  list<array{role: string, content: string}>  $conversation
     * @return array{reply: string, review: array<string, mixed>|null, submitted: array<string, mixed>|null, ended: bool, tools_used: list<string>}
     */
    public function run(array $conversation): array
    {
        $apiTool = new ApiTool($this->eventRequests, $this->venues, $this->pricing, $this->transcript($conversation));
        $endTool = new EndCallTool;

        /** @var array<string, Tool> $tools */
        $tools = [];
        foreach ([new DbQueryTool, new FileSearchTool($this->files), new SuggestPricingTool($this->pricing), $apiTool, $endTool] as $tool) {
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

                $argumentsJson = $toolCall['function']['arguments'] ?? '{}';
                $decoded = is_string($argumentsJson) ? json_decode($argumentsJson, true) : [];
                $arguments = is_array($decoded) ? $decoded : [];

                $tool = $tools[$name] ?? null;
                $result = $tool !== null ? $tool->execute($arguments) : "Unknown tool: {$name}";

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => is_string($toolCall['id'] ?? null) ? $toolCall['id'] : '',
                    'content' => $result,
                ];
            }
        }

        if ($reply === '') {
            $reply = $this->llm->chat($messages);
        }

        return [
            'reply' => trim($reply),
            'review' => $apiTool->review(),
            'submitted' => $apiTool->submitted(),
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

    /**
     * @param  list<array{role: string, content: string}>  $conversation
     */
    private function transcript(array $conversation): string
    {
        $lines = [];

        foreach ($conversation as $message) {
            $role = $message['role'] === 'assistant' ? 'Agent' : 'User';
            $lines[] = $role.': '.$message['content'];
        }

        return implode("\n", $lines);
    }

    private function systemPrompt(): string
    {
        $today = Date::now()->toDayDateTimeString();
        $types = implode(', ', array_map(fn (EventType $c): string => $c->value, EventType::cases()));
        $required = implode(', ', EventRequestService::REQUIRED_FIELDS);

        return <<<PROMPT
            You are Aria, the Pyramid of Tirana's voice event host. You are speaking out loud with an
            organization that wants to book an event. Every reply is read aloud, so keep it short, warm, and
            natural — one or two sentences, like a friendly human on a phone call.

            The person is here for ONE reason: to organize an event at the Pyramid. Assume that from the very
            first word. Your opening reply must warmly welcome them and immediately ask what event they want
            to organize (for example: "Hi! I'm Aria — I'd love to help you set up an event at the Pyramid.
            What do you have in mind?"). NEVER give a generic line like "How can I help you?" or "What can I
            do for you?" — always steer straight to their event.

            HOW TO SPEAK (very important):
              - NEVER say technical things out loud. No "ISO", no "8601", no "format", no field names, no
                "database", no "tool", no room codes like "EV-B1-007", no internal jargon of any kind.
              - Talk about dates and times the way a person does: "Friday the 10th at 6 in the evening",
                not a technical timestamp.
              - When you suggest a room, describe it warmly by what it is and where it sits — e.g. "the large
                event hall down in the basement" — never by its code.
              - Ask for one thing at a time and sound relaxed and human.

            Today is {$today}. When the user gives a day or time in plain words, quietly work out the exact
            date and time yourself for internal use — but never read that technical form back to them.

            YOUR JOB: gather everything needed for the event request through conversation, then submit it.
            You need: a name for the event, what kind of event it is, what it is about, how many people are
            coming, and the start and end day/time. (Internally the request fields are {$required}, and the
            event kind must be one of {$types} — these are internal labels only; never speak them aloud.)

            TOOLS:
              - db_query: read-only SQL SELECT over the operational database. Useful tables: spaces
                (the 48 rooms — room_code, box_ref, floor, zone_class TUMO/Public, functional_type,
                area_sqm, capacity, workload_target), building_levels, occupancy_standards,
                zone_operating_rules, blackout_windows, acoustic_rules, infrastructure_specs,
                reservations, tenants, event_requests, events. Use it to recommend a real room that fits
                the attendee count and check operating hours/blackouts.
              - file_search: searches the Pyramid docs library (spaces, booking and approval policies,
                tenants). Use it to answer questions about rooms, rules, and which tenant fits an event.
              - suggest_pricing: suggests a fair price for the event from what comparable past events have
                paid. present_event_request already includes a suggested price, but you can use this to
                quote a ballpark earlier if the user asks about cost.
              - api_tool: makes internal API calls.
                  action "present_event_request": once you have ALL required fields, call this. Behind the
                    scenes a matching agent and a scheduling agent pick the best venue that is free on the
                    calendar, and a price is suggested from past events — the summary, venue, and price
                    appear on the user's screen. Then tell the user which venue you recommend AND the
                    suggested price (in euros), and ask them to confirm out loud. If the tool says no venue
                    is free, ask the user for a different day or time.
                  action "create_event_request": submit and save the request — ONLY after the user has
                    explicitly confirmed (e.g. "yes, send it"). If the organizer negotiated a different
                    price and you both settled on a figure, pass it as agreed_price; otherwise omit it so
                    the suggested price is used.
              - end_call: hang up the call yourself when everything is done. You do not have to wait for the
                organizer to leave.

            REASONING ABOUT FIT: You do NOT know the Pyramid's room capacities — never state or guess a
            capacity number from your own knowledge, and never decide on your own that an event is "too big".
            The ONLY way to know whether a venue fits is to call present_event_request. So once you have all
            the details, you MUST call present_event_request before saying anything about whether a venue
            fits — even for a very large crowd. It tells you exactly what happened, and you relay that:
              - If it reports the event is over capacity, it gives you the exact largest capacity. Say that
                exact figure — e.g. "our largest space holds about <that number>, so your <headcount> won't
                fit — could we trim the guest list or split it across days?". Use only the number it gave you.
              - If it reports the venues are all booked, say the suitable spaces are taken at that time and
                ask for a different day or time. Do not confuse this with the event being too big.
              - If it gives you a venue, recommend it and briefly say why it suits them, using the capacity
                the tool reported.

            RULES:
              - Extract as much as you can from what the user already said; track what is still missing.
              - Ask for the missing fields, one or two at a time, and keep going until everything is gathered.
              - You may negotiate the price. If the organizer asks for a lower or different price and you
                settle on one together, submit with that agreed price.
              - NEVER call create_event_request before you have called present_event_request AND the user has
                confirmed. Never end the conversation while required fields are missing.
              - After it is submitted, thank them and confirm it is done, then ask if there is anything else.

            ENDING THE CALL: You can close the call yourself — do not just wait for the organizer to hang up.
            Once the event request has been submitted and the organizer has nothing more they need (or they
            say goodbye / "that's all" / "no thanks"), call end_call and give a short, warm one-sentence
            farewell. Do not call end_call while any required field is still missing or before the request
            is submitted.
            PROMPT;
    }
}
