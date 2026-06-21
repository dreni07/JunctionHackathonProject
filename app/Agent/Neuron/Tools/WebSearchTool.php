<?php

declare(strict_types=1);

namespace App\Agent\Neuron\Tools;

use Illuminate\Support\Facades\Http;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use Throwable;

/**
 * A web search tool the agent can call.
 *
 * Neuron tools work by:
 *  1. parent::__construct() declares the name + description the LLM sees,
 *  2. properties() declares the arguments the LLM must provide,
 *  3. __invoke() runs the actual work; its parameters match properties() by name.
 *
 * Uses DuckDuckGo's free Instant Answer API (no API key required).
 */
class WebSearchTool extends Tool
{
    public function __construct()
    {
        parent::__construct(
            'web_search',
            'Search the public web for current or general information. '
                .'Use this whenever the answer is not something you already know for certain.',
        );
    }

    /**
     * @return list<ToolProperty>
     */
    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'query',
                type: PropertyType::STRING,
                description: 'The web search query, e.g. "population of Tirana 2024".',
                required: true,
            ),
        ];
    }

    public function __invoke(string $query): string
    {
        $query = trim($query);

        if ($query === '') {
            return 'Error: empty query.';
        }

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->get('https://api.duckduckgo.com/', [
                    'q' => $query,
                    'format' => 'json',
                    'no_html' => 1,
                    'no_redirect' => 1,
                ]);
        } catch (Throwable $e) {
            return 'Web search error: '.$e->getMessage();
        }

        if ($response->failed()) {
            return 'Web search failed ('.$response->status().').';
        }

        $lines = [];

        $abstract = $response->json('AbstractText');
        if (is_string($abstract) && $abstract !== '') {
            $lines[] = $abstract;
        }

        $related = $response->json('RelatedTopics');
        if (is_array($related)) {
            foreach ($related as $topic) {
                if (count($lines) >= 6) {
                    break;
                }

                if (is_array($topic) && is_string($topic['Text'] ?? null) && $topic['Text'] !== '') {
                    $lines[] = $topic['Text'];
                }
            }
        }

        if ($lines === []) {
            return 'No web results found for "'.$query.'".';
        }

        return implode("\n", $lines);
    }
}
