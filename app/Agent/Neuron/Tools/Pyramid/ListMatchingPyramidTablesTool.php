<?php

declare(strict_types=1);

namespace App\Agent\Neuron\Tools\Pyramid;

use App\Services\PyramidTableRegistry;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use Throwable;

/**
 * Returns Pyramid-related tables that match the extracted document topic.
 */
class ListMatchingPyramidTablesTool extends Tool
{
    public function __construct(
        private readonly PyramidTableRegistry $registry,
    ) {
        parent::__construct(
            'list_matching_pyramid_tables',
            'Find existing Pyramid database tables that may fit extracted PDF data. '
                .'Returns domain tables (spaces, assets, events, etc.) and any dynamic '
                .'pyramid_data_* tables already created. Use an optional topic such as '
                .'"spaces capacity halls" or "inventory microphones".',
        );
    }

    /**
     * @return list<ToolProperty>
     */
    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'topic',
                type: PropertyType::STRING,
                description: 'Keywords describing the PDF content (e.g. "spaces halls capacity"). Optional.',
                required: false,
            ),
        ];
    }

    public function __invoke(string $topic = ''): string
    {
        try {
            $tables = $this->registry->listMatchingTables($topic);

            return json_encode([
                'tables' => $tables,
                'count' => count($tables),
            ], JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            return 'Error: '.$e->getMessage();
        }
    }
}
