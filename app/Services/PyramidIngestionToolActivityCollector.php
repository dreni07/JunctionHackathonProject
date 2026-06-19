<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Collects Neuron tool call activity during Pyramid PDF ingestion.
 */
final class PyramidIngestionToolActivityCollector
{
    /** @var list<array{name: string, input: array<string, mixed>, result: string}> */
    public array $entries = [];

    /**
     * @return list<array{name: string, input: array<string, mixed>, result: string}>
     */
    public function all(): array
    {
        return $this->entries;
    }
}
