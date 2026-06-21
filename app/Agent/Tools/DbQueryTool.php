<?php

namespace App\Agent\Tools;

use App\Agent\Tool;
use Illuminate\Support\Facades\DB;
use Throwable;

class DbQueryTool implements Tool
{
    private const MAX_ROWS = 50;

    public function name(): string
    {
        return 'db_query';
    }

    public function description(): string
    {
        return 'Run a read-only SQL SELECT query against the MySQL database to answer questions '
            .'about uploaded documents (counts, filters, dates, listings). '
            .'Schema: documents(id, title, original_filename, source_type[image|pdf], page_count, full_text, created_at, updated_at). '
            .'Only a single SELECT statement is allowed.';
    }

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

    public function execute(array $arguments): string
    {
        $sql = trim((string) ($arguments['query'] ?? ''));

        if ($sql === '') {
            return 'Error: empty query.';
        }

        $withoutTrailingSemicolon = rtrim($sql, "; \t\n\r");

        if (! preg_match('/^select\b/i', $withoutTrailingSemicolon)) {
            return 'Error: only SELECT queries are allowed.';
        }

        if (preg_match('/\b(insert|update|delete|drop|alter|truncate|create|replace|grant|revoke|merge|call)\b/i', $withoutTrailingSemicolon)) {
            return 'Error: the query contains a forbidden keyword. Only read-only SELECT is allowed.';
        }

        if (str_contains($withoutTrailingSemicolon, ';')) {
            return 'Error: multiple statements are not allowed.';
        }

        try {
            $rows = DB::select($withoutTrailingSemicolon);
        } catch (Throwable $e) {
            return 'Query error: '.$e->getMessage();
        }

        if ($rows === []) {
            return 'No rows returned.';
        }

        $trimmed = array_map(
            fn (object $row): array => $this->truncateValues((array) $row),
            array_slice($rows, 0, self::MAX_ROWS),
        );

        $note = count($rows) > self::MAX_ROWS ? ' (truncated to '.self::MAX_ROWS.' rows)' : '';

        return 'Rows'.$note.': '.(json_encode($trimmed) ?: '[]');
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function truncateValues(array $row): array
    {
        return array_map(function (mixed $value): mixed {
            if (is_string($value) && strlen($value) > 500) {
                return substr($value, 0, 500).'…';
            }

            return $value;
        }, $row);
    }
}
