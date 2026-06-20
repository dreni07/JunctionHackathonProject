<?php

declare(strict_types=1);

namespace App\Services;

use App\Agent\Neuron\OpenAIAgent;
use App\Agent\Neuron\SchedulingAgent;
use App\Agent\Neuron\ToolResultCollector;
use App\Agent\Neuron\VenueMatchingAgent;
use App\Models\Space;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Observability\Events\ToolCalled;
use NeuronAI\Observability\ObserverInterface;
use Throwable;

/**
 * Runs the two NeuronAI agents in sequence — the matching agent scores every
 * venue, then the scheduling agent picks the highest-confidence venue that is
 * free on the calendar — and hands the result back to the main (voice) agent.
 *
 * Each agent's structured output is captured from its tool result; if an agent
 * run fails, it falls back to the deterministic service so a recommendation is
 * always produced.
 */
class VenueOrchestrator
{
    public function __construct(
        private readonly VenueMatchingService $matching,
        private readonly SchedulingService $scheduling,
    ) {}

    /**
     * @return array{
     *     ranked: list<array<string, mixed>>,
     *     selected: array<string, mixed>|null,
     *     available: bool,
     *     reason: 'ok'|'over_capacity'|'all_booked',
     *     max_capacity: int|null
     * }
     */
    public function recommend(string $eventType, int $attendees, string $startAt, string $endAt): array
    {
        $ranked = $this->matchVenues($eventType, $attendees);

        // No venue scored above zero — every bookable space is too small for the
        // crowd (TUMO education rooms are off-limits to commercial events).
        if ($ranked === []) {
            return [
                'ranked' => [],
                'selected' => null,
                'available' => false,
                'reason' => 'over_capacity',
                'max_capacity' => $this->largestBookableCapacity(),
            ];
        }

        $ids = array_map(fn (array $row): string => (string) $row['space_id'], $ranked);
        $schedule = $this->scheduleVenues($ids, $startAt, $endAt);

        $selectedId = $schedule['selected_space_id'] ?? null;
        $selected = $selectedId !== null
            ? collect($ranked)->firstWhere('space_id', $selectedId)
            : null;

        return [
            'ranked' => array_slice($ranked, 0, 5),
            'selected' => $selected,
            'available' => $selected !== null,
            // A fitting venue exists, but every one is already booked then.
            'reason' => $selected !== null ? 'ok' : 'all_booked',
            'max_capacity' => null,
        ];
    }

    /**
     * The capacity of the largest commercial (non-TUMO) venue.
     */
    private function largestBookableCapacity(): int
    {
        return (int) Space::query()
            ->whereNotNull('room_code')
            ->where('zone_class', '!=', 'TUMO')
            ->max('capacity');
    }

    /**
     * Run the matching agent; fall back to the service on any failure.
     *
     * @return list<array<string, mixed>>
     */
    private function matchVenues(string $eventType, int $attendees): array
    {
        $prompt = "Match this event to the venues. Event type: {$eventType}. Attendees: {$attendees}.";

        $raw = $this->runAgent(VenueMatchingAgent::class, $prompt, 'rank_venues_by_fit');
        $decoded = is_string($raw) ? json_decode($raw, true) : null;

        if (is_array($decoded) && $decoded !== []) {
            return array_values($decoded);
        }

        return $this->matching->rankAsArray($eventType, $attendees);
    }

    /**
     * Run the scheduling agent; fall back to the service on any failure.
     *
     * @param  list<string>  $ids
     * @return array{selected_space_id: string|null, checked: list<array{space_id: string, available: bool}>}
     */
    private function scheduleVenues(array $ids, string $startAt, string $endAt): array
    {
        $csv = implode(',', $ids);
        $prompt = "Find the first free venue. Ranked venue ids (best first): {$csv}. "
            ."Start: {$startAt}. End: {$endAt}.";

        $raw = $this->runAgent(SchedulingAgent::class, $prompt, 'select_available_venue');
        $decoded = is_string($raw) ? json_decode($raw, true) : null;

        if (is_array($decoded) && array_key_exists('selected_space_id', $decoded)) {
            /** @var array{selected_space_id: string|null, checked: list<array{space_id: string, available: bool}>} $decoded */
            return $decoded;
        }

        return $this->scheduling->firstAvailable($ids, $startAt, $endAt);
    }

    /**
     * Invoke a NeuronAI agent and return the raw result of the named tool it
     * called, or null if the agent run failed or never called the tool.
     *
     * @param  class-string<OpenAIAgent>  $agentClass
     */
    private function runAgent(string $agentClass, string $prompt, string $toolName): ?string
    {
        try {
            $collector = new ToolResultCollector;

            $agent = $agentClass::make();
            $agent->observe($this->collectingObserver($collector));
            $agent->chat(new UserMessage($prompt))->getMessage();

            return $collector->results[$toolName] ?? null;
        } catch (Throwable) {
            return null;
        }
    }

    private function collectingObserver(ToolResultCollector $collector): ObserverInterface
    {
        return new class($collector) implements ObserverInterface
        {
            public function __construct(private readonly ToolResultCollector $collector) {}

            public function onEvent(string $event, object $source, mixed $data = null, ?string $branchId = null): void
            {
                if ($event === 'tool-called' && $data instanceof ToolCalled) {
                    $this->collector->results[$data->tool->getName()] = (string) $data->tool->getResult();
                }
            }
        };
    }
}
