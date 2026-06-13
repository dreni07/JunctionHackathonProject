<?php

namespace App\Console\Commands;

use App\Services\QdrantService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class QdrantTestCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'qdrant:test {--collection= : Override the default Qdrant collection name}';

    /**
     * @var string
     */
    protected $description = 'Store sample vectors in Qdrant and run a search to verify the connection';

    public function handle(QdrantService $qdrant): int
    {
        if (! $qdrant->isConfigured()) {
            $this->error('Qdrant is not configured. Add QWDRANT_ENDPOINT and QWDRANT_API_KEY to your .env file.');

            return self::FAILURE;
        }

        $collection = $this->option('collection') ?: config('qdrant.collection');
        $testRunId = Str::uuid()->toString();

        $documents = [
            [
                'id' => (string) Str::uuid(),
                'text' => 'Laravel makes building web applications expressive and enjoyable.',
                'payload' => [
                    'title' => 'Laravel overview',
                    'source' => 'qdrant:test',
                    'test_run_id' => $testRunId,
                ],
            ],
            [
                'id' => (string) Str::uuid(),
                'text' => 'Vector databases store embeddings and enable semantic search.',
                'payload' => [
                    'title' => 'Vector database basics',
                    'source' => 'qdrant:test',
                    'test_run_id' => $testRunId,
                ],
            ],
            [
                'id' => (string) Str::uuid(),
                'text' => 'Pizza recipes usually start with dough, sauce, and cheese.',
                'payload' => [
                    'title' => 'Pizza recipe',
                    'source' => 'qdrant:test',
                    'test_run_id' => $testRunId,
                ],
            ],
        ];

        $this->info("Using collection: {$collection}");

        $this->line('Ensuring collection exists...');
        $qdrant->ensureCollection($collection);

        $this->line('Upserting sample documents...');
        $qdrant->upsertMany(
            points: collect($documents)
                ->map(fn (array $document): array => [
                    'id' => $document['id'],
                    'vector' => $qdrant->vectorFromText($document['text']),
                    'payload' => array_merge($document['payload'], [
                        'text' => $document['text'],
                    ]),
                ])
                ->all(),
            collection: $collection,
        );

        $query = 'How do vector databases help with semantic search?';
        $this->line("Searching for: \"{$query}\"");

        $results = $qdrant->searchByText($query, limit: 3, collection: $collection);

        if ($results === []) {
            $this->error('Search returned no results.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Top matches:');

        foreach ($results as $index => $result) {
            $title = $result['payload']['title'] ?? 'Untitled';
            $score = number_format($result['score'], 4);

            $this->line(sprintf('%d. [%s] %s (score: %s)', $index + 1, $result['id'], $title, $score));
        }

        $topResult = $results[0];
        $vectorDatabaseDocumentId = collect($documents)
            ->firstWhere('payload.title', 'Vector database basics')['id'];

        if ($topResult['id'] !== $vectorDatabaseDocumentId) {
            $this->warn('The expected top match was the vector database document, but Qdrant returned a different ranking.');
            $this->warn('The connection works, but you may want a real embedding model for production search quality.');
        } else {
            $this->info('Qdrant save + search test completed successfully.');
        }

        return self::SUCCESS;
    }
}
