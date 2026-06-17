<?php

namespace App\Services;

use App\Models\Document;
use Throwable;

class DocumentIndexer
{
    public function __construct(
        private readonly EmbeddingService $embeddings,
        private readonly QdrantService $qdrant,
        private readonly string $collection = 'documents',
        private readonly int $chunkSize = 900,
        private readonly int $chunkOverlap = 150,
    ) {}

    /**
     * RAG indexing only runs when both embeddings (Gemini) and Qdrant are configured.
     */
    public function isEnabled(): bool
    {
        return $this->embeddings->isConfigured() && $this->qdrant->isConfigured();
    }

    /**
     * Chunk the document text, embed each chunk, and upsert into Qdrant.
     * Returns the number of chunks indexed (0 if disabled or empty).
     */
    public function index(Document $document): int
    {
        if (! $this->isEnabled()) {
            return 0;
        }

        $chunks = $this->chunk($document->full_text);

        if ($chunks === []) {
            return 0;
        }

        $this->qdrant->ensureCollection($this->collection);

        $points = [];

        foreach ($chunks as $chunkIndex => $content) {
            $points[] = [
                'id' => $document->id * 100_000 + $chunkIndex,
                'vector' => $this->embeddings->embed($content),
                'payload' => [
                    'document_id' => $document->id,
                    'title' => $document->title,
                    'chunk_index' => $chunkIndex,
                    'content' => $content,
                ],
            ];
        }

        $this->qdrant->upsertMany($points, $this->collection);

        return count($points);
    }

    /**
     * Best-effort indexing that never breaks the upload flow.
     */
    public function indexQuietly(Document $document): int
    {
        try {
            return $this->index($document);
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * Split text into overlapping word chunks.
     *
     * @return list<string>
     */
    private function chunk(string $text): array
    {
        $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($words === []) {
            return [];
        }

        $wordsPerChunk = max(1, (int) ($this->chunkSize / 6));
        $overlapWords = max(0, (int) ($this->chunkOverlap / 6));
        $step = max(1, $wordsPerChunk - $overlapWords);

        $chunks = [];

        for ($start = 0; $start < count($words); $start += $step) {
            $slice = array_slice($words, $start, $wordsPerChunk);
            $chunks[] = implode(' ', $slice);

            if ($start + $wordsPerChunk >= count($words)) {
                break;
            }
        }

        return $chunks;
    }
}
