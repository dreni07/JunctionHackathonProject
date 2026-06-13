<?php

namespace App\Console\Commands;

use App\Services\QdrantService;
use Illuminate\Console\Command;

class QdrantSetupCollectionsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'qdrant:setup-collections';

    /**
     * @var string
     */
    protected $description = 'Create the default Qdrant collections for agent memory, documents, and chat embeddings';

    /**
     * @var array<int, string>
     */
    private array $collections = [
        'agent_memory',
        'documents',
        'chat_embeddings',
    ];

    public function handle(QdrantService $qdrant): int
    {
        if (! $qdrant->isConfigured()) {
            $this->error('Qdrant is not configured. Add QWDRANT_ENDPOINT and QWDRANT_API_KEY to your .env file.');

            return self::FAILURE;
        }

        $vectorSize = (int) config('qdrant.vector_size');

        foreach ($this->collections as $collection) {
            $alreadyExists = $qdrant->collectionExists($collection);

            $qdrant->createCollection($collection, $vectorSize);

            $this->line($alreadyExists
                ? "Collection already exists: {$collection}"
                : "Created collection: {$collection} (vector size: {$vectorSize})");
        }

        $this->info('Qdrant collections are ready.');

        return self::SUCCESS;
    }
}
