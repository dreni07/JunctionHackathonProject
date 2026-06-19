<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\OcrController;
use App\Http\Controllers\OperationalChangePollController;
use App\Http\Controllers\PyramidKnowledgeController;
use App\Http\Controllers\SpeechController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'landing')->name('home');

Route::inertia('/planner', 'planner')->name('planner');

// Speech (OpenAI): voice input + spoken replies for the planner. Public.
Route::post('/speech/transcribe', [SpeechController::class, 'transcribe'])->name('speech.transcribe');
Route::post('/speech/speak', [SpeechController::class, 'speak'])->name('speech.speak');

Route::get('/ocr', [OcrController::class, 'index'])->name('ocr.index');
Route::post('/ocr', [OcrController::class, 'extract'])->name('ocr.extract');
Route::post('/ocr/document', [OcrController::class, 'extractDocument'])->name('ocr.document');

Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');

Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::post('/chat', [ChatController::class, 'send'])->name('chat.send');

Route::get('pyramid/ingest', [PyramidKnowledgeController::class, 'index'])
    ->name('pyramid.ingest.index');

Route::post('pyramid/ingest', [PyramidKnowledgeController::class, 'store'])
    ->name('pyramid.ingest.store');

Route::get('pyramid/knowledge', [PyramidKnowledgeController::class, 'explore'])
    ->name('pyramid.knowledge.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('operational/changes/poll', OperationalChangePollController::class)
        ->name('operational.changes.poll');
});

require __DIR__.'/settings.php';
