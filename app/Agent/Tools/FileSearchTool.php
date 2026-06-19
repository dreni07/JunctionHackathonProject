<?php

declare(strict_types=1);

namespace App\Agent\Tools;

use App\Agent\Tool;
use App\Services\FileSearchService;

/**
 * Reads knowledge directly from the docs library on disk — no database, no
 * embeddings. An agent can be scoped to specific collections so it only ever
 * searches the files it is allowed to see.
 */
class FileSearchTool implements Tool
{
    /**
     * @param  list<string>  $allowedCollections  The doc collections this agent may
     *                                            search. Empty means the whole library.
     */
    public function __construct(
        private readonly FileSearchService $files,
        private readonly array $allowedCollections = [],
    ) {}

    public function name(): string
    {
        return 'file_search';
    }

    public function description(): string
    {
        $scope = $this->allowedCollections === []
            ? 'the docs knowledge library'
            : 'these document collections: '.implode(', ', $this->allowedCollections);

        return 'Search '.$scope.' and read information directly from the files. '
            .'Use it for questions about Pyramid spaces, booking and approval policies, '
            .'tenants, and event guidelines. Returns the most relevant excerpts with their '
            .'source file. No database is involved.';
    }

    public function parameters(): array
    {
        $collectionProperty = [
            'type' => 'string',
            'description' => 'Optional: limit the search to a single collection (docs sub-folder). Omit to search everything allowed.',
        ];

        if ($this->allowedCollections !== []) {
            $collectionProperty['enum'] = array_values($this->allowedCollections);
        }

        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Keywords or a natural-language question to look up in the files.',
                ],
                'collection' => $collectionProperty,
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

        $requested = trim((string) ($arguments['collection'] ?? ''));
        $hits = $this->files->search($query, $this->resolveCollections($requested), 5);

        if ($hits === []) {
            return 'No matching passages found in the docs library for "'.$query.'".';
        }

        $lines = ['Found '.count($hits).' passage(s) for "'.$query.'":', ''];
        $index = 1;

        foreach ($hits as $hit) {
            $lines[] = '['.$index.'] '.$hit['file'];
            $lines[] = $hit['snippet'];
            $lines[] = '';
            $index++;
        }

        return rtrim(implode("\n", $lines));
    }

    /**
     * Decide which collections to search, never escaping the agent's allowed set.
     *
     * @return list<string>
     */
    private function resolveCollections(string $requested): array
    {
        if ($this->allowedCollections === []) {
            return $requested !== '' ? [$requested] : [];
        }

        if ($requested !== '' && in_array($requested, $this->allowedCollections, true)) {
            return [$requested];
        }

        return $this->allowedCollections;
    }
}
