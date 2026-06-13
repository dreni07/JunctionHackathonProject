<?php

namespace App\Services;

class VectorStore
{
    /**
     * Generate a vector embedding for the given text.
     *
     * @return array<int, float>
     */
    public function embed(string $text): array
    {
        return [];
    }

    /**
     * Store text and metadata in the given collection.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function store(string $collection, string|int $id, string $text, array $metadata = []): void
    {
        //
    }

    /**
     * Search a collection for content similar to the given query.
     *
     * @return array<int, array{id: string|int, score: float, payload: array<string, mixed>}>
     */
    public function search(string $collection, string $query, int $limit = 10): array
    {
        return [];
    }

    /**
     * Delete a stored vector from the given collection.
     */
    public function delete(string $collection, string|int $id): void
    {
        //
    }
}
