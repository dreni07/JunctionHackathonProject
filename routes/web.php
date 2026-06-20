<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EventRequestController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OcrController;
use App\Http\Controllers\OperationalChangePollController;
use App\Http\Controllers\PlannerAgentController;
use App\Http\Controllers\PyramidKnowledgeController;
use App\Http\Controllers\SpeechController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'landing')->name('home');

Route::inertia('/planner', 'planner')->name('planner');

Route::get('/facility', [FacilityController::class, 'index'])->name('facility');

Route::post('/planner/agent', [PlannerAgentController::class, 'converse'])->name('planner.agent');

Route::post('/event-requests', [EventRequestController::class, 'store'])->name('event-requests.store');

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
    Route::get('dashboard', function (Request $request) {
        if ($request->user()?->isOperational()) {
            return redirect()->route('operations.home');
        }

        return Inertia\Inertia::render('dashboard');
    })->name('dashboard');

    // Operations dashboard shell (the React app reads the operations.* JSON API).
    Route::get('operations', function (Request $request) {
        $user = $request->user();

        return Inertia\Inertia::render('operations/dashboard', [
            'tenant' => $user?->tenant?->only(['id', 'title', 'description']),
            'isTenantManager' => $user?->isTenantManager() ?? false,
            'assignableWorkerRoles' => $user?->tenant?->assignableWorkerRoles() ?? [],
        ]);
    })->middleware('operational')->name('operations.home');

    Route::get('operational/changes/poll', OperationalChangePollController::class)
        ->middleware('operational')
        ->name('operational.changes.poll');

    // Profile completion (avatar + details).
    Route::get('profile/complete', [UserProfileController::class, 'edit'])->name('profile.complete');
    Route::post('profile/complete', [UserProfileController::class, 'update'])->name('profile.complete.update');
    Route::get('users/{user}/profile', [UserProfileController::class, 'show'])->name('users.profile.show');

    // In-app notifications (organizer is told when their event request is accepted).
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

require __DIR__.'/auth.php';

require __DIR__.'/operations.php';

require __DIR__.'/settings.php';
