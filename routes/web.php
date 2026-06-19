<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\OcrController;
use App\Http\Controllers\OperationalChangePollController;
use App\Http\Controllers\PyramidKnowledgeController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'landing')->name('home');

Route::inertia('/planner', 'planner')->name('planner');

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
