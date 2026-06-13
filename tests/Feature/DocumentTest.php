<?php

use App\Models\Document;
use App\Services\DocumentOcrService;
use App\Services\OcrService;
use Illuminate\Http\UploadedFile;

it('lists uploaded documents', function () {
    Document::factory()->count(3)->create();

    $this->get('/documents')->assertOk();
});

it('shows a single document', function () {
    $document = Document::factory()->create(['title' => 'Attention Is All You Need']);

    $this->get("/documents/{$document->id}")->assertOk();
});

it('stores an uploaded image as a document', function () {
    $this->mock(OcrService::class)
        ->shouldReceive('extractTextFromFile')
        ->once()
        ->andReturn('Extracted image text');

    $this->post('/documents', [
        'file' => UploadedFile::fake()->image('note.png'),
    ])->assertRedirect();

    $this->assertDatabaseHas('documents', [
        'original_filename' => 'note.png',
        'source_type' => 'image',
        'full_text' => 'Extracted image text',
    ]);
});

it('stores an uploaded pdf as a document', function () {
    $this->mock(DocumentOcrService::class)
        ->shouldReceive('extractTextFromFile')
        ->once()
        ->andReturn("Page one\n\nPage two");

    $this->post('/documents', [
        'file' => UploadedFile::fake()->create('paper.pdf', 200, 'application/pdf'),
    ])->assertRedirect();

    $this->assertDatabaseHas('documents', [
        'original_filename' => 'paper.pdf',
        'source_type' => 'pdf',
        'page_count' => 2,
    ]);
});

it('rejects unsupported file types', function () {
    $this->post('/documents', [
        'file' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
    ])->assertSessionHasErrors('file');
});
