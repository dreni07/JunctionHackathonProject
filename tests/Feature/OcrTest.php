<?php

use App\Services\DocumentOcrService;
use App\Services\OcrService;
use Illuminate\Http\UploadedFile;

it('renders the landing page', function () {
    $this->get('/')->assertOk();
});

it('extracts text from an uploaded image', function () {
    $this->mock(OcrService::class)
        ->shouldReceive('extractTextFromFile')
        ->once()
        ->andReturn('Hello OCR');

    $this->post('/ocr', [
        'image' => UploadedFile::fake()->image('sample.png'),
    ])
        ->assertOk()
        ->assertExactJson(['response' => 'Hello OCR']);
});

it('requires an uploaded image', function () {
    $this->postJson('/ocr', [])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors' => ['image']]);
});

it('rejects non-image uploads', function () {
    $this->postJson('/ocr', [
        'image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
    ])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors' => ['image']]);
});

it('extracts text from an uploaded PDF document', function () {
    $this->mock(DocumentOcrService::class)
        ->shouldReceive('extractTextFromFile')
        ->once()
        ->andReturn("Page 1 text\n\nPage 2 text");

    $this->post('/ocr/document', [
        'document' => UploadedFile::fake()->create('report.pdf', 200, 'application/pdf'),
    ])
        ->assertOk()
        ->assertExactJson(['response' => "Page 1 text\n\nPage 2 text"]);
});

it('requires an uploaded document', function () {
    $this->postJson('/ocr/document', [])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors' => ['document']]);
});

it('rejects non-pdf documents', function () {
    $this->postJson('/ocr/document', [
        'document' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
    ])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors' => ['document']]);
});
