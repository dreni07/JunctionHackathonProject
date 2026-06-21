<?php

namespace App\Agent\Tools;

use App\Agent\Tool;
use Illuminate\Support\Facades\Http;
use Throwable;

class WebSearchTool implements Tool
{
    public function name(): string
    {
        return 'web_search';
    }

    public function description(): string
    {
        return 'Search the public web for current or general information that is NOT in the user\'s '
            .'uploaded documents (e.g. recent facts, definitions, external context).';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'The web search query.',
                ],
            ],
            'required' => ['query'],
        ];
    }

    public function execute(array $arguments): string
    {
        $query = trim((string) ($arguments['query'] ?? ''));

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
