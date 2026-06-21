<?php

use App\Agent\Tools\DbQueryTool;
use App\Agent\Tools\DocumentSearchTool;
use App\Agent\Tools\WebSearchTool;
use App\Models\Document;
use App\Services\EmbeddingService;
use App\Services\QdrantService;
use Illuminate\Support\Facades\Http;

describe('DbQueryTool', function () {
    it('runs a read-only SELECT and returns rows', function () {
        Document::factory()->create(['title' => 'Transformers paper']);

        $result = (new DbQueryTool)->execute([
            'query' => 'SELECT title FROM documents',
        ]);

        expect($result)->toContain('Transformers paper');
    });

    it('rejects non-SELECT statements', function () {
        $result = (new DbQueryTool)->execute([
            'query' => 'DELETE FROM documents',
        ]);

        expect($result)->toContain('only SELECT');
    });

    it('rejects forbidden keywords hidden in a SELECT', function () {
        $result = (new DbQueryTool)->execute([
            'query' => 'SELECT 1; DROP TABLE documents',
        ]);

        expect($result)->toContain('forbidden keyword');
    });
});

describe('DocumentSearchTool', function () {
    it('reports unavailable when embeddings/Qdrant are not configured', function () {
        $embeddings = Mockery::mock(EmbeddingService::class);
        $embeddings->shouldReceive('isConfigured')->andReturnFalse();

        $qdrant = Mockery::mock(QdrantService::class);
        $qdrant->shouldReceive('isConfigured')->andReturnFalse();

        $result = (new DocumentSearchTool($embeddings, $qdrant))->execute(['query' => 'attention']);

        expect($result)->toContain('unavailable');
    });

    it('returns matching passages when configured', function () {
        $embeddings = Mockery::mock(EmbeddingService::class);
        $embeddings->shouldReceive('isConfigured')->andReturnTrue();
        $embeddings->shouldReceive('embed')->andReturn([0.1, 0.2, 0.3]);

        $qdrant = Mockery::mock(QdrantService::class);
        $qdrant->shouldReceive('isConfigured')->andReturnTrue();
        $qdrant->shouldReceive('search')->andReturn([
            ['id' => 1, 'score' => 0.9, 'payload' => ['title' => 'Notes', 'content' => 'Attention is a mechanism.']],
        ]);

        $result = (new DocumentSearchTool($embeddings, $qdrant))->execute(['query' => 'attention']);

        expect($result)->toContain('[Notes] Attention is a mechanism.');
    });
});

describe('WebSearchTool', function () {
    it('formats DuckDuckGo results', function () {
        Http::fake([
            'api.duckduckgo.com/*' => Http::response([
                'AbstractText' => 'Laravel is a PHP framework.',
                'RelatedTopics' => [
                    ['Text' => 'Laravel was created by Taylor Otwell.'],
                ],
            ]),
        ]);

        $result = (new WebSearchTool)->execute(['query' => 'laravel']);

        expect($result)
            ->toContain('Laravel is a PHP framework.')
            ->toContain('Taylor Otwell');
    });
});
