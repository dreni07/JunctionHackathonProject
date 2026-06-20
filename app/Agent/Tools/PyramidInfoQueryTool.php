<?php

declare(strict_types=1);

namespace App\Agent\Tools;

use App\Agent\Tool;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * A read-only SQL window the event advisor uses to answer questions about what
 * the Pyramid offers — venues, capacities, pricing, rules and availability.
 * Only a single SELECT statement is ever executed.
 */
class PyramidInfoQueryTool implements Tool
{
    private const MAX_ROWS = 50;

    public function name(): string
    {
        return 'db_query';
    }

    public function description(): string
    {
        return 'Run a single read-only SQL SELECT to look up real Pyramid details so you can answer the '
            .'visitor accurately. Useful tables: '
            .'spaces(id, name, room_code, box_ref, zone_class[TUMO|Public], functional_type, floor, area_sqm, '
            .'capacity, workload_target) — the bookable rooms; commercial events use non-TUMO spaces. '
            .'pricing_references(event_type, venue_name, area_sqm, duration_days, attendees, price_eur) — '
            .'what comparable past events paid, for ballpark pricing. '
            .'occupancy_standards, zone_operating_rules, blackout_windows, acoustic_rules, infrastructure_specs — '
            .'capacity, opening hours, closed periods and technical specs. '
            .'reservations(space_id, start_at, end_at, status) — existing bookings. '
            .'Example: SELECT name, capacity, area_sqm FROM spaces WHERE zone_class != \'TUMO\' AND capacity >= 100 ORDER BY capacity. '
            .'Only one SELECT statement is allowed.';
    }

    /**
     * @return array<string, mixed>
     */
    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'A single read-only SQL SELECT statement.',
                ],
            ],
            'required' => ['query'],
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function execute(array $arguments): string
    {
        $sql = trim((string) ($arguments['query'] ?? ''));

        if ($sql === '') {
            return 'Error: empty query.';
        }

        $clean = rtrim($sql, "; \t\n\r");

        if (! preg_match('/^select\b/i', $clean)) {
            return 'Error: only SELECT queries are allowed.';
        }

        if (preg_match('/\b(insert|update|delete|drop|alter|truncate|create|replace|grant|revoke|merge|call)\b/i', $clean)) {
            return 'Error: the query contains a forbidden keyword. Only read-only SELECT is allowed.';
        }

        if (str_contains($clean, ';')) {
            return 'Error: multiple statements are not allowed.';
        }

        try {
            $rows = DB::select($clean);
        } catch (Throwable $e) {
            return 'Query error: '.$e->getMessage();
        }

        if ($rows === []) {
            return 'No rows returned.';
        }

        $trimmed = array_map(fn (object $row): array => (array) $row, array_slice($rows, 0, self::MAX_ROWS));
        $note = count($rows) > self::MAX_ROWS ? ' (truncated to '.self::MAX_ROWS.' rows)' : '';

        return 'Rows'.$note.': '.(json_encode($trimmed) ?: '[]');
    }
}
