<?php

declare(strict_types=1);

namespace App\Agent\Neuron\Tools\Pyramid;

use App\Services\PyramidTableRegistry;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use Throwable;

/**
 * Inserts structured rows extracted from a PDF into Pyramid tables.
 */
class InsertPyramidRowsTool extends Tool
{
    public function __construct(
        private readonly PyramidTableRegistry $registry,
    ) {
        parent::__construct(
            'insert_pyramid_rows',
            'Insert structured rows into Pyramid tables returned from list_matching_pyramid_tables '
                .'and/or create_pyramid_table. Pass rows_json as a JSON array of objects whose '
                .'keys match table columns. You may call list/create tools first, then insert into '
                .'all relevant tables in one or more calls (max 50 rows per call).',
        );
    }

    /**
     * @return list<ToolProperty>
     */
    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'table_name',
                type: PropertyType::STRING,
                description: 'Target table name.',
                required: true,
            ),
            new ToolProperty(
                name: 'rows_json',
                type: PropertyType::STRING,
                description: 'JSON array of row objects to insert.',
                required: true,
            ),
        ];
    }

    public function __invoke(string $table_name, string $rows_json): string
    {
        try {
            /** @var list<array<string, mixed>>|null $rows */
            $rows = json_decode($rows_json, true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($rows)) {
                return 'Error: rows_json must decode to an array.';
            }

            $result = $this->registry->insertRows($table_name, $rows);

            return json_encode($result, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            return 'Error: '.$e->getMessage();
        }
    }
}
