<?php

namespace App\Agent\Tools;

use App\Agent\Tool;
use App\Services\EmbeddingService;
use App\Services\QdrantService;
use Throwable;

class DocumentSearchTool implements Tool
{
    public function __construct(
        private readonly EmbeddingService $embeddings,
        private readonly QdrantService $qdrant,
        private readonly string $collection = 'documents',
    ) {}

    public function name(): string
    {
        return 'document_search';
    }

    public function description(): string
    {
        return 'Semantic search over the content of the user\'s uploaded study documents and notes. '
            .'Use this to find relevant passages when answering questions about what the user has uploaded.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'The natural-language search query.',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of passages to return (default 5).',
                ],
            ],
            'required' => ['query'],
        ];
    }

    public function execute(array $arguments): string
    {
        if (! $this->embeddings->isConfigured() || ! $this->qdrant->isConfigured()) {
            return 'Document search is unavailable: embeddings (GOOGLE_API_KEY) and/or Qdrant are not configured.';
        }

        $query = trim((string) ($arguments['query'] ?? ''));

        if ($query === '') {
            return 'Error: empty query.';
        }

        $limit = (int) ($arguments['limit'] ?? 5);
        $limit = max(1, min($limit, 10));

        try {
            $vector = $this->embeddings->embed($query);
            $results = $this->qdrant->search($vector, $limit, $this->collection);
        } catch (Throwable $e) {
            return 'Document search error: '.$e->getMessage();
        }

        if ($results === []) {
            return 'No relevant passages found in the uploaded documents.';
        }

        $lines = [];

        foreach ($results as $result) {
            $title = is_string($result['payload']['title'] ?? null) ? $result['payload']['title'] : 'Untitled';
            $content = is_string($result['payload']['content'] ?? null) ? $result['payload']['content'] : '';
            $lines[] = "[{$title}] {$content}";
        }

        return implode("\n\n", $lines);
    }
}
