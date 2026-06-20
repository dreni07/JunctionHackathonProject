<?php

declare(strict_types=1);

namespace App\Agent\Tools;

use App\Agent\Tool;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * A read-only SQL window the task-planning agent uses to discover the workers
 * of a tenant (and their roles) before splitting an event into assignments.
 * Only a single SELECT statement is ever executed.
 */
class WorkforceQueryTool implements Tool
{
    private const MAX_ROWS = 80;

    public function name(): string
    {
        return 'db_query';
    }

    public function description(): string
    {
        return 'Run a single read-only SQL SELECT to inspect the Pyramid workforce before assigning tasks. '
            .'Useful tables: '
            .'users(id, name, email, account_type[organization|operational], tenant_id, worker_role) — '
            .'operational workers belong to a tenant and each has a worker_role (their profession); '
            .'tenants(id, title, description, roles[JSON array of the branch\'s job roles]). '
            .'Example: SELECT id, name, worker_role FROM users WHERE tenant_id = 2 AND account_type = \'operational\'. '
            .'Only a single SELECT statement is allowed.';
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
