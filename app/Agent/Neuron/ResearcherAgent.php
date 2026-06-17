<?php

declare(strict_types=1);

namespace App\Agent\Neuron;

use App\Agent\Neuron\Tools\DbQueryTool;
use NeuronAI\Agent\SystemPrompt;
use NeuronAI\Tools\ToolInterface;

/**
 * An agent that researches the user's uploaded documents by querying the
 * database with the db_query tool.
 *
 * The only thing that makes it "agentic": the tools() method below.
 * Neuron handles the loop — the LLM decides when to call a tool,
 * Neuron runs it, feeds the result back, and the LLM answers.
 */
class ResearcherAgent extends GroqAgent
{
    protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'You are a diligent research assistant for a document library.',
                'You answer questions about the documents the user has uploaded.',
            ],
            steps: [
                'When a question is about the uploaded documents (counts, titles, dates, contents), '
                    .'call the db_query tool with a single read-only SELECT statement.',
                'Read the rows that come back, then answer based on them.',
            ],
            output: [
                'Give a clear, concise answer grounded in the query results.',
                'If a query returns no rows, say so honestly instead of guessing.',
            ],
        );
    }

    /**
     * The full list of tools THIS agent is allowed to use.
     *
     * tools() returns an array, so an agent can have as many tools as you like —
     * just add more instances to this list and the LLM will choose between them.
     *
     * @return list<ToolInterface>
     */
    protected function tools(): array
    {
        return [
            new DbQueryTool,
            // Add more tools here, e.g.:
            // new WebSearchTool,
            // new DocumentSearchTool,
        ];
    }
}
