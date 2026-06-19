<?php

declare(strict_types=1);

namespace App\Agent\Neuron\Tools\Pyramid;

use App\Services\PyramidTableRegistry;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use Throwable;

/**
 * Creates a new dynamic Pyramid table when no existing table fits the PDF data.
 */
class CreatePyramidTableTool extends Tool
{
    public function __construct(
        private readonly PyramidTableRegistry $registry,
    ) {
        parent::__construct(
            'create_pyramid_table',
            'Create a new dynamic table prefixed with pyramid_data_ when extracted PDF '
                .'facts do not fit existing tables. Pass columns_json as a JSON array like '
                .'[{"name":"hall_name","type":"string"},{"name":"capacity","type":"integer"}]. '
                .'Allowed types: string, text, integer, decimal, boolean, json, datetime.',
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
                description: 'Desired table name (pyramid_data_ prefix added automatically if missing).',
                required: true,
            ),
            new ToolProperty(
                name: 'columns_json',
                type: PropertyType::STRING,
                description: 'JSON array of column definitions with name and type.',
                required: true,
            ),
        ];
    }

    public function __invoke(string $table_name, string $columns_json): string
    {
        try {
            /** @var list<array{name: string, type: string}>|null $columns */
            $columns = json_decode($columns_json, true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($columns)) {
                return 'Error: columns_json must decode to an array.';
            }

            $result = $this->registry->createTable($table_name, $columns);

            return json_encode($result, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            return 'Error: '.$e->getMessage();
        }
    }
}
