<?php

namespace App\Http\Controllers;

use App\Services\SpeechToTextService;
use App\Services\TextToSpeechService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SpeechController extends Controller
{
    public function __construct(
        private readonly SpeechToTextService $speechToText,
        private readonly TextToSpeechService $textToSpeech,
    ) {}

    /**
     * Transcribe an uploaded audio recording to text (speech-to-text).
     */
    public function transcribe(Request $request): JsonResponse
    {
        $request->validate([
            'audio' => ['required', 'file', 'max:25600'],
            'language' => ['nullable', 'string', 'max:10'],
        ]);

        if (! $this->speechToText->isConfigured()) {
            return response()->json(
                ['message' => 'Speech-to-text is not configured. Set OPENAI_API_KEY.'],
                503,
            );
        }

        try {
            $text = $this->speechToText->transcribeUploadedFile(
                $request->file('audio'),
                $request->string('language')->value() ?: null,
            );
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Transcription failed: '.$e->getMessage()], 502);
        }

        return response()->json(['text' => $text]);
    }

    /**
     * Synthesize speech from text and stream back the audio (text-to-speech).
     */
    public function speak(Request $request): Response
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:4000'],
            'voice' => ['nullable', 'string', 'max:40'],
        ]);

        if (! $this->textToSpeech->isConfigured()) {
            return response()->json(
                ['message' => 'Text-to-speech is not configured. Set OPENAI_API_KEY.'],
                503,
            );
        }

        try {
            $audio = $this->textToSpeech->synthesize(
                $validated['text'],
                $validated['voice'] ?? null,
            );
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Speech synthesis failed: '.$e->getMessage()], 502);
        }

        return response($audio, 200)
            ->header('Content-Type', $this->textToSpeech->mimeType())
            ->header('Cache-Control', 'no-store');
    }
}
