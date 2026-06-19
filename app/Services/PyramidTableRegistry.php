<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Safe, scoped database operations for Pyramid knowledge ingestion tools.
 */
class PyramidTableRegistry
{
    public const DYNAMIC_PREFIX = 'pyramid_data_';

    /** @var list<string> */
    public const DOMAIN_TABLES = [
        'organizations',
        'tenants',
        'spaces',
        'assets',
        'events',
        'event_requests',
        'event_requirements',
        'event_state',
        'reservations',
        'asset_reservations',
        'asset_movements',
        'final_proposals',
        'quotation_line_items',
        'tasks',
        'conflicts',
        'event_contacts',
        'alerts',
    ];

    /** @var list<string> */
    private const ALLOWED_COLUMN_TYPES = [
        'string',
        'text',
        'integer',
        'decimal',
        'boolean',
        'json',
        'datetime',
    ];

    private const MAX_ROWS_PER_INSERT = 50;

    private const MAX_ROWS_PER_EXPORT = 200;

    /**
     * Snapshot of ingested pyramid_data_* tables plus domain tables that contain rows.
     *
     * @return list<array{
     *     table: string,
     *     label: string,
     *     kind: 'ingested'|'domain',
     *     row_count: int,
     *     columns: list<array{name: string, type: string}>,
     *     display_columns: list<string>,
     *     rows: list<array<string, mixed>>
     * }>
     */
    public function exportKnowledgeSnapshot(): array
    {
        $tableNames = collect(Schema::getTableListing())
            ->map(fn (string $table): string => $this->normalizeTableName($table))
            ->filter(fn (string $table): bool => str_starts_with($table, self::DYNAMIC_PREFIX))
            ->values();

        foreach (self::DOMAIN_TABLES as $domainTable) {
            if (Schema::hasTable($domainTable) && $this->rowCount($domainTable) > 0) {
                $tableNames->push($domainTable);
            }
        }

        $snapshots = [];

        foreach ($tableNames->unique()->sort()->values() as $table) {
            $snapshot = $this->snapshotTable($table);

            if ($snapshot['kind'] === 'ingested' || $snapshot['row_count'] > 0) {
                $snapshots[] = $snapshot;
            }
        }

        return $snapshots;
    }

    /**
     * @return array{
     *     table: string,
     *     label: string,
     *     kind: 'ingested'|'domain',
     *     row_count: int,
     *     columns: list<array{name: string, type: string}>,
     *     display_columns: list<string>,
     *     rows: list<array<string, mixed>>
     * }
     */
    private function snapshotTable(string $table): array
    {
        $columns = $this->describeColumns($table);
        $displayColumns = collect($columns)
            ->pluck('name')
            ->reject(fn (string $name): bool => in_array($name, ['created_at', 'updated_at'], true))
            ->values()
            ->all();

        $rows = DB::table($table)
            ->orderBy('id')
            ->limit(self::MAX_ROWS_PER_EXPORT)
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->all();

        if (str_starts_with($table, self::DYNAMIC_PREFIX)) {
            return [
                'table' => $table,
                'label' => $this->humanLabel($table),
                'kind' => 'ingested',
                'row_count' => $this->rowCount($table),
                'columns' => $columns,
                'display_columns' => array_values($displayColumns),
                'rows' => array_values($rows),
            ];
        }

        return [
            'table' => $table,
            'label' => $this->humanLabel($table),
            'kind' => 'domain',
            'row_count' => $this->rowCount($table),
            'columns' => $columns,
            'display_columns' => array_values($displayColumns),
            'rows' => array_values($rows),
        ];
    }

    private function humanLabel(string $table): string
    {
        $name = str_starts_with($table, self::DYNAMIC_PREFIX)
            ? substr($table, strlen(self::DYNAMIC_PREFIX))
            : $table;

        return str($name)->replace('_', ' ')->title()->toString();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listMatchingTables(string $topic = ''): array
    {
        $keywords = $this->keywordsFromTopic($topic);
        $tables = $this->discoverPyramidTables();
        $results = [];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $columns = $this->describeColumns($table);
            $score = $this->scoreTable($table, $columns, $keywords);

            if ($keywords !== [] && $score === 0) {
                continue;
            }

            $results[] = [
                'table' => $table,
                'kind' => str_starts_with($table, self::DYNAMIC_PREFIX) ? 'dynamic' : 'domain',
                'match_score' => $score,
                'row_count' => $this->rowCount($table),
                'columns' => $columns,
            ];
        }

        usort($results, fn (array $a, array $b): int => ($b['match_score'] <=> $a['match_score']) ?: strcmp($a['table'], $b['table']));

        return $results;
    }

