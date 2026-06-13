<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class QdrantService
{
    public function __construct(
        private readonly string $endpoint,
        private readonly ?string $apiKey,
        private readonly string $defaultCollection,
        private readonly int $vectorSize,
        private readonly int $timeout = 30,
    ) {}

    public function isConfigured(): bool
    {
        return $this->endpoint !== '' && filled($this->apiKey);
    }

    /**
     * @return array<int, float>
     */
    public function vectorFromText(string $text, ?int $size = null): array
    {
        $size ??= $this->vectorSize;
        $vector = array_fill(0, $size, 0.0);
        $hash = hash('sha256', $text);

        for ($index = 0; $index < $size; $index++) {
            $vector[$index] = (hexdec(substr($hash, ($index * 2) % 64, 2)) / 255) - 0.5;
        }

        $magnitude = sqrt(array_sum(array_map(fn (float $value): float => $value ** 2, $vector)));

        if ($magnitude === 0.0) {
            return $vector;
        }

        return array_map(fn (float $value): float => $value / $magnitude, $vector);
    }

    public function ensureCollection(?string $collection = null): void
    {
        $this->createCollection(
            $this->collectionName($collection),
            $this->vectorSize,
        );
    }

    public function createCollection(string $name, ?int $vectorSize = null): void
    {
        if ($this->collectionExists($name)) {
            return;
        }

        $this->request()->put("/collections/{$name}", [
            'vectors' => [
                'size' => $vectorSize ?? $this->vectorSize,
                'distance' => 'Cosine',
            ],
        ]);
    }

    public function collectionExists(string $collection): bool
    {
        $response = $this->request(false)->get("/collections/{$collection}");

        return $response->successful();
    }

    /**
     * @param  array<int, float>  $vector
     * @param  array<string, mixed>  $payload
     */
    public function upsert(string|int $id, array $vector, array $payload = [], ?string $collection = null): void
    {
        $this->upsertMany([
            [
                'id' => $id,
                'vector' => $vector,
                'payload' => $payload,
            ],
        ], $collection);
    }

    /**
     * @param  array<int, array{id: string|int, vector: array<int, float>, payload?: array<string, mixed>}>  $points
     */
    public function upsertMany(array $points, ?string $collection = null): void
    {
        $collection = $this->collectionName($collection);

        $this->request()->put("/collections/{$collection}/points", [
            'points' => array_map(function (array $point): array {
                $payload = [
                    'id' => $point['id'],
                    'vector' => $point['vector'],
                ];

                if (isset($point['payload'])) {
                    $payload['payload'] = $point['payload'];
                }

                return $payload;
            }, $points),
        ]);
    }

    /**
     * @param  array<int, float>  $vector
     * @param  array<string, mixed>|null  $filter
     * @return array<int, array{id: string|int, score: float, payload: array<string, mixed>}>
     */
    public function search(array $vector, int $limit = 10, ?string $collection = null, ?array $filter = null): array
    {
        $collection = $this->collectionName($collection);

        $body = [
            'vector' => $vector,
            'limit' => $limit,
            'with_payload' => true,
        ];

        if ($filter !== null) {
            $body['filter'] = $filter;
        }

        $response = $this->request()->post("/collections/{$collection}/points/search", $body);

        return $this->mapSearchResults($response);
    }

    /**
     * @return array<int, array{id: string|int, score: float, payload: array<string, mixed>}>
     */
    public function searchByText(string $text, int $limit = 10, ?string $collection = null): array
    {
        return $this->search(
            vector: $this->vectorFromText($text),
            limit: $limit,
            collection: $collection,
        );
    }

    public function deleteCollection(?string $collection = null): void
    {
        $collection = $this->collectionName($collection);

        $this->request(false)->delete("/collections/{$collection}");
    }

    private function collectionName(?string $collection): string
    {
        return $collection ?? $this->defaultCollection;
    }

    private function request(bool $throwOnFailure = true): PendingRequest
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Qdrant is not configured. Set QWDRANT_ENDPOINT and QWDRANT_API_KEY in your .env file.');
        }

        $pendingRequest = Http::baseUrl($this->endpoint)
            ->withHeaders([
                'api-key' => $this->apiKey,
            ])
            ->acceptJson()
            ->timeout($this->timeout);

        if ($throwOnFailure) {
            $pendingRequest = $pendingRequest->throw(function (Response $response): void {
                throw new RuntimeException(sprintf(
                    'Qdrant request failed (%s): %s',
                    $response->status(),
                    $response->json('status.error') ?? $response->body(),
                ));
            });
        }

        return $pendingRequest;
    }

    /**
     * @return list<array{id: string|int, score: float, payload: array<array-key, mixed>}>
     */
    private function mapSearchResults(Response $response): array
    {
        $results = $response->json('result');

        if (! is_array($results)) {
            return [];
        }

        $mapped = [];

        foreach ($results as $result) {
            if (! is_array($result)) {
                continue;
            }

            $id = $result['id'] ?? null;
            $score = $result['score'] ?? null;
            $payload = $result['payload'] ?? [];

            $mapped[] = [
                'id' => is_int($id) ? $id : (string) (is_scalar($id) ? $id : ''),
                'score' => is_numeric($score) ? (float) $score : 0.0,
                'payload' => is_array($payload) ? $payload : [],
            ];
        }

        return $mapped;
    }
}
