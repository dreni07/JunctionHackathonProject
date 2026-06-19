<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Speech-to-text via the OpenAI transcription API (POST /audio/transcriptions).
 * Mirrors the shape of {@see GroqService}: a thin, injectable client.
 */
class SpeechToTextService
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://api.openai.com/v1',
        private readonly string $model = 'whisper-1',
        private readonly int $timeout = 120,
    ) {}

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Transcribe an audio file on disk to text.
     *
     * @param  string|null  $language  Optional ISO-639-1 hint (e.g. "en", "sq").
     */
    public function transcribe(string $audioPath, ?string $language = null): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('OPENAI_API_KEY is not set.');
        }

        if (! is_file($audioPath)) {
            throw new RuntimeException("Audio file not found: {$audioPath}");
        }

        $payload = ['model' => $this->model, 'response_format' => 'json'];

        if ($language !== null && $language !== '') {
            $payload['language'] = $language;
        }

        $response = Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->attach('file', (string) file_get_contents($audioPath), basename($audioPath))
            ->post($this->baseUrl.'/audio/transcriptions', $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                'OpenAI transcription failed ('.$response->status().'): '.$response->body()
            );
        }

        $text = $response->json('text');

        if (! is_string($text)) {
            throw new RuntimeException('Unexpected transcription response from OpenAI.');
        }

        return trim($text);
    }

    /**
     * Transcribe an uploaded audio file (e.g. a browser recording).
     */
    public function transcribeUploadedFile(UploadedFile $file, ?string $language = null): string
    {
        return $this->transcribe($file->getRealPath(), $language);
    }
}