    /**
     * @param  list<array{name: string, type: string}>  $columns
     * @return array<string, mixed>
     */
    public function createTable(string $tableName, array $columns): array
    {
        $table = $this->normalizeDynamicTableName($tableName);

        if ($columns === []) {
            throw new InvalidArgumentException('At least one column is required.');
        }

        if (Schema::hasTable($table)) {
            return [
                'status' => 'exists',
                'table' => $table,
                'columns' => $this->describeColumns($table),
            ];
        }

        Schema::create($table, function (Blueprint $blueprint) use ($columns): void {
            $blueprint->uuid('id')->primary();

            foreach ($columns as $column) {
                $name = $this->normalizeColumnName($column['name']);
                $type = strtolower($column['type']);

                if (! in_array($type, self::ALLOWED_COLUMN_TYPES, true)) {
                    throw new InvalidArgumentException("Unsupported column type [{$type}].");
                }

                match ($type) {
                    'string' => $blueprint->string($name),
                    'text' => $blueprint->text($name),
                    'integer' => $blueprint->integer($name),
                    'decimal' => $blueprint->decimal($name, 12, 4)->nullable(),
                    'boolean' => $blueprint->boolean($name)->default(false),
                    'json' => $blueprint->json($name)->nullable(),
                    'datetime' => $blueprint->dateTime($name)->nullable(),
                };
            }

            $blueprint->timestamps();
        });

        return [
            'status' => 'created',
            'table' => $table,
            'columns' => $this->describeColumns($table),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    public function insertRows(string $tableName, array $rows): array
    {
        $table = $this->resolveInsertableTable($tableName);

        if ($rows === []) {
            throw new InvalidArgumentException('At least one row is required.');
        }

        if (count($rows) > self::MAX_ROWS_PER_INSERT) {
            throw new InvalidArgumentException('Too many rows. Maximum is '.self::MAX_ROWS_PER_INSERT.' per call.');
        }

        $allowedColumns = array_column($this->describeColumns($table), 'name');
        $preparedRows = [];

        foreach ($rows as $index => $row) {
            $filtered = [];

            foreach ($row as $key => $value) {
                $column = $this->normalizeColumnName((string) $key);

                if (! in_array($column, $allowedColumns, true)) {
                    continue;
                }

                if ($column === 'id' && ($value === null || $value === '')) {
                    $filtered['id'] = (string) str()->uuid();

                    continue;
                }

                $filtered[$column] = $value;
            }

            if (! array_key_exists('id', $filtered) && in_array('id', $allowedColumns, true)) {
                $filtered['id'] = (string) str()->uuid();
            }

            if ($filtered === []) {
                throw new InvalidArgumentException("Row {$index} did not contain any valid columns.");
            }

            $preparedRows[] = $filtered;
        }

        try {
            DB::table($table)->insert($preparedRows);
        } catch (Throwable $e) {
            throw new RuntimeException('Insert failed: '.$e->getMessage(), previous: $e);
        }

        return [
            'table' => $table,
            'inserted' => count($preparedRows),
            'row_count' => $this->rowCount($table),
        ];
    }

    /**
     * @return list<string>
     */
    private function discoverPyramidTables(): array
    {
        $dynamic = collect(Schema::getTableListing())
            ->map(fn (string $table): string => $this->normalizeTableName($table))
            ->filter(fn (string $table): bool => str_starts_with($table, self::DYNAMIC_PREFIX))
            ->values()
            ->all();

        return array_values(array_unique([...self::DOMAIN_TABLES, ...$dynamic]));
    }

    private function normalizeTableName(string $table): string
    {
        if (str_contains($table, '.')) {
            return substr($table, strrpos($table, '.') + 1);
        }

        return $table;
    }

    /**
     * @return list<string>
     */
    private function keywordsFromTopic(string $topic): array
    {
        $words = preg_split('/[^a-z0-9_]+/i', strtolower($topic)) ?: [];

        return array_values(array_filter($words, fn (string $word): bool => strlen($word) >= 3));
    }

    /**
     * @param  list<array{name: string, type: string}>  $columns
     * @param  list<string>  $keywords
     */
    private function scoreTable(string $table, array $columns, array $keywords): int
    {
        if ($keywords === []) {
            return 1;
        }

        $haystack = strtolower($table.' '.implode(' ', array_column($columns, 'name')));
        $score = 0;

        foreach ($keywords as $keyword) {
            if (str_contains($haystack, $keyword)) {
                $score++;
            }
        }

        return $score;
    }

    /**
     * @return list<array{name: string, type: string}>
     */
    private function describeColumns(string $table): array
    {
        /** @var list<array{name: string, type: string}> $columns */
        $columns = collect(Schema::getColumns($table))
            ->map(fn (array $column): array => [
                'name' => (string) $column['name'],
                'type' => (string) ($column['type_name'] ?? $column['type'] ?? 'string'),
            ])
            ->values()
            ->all();

        return $columns;
    }

    private function rowCount(string $table): int
    {
        return (int) DB::table($table)->count();
    }

    private function normalizeDynamicTableName(string $tableName): string
    {
        $normalized = strtolower(trim($tableName));
        $normalized = preg_replace('/[^a-z0-9_]+/', '_', $normalized) ?? $normalized;
        $normalized = trim($normalized, '_');

        if (! str_starts_with($normalized, self::DYNAMIC_PREFIX)) {
            $normalized = self::DYNAMIC_PREFIX.ltrim($normalized, '_');
        }

        if (! preg_match('/^pyramid_data_[a-z0-9_]{2,60}$/', $normalized)) {
            throw new InvalidArgumentException('Invalid dynamic table name.');
        }

        return $normalized;
    }

    private function resolveInsertableTable(string $tableName): string
    {
        $normalized = strtolower(trim($tableName));

        if (str_starts_with($normalized, self::DYNAMIC_PREFIX)) {
            $table = $this->normalizeDynamicTableName($normalized);
        } elseif (in_array($normalized, self::DOMAIN_TABLES, true)) {
            $table = $normalized;
        } else {
            throw new InvalidArgumentException('Table is not allowed for inserts.');
        }

        if (! Schema::hasTable($table)) {
            throw new InvalidArgumentException("Table [{$table}] does not exist.");
        }

        return $table;
    }

    private function normalizeColumnName(string $name): string
    {
        $normalized = strtolower(trim($name));
        $normalized = preg_replace('/[^a-z0-9_]+/', '_', $normalized) ?? $normalized;
        $normalized = trim($normalized, '_');

        if ($normalized === '' || ! preg_match('/^[a-z_][a-z0-9_]{0,62}$/', $normalized)) {
            throw new InvalidArgumentException('Invalid column name.');
        }

        return $normalized;
    }
}
