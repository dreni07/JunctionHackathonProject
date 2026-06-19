<?php

declare(strict_types=1);

namespace App\Agent\Neuron;

use App\Agent\Neuron\Tools\Pyramid\CreatePyramidTableTool;
use App\Agent\Neuron\Tools\Pyramid\InsertPyramidRowsTool;
use App\Agent\Neuron\Tools\Pyramid\ListMatchingPyramidTablesTool;
use App\Services\PyramidTableRegistry;
use NeuronAI\Agent\SystemPrompt;
use NeuronAI\Tools\ToolInterface;

/**
 * Ingests Pyramid PDF knowledge into structured database tables.
 */
class PyramidKnowledgeAgent extends OpenAIAgent
{
    protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'You are a Pyramid of Tirana knowledge ingestion agent.',
                'You receive raw text extracted from official Pyramid documents (PDFs).',
                'Your job is to structure that information into database tables for RAG and operations.',
            ],
            steps: [
                'Read the PDF text carefully and identify structured facts (spaces, capacities, equipment, policies, contacts, schedules, etc.).',
                'Call list_matching_pyramid_tables with a short topic describing the content.',
                'If existing domain or pyramid_data_* tables fit, use them. If not, call create_pyramid_table for each new dataset you need.',
                'You may call list_matching_pyramid_tables and create_pyramid_table multiple times in any order before inserting data.',
                'Call insert_pyramid_rows for every target table, using all tables returned from the list/create tools.',
                'Prefer dynamic pyramid_data_* tables for novel PDF facts. Only insert into core domain tables when columns clearly match.',
                'Do not invent data that is not supported by the PDF text.',
            ],
            output: [
                'Finish with a concise summary listing which tables were used or created and how many rows were inserted.',
                'Mention any facts that could not be mapped.',
            ],
        );
    }

    /**
     * @return list<ToolInterface>
     */
    protected function tools(): array
    {
        $registry = app(PyramidTableRegistry::class);

        return [
            new ListMatchingPyramidTablesTool($registry),
            new CreatePyramidTableTool($registry),
            new InsertPyramidRowsTool($registry),
        ];
    }
}
