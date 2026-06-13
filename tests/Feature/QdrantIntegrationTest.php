<?php

use App\Services\QdrantService;
use Illuminate\Support\Str;

test('stores and searches vectors in the live qdrant cluster', function () {
    $qdrant = app(QdrantService::class);

    if (! $qdrant->isConfigured()) {
        test()->markTestSkipped('Qdrant credentials are not configured.');
    }

    $collection = 'hackathon_test_'.Str::lower(Str::random(8));
    $pointId = Str::uuid()->toString();
    $text = 'Integration test document about Laravel and Qdrant.';

    $qdrant->ensureCollection($collection);

    $qdrant->upsert($pointId, $qdrant->vectorFromText($text), [
        'title' => 'Integration test',
        'text' => $text,
        'source' => 'pest',
    ], $collection);

    $results = $qdrant->searchByText('Laravel Qdrant integration', limit: 1, collection: $collection);

    expect($results)->not->toBeEmpty()
        ->and($results[0]['id'])->toBe($pointId)
        ->and($results[0]['payload']['title'])->toBe('Integration test');

    $qdrant->deleteCollection($collection);
})->group('integration');
