<?php

use App\Services\VectorStore;

test('vector store exposes stubbed vector database methods', function () {
    $vectorStore = app(VectorStore::class);

    expect($vectorStore->embed('hello world'))->toBe([])
        ->and($vectorStore->search('documents', 'hello world'))->toBe([]);

    $vectorStore->store('documents', 'doc-1', 'hello world', ['title' => 'Hello']);
    $vectorStore->delete('documents', 'doc-1');
});
