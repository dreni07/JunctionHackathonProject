<?php

use App\Services\QdrantService;
use Illuminate\Support\Facades\Http;

test('reports whether qdrant credentials are configured', function () {
    $configured = new QdrantService(
        endpoint: 'https://example.qdrant.io',
        apiKey: 'test-key',
        defaultCollection: 'hackathon_documents',
        vectorSize: 384,
    );

    expect($configured->isConfigured())->toBeTrue();

    $missingCredentials = new QdrantService(
        endpoint: '',
        apiKey: null,
        defaultCollection: 'hackathon_documents',
        vectorSize: 384,
    );

    expect($missingCredentials->isConfigured())->toBeFalse();
});

test('creates a normalized vector from text', function () {
    $qdrant = new QdrantService(
        endpoint: 'https://example.qdrant.io',
        apiKey: 'test-key',
        defaultCollection: 'hackathon_documents',
        vectorSize: 4,
    );

    $vector = $qdrant->vectorFromText('hello world');

    expect($vector)->toHaveCount(4)
        ->and($vector)->each->toBeFloat();

    $magnitude = sqrt(array_sum(array_map(fn (float $value): float => $value ** 2, $vector)));

    expect($magnitude)->toBeGreaterThan(0.99)
        ->and($magnitude)->toBeLessThan(1.01);
});

test('upserts points and searches qdrant via the http api', function () {
    Http::fake([
        'https://example.qdrant.io/collections/hackathon_documents/points' => Http::response([
            'status' => 'ok',
            'result' => [
                'operation_id' => 1,
                'status' => 'completed',
            ],
        ]),
        'https://example.qdrant.io/collections/hackathon_documents/points/search' => Http::response([
            'status' => 'ok',
            'result' => [
                [
                    'id' => 'doc-1',
                    'score' => 0.91,
                    'payload' => [
                        'title' => 'Vector database basics',
                    ],
                ],
            ],
        ]),
    ]);

    config([
        'qdrant.endpoint' => 'https://example.qdrant.io',
        'qdrant.api_key' => 'test-key',
        'qdrant.collection' => 'hackathon_documents',
        'qdrant.vector_size' => 4,
    ]);

    $qdrant = app(QdrantService::class);
    $vector = $qdrant->vectorFromText('semantic search with vectors');

    $qdrant->upsert('doc-1', $vector, [
        'title' => 'Vector database basics',
        'text' => 'Vector databases store embeddings and enable semantic search.',
    ]);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.qdrant.io/collections/hackathon_documents/points'
            && $request->method() === 'PUT'
            && $request->hasHeader('api-key', 'test-key')
            && $request['points'][0]['id'] === 'doc-1';
    });

    $results = $qdrant->search($vector, limit: 1);

    expect($results)->toHaveCount(1)
        ->and($results[0]['id'])->toBe('doc-1')
        ->and($results[0]['score'])->toBe(0.91)
        ->and($results[0]['payload']['title'])->toBe('Vector database basics');
});

test('creates a collection with a custom vector size', function () {
    Http::fake([
        'https://example.qdrant.io/collections/agent_memory' => Http::sequence()
            ->push(['status' => 'error'], 404)
            ->push(['status' => 'ok', 'result' => true]),
    ]);

    $qdrant = new QdrantService(
        endpoint: 'https://example.qdrant.io',
        apiKey: 'test-key',
        defaultCollection: 'hackathon_documents',
        vectorSize: 384,
    );

    $qdrant->createCollection('agent_memory', 768);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.qdrant.io/collections/agent_memory'
            && $request->method() === 'PUT'
            && $request['vectors']['size'] === 768
            && $request['vectors']['distance'] === 'Cosine';
    });
});

test('ensures a collection exists before writing points', function () {
    Http::fake([
        'https://example.qdrant.io/collections/custom_docs' => Http::sequence()
            ->push(['status' => 'error'], 404)
            ->push(['status' => 'ok', 'result' => true]),
    ]);

    config([
        'qdrant.endpoint' => 'https://example.qdrant.io',
        'qdrant.api_key' => 'test-key',
        'qdrant.collection' => 'custom_docs',
        'qdrant.vector_size' => 384,
    ]);

    app(QdrantService::class)->ensureCollection('custom_docs');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.qdrant.io/collections/custom_docs'
            && $request->method() === 'PUT'
            && $request['vectors']['size'] === 384
            && $request['vectors']['distance'] === 'Cosine';
    });
});
