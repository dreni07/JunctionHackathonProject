<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class EmbeddingService
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta',
        private readonly string $model = 'gemini-embedding-001',
        private readonly int $dimensions = 768,
        private readonly int $timeout = 30,
    ) {}

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Generate a real embedding vector for the given text via Google Gemini.
     *
     * @return list<float>
     */
    public function embed(string $text): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('GOOGLE_API_KEY is not set.');
        }

        $response = Http::timeout($this->timeout)
            ->acceptJson()
            ->post("{$this->baseUrl}/models/{$this->model}:embedContent?key={$this->apiKey}", [
                'model' => "models/{$this->model}",
                'content' => ['parts' => [['text' => $text]]],
                'outputDimensionality' => $this->dimensions,
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Gemini embedding failed ('.$response->status().'): '.$response->body()
            );
        }

        $values = $response->json('embedding.values');

        if (! is_array($values)) {
            throw new RuntimeException('Unexpected embedding response from Gemini.');
        }

        return array_values(array_map(fn (mixed $value): float => (float) (is_numeric($value) ? $value : 0), $values));
    }
}
